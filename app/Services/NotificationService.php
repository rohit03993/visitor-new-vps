<?php

namespace App\Services;

use App\Models\InteractionNotification;
use App\Models\NotificationLog;
use App\Models\InteractionHistory;
use App\Models\VmsUser;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Subscribe a user to notifications for an interaction
     */
    public function subscribeUser(int $interactionId, int $userId, string $subscribedBy = 'manual'): bool
    {
        try {
            InteractionNotification::updateOrCreate(
                [
                    'interaction_id' => $interactionId,
                    'user_id' => $userId
                ],
                [
                    'subscribed_by' => $subscribedBy,
                    'is_active' => true,
                    'subscribed_at' => now()
                ]
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to subscribe user to notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsubscribe a user from notifications for an interaction
     */
    public function unsubscribeUser(int $interactionId, int $userId): bool
    {
        try {
            InteractionNotification::where('interaction_id', $interactionId)
                ->where('user_id', $userId)
                ->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to unsubscribe user from notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set privacy level for an interaction
     */
    public function setPrivacyLevel(int $interactionId, string $privacyLevel): bool
    {
        try {
            InteractionNotification::where('interaction_id', $interactionId)
                ->update(['privacy_level' => $privacyLevel]);

            // If setting to private, remove all staff except current assignee and director
            if ($privacyLevel === 'private') {
                $interaction = InteractionHistory::find($interactionId);
                if ($interaction) {
                    $currentAssignee = $interaction->meeting_with;
                    $directors = VmsUser::where('role', 'admin')->pluck('user_id')->toArray();

                    InteractionNotification::where('interaction_id', $interactionId)
                        ->whereNotIn('user_id', array_merge([$currentAssignee], $directors))
                        ->delete();
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to set privacy level: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all subscribed users for an interaction
     */
    public function sendNotification(int $interactionId, int $triggeredBy, string $type, string $message): bool
    {
        try {
            DB::beginTransaction();

            // Get all active subscribers for this interaction
            $subscribers = InteractionNotification::where('interaction_id', $interactionId)
                ->where('is_active', true)
                ->where('user_id', '!=', $triggeredBy) // Problem 1 Fix: Don't notify the person who triggered it
                ->get();

            foreach ($subscribers as $subscriber) {
                // Problem 2 Fix: For assignments, don't notify the assigned person
                // (They already get Firebase notification + Assigned tab)
                if ($type === 'assignment') {
                    $interaction = \App\Models\InteractionHistory::find($interactionId);
                    if ($interaction && $subscriber->user_id === $interaction->meeting_with) {
                        continue; // Skip notification to assigned person
                    }
                }

                NotificationLog::create([
                    'interaction_id' => $interactionId,
                    'user_id' => $subscriber->user_id,
                    'triggered_by' => $triggeredBy,
                    'notification_type' => $type,
                    'message' => $message,
                    'is_read' => false
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to send notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Auto-subscribe creator, assignee, and anyone who worked on this visitor/purpose before
     */
    public function autoSubscribe(int $interactionId, int $creatorId, int $assigneeId): void
    {
        try {
            // Get interaction details to find visitor_id and purpose
            $interaction = \App\Models\InteractionHistory::find($interactionId);
            if (!$interaction) {
                \Log::error("Interaction {$interactionId} not found for auto-subscribe");
                return;
            }

            $visitorId = $interaction->visitor_id;
            $purpose = $interaction->purpose;

            // Subscribe creator
            $this->subscribeUser($interactionId, $creatorId, 'creator');

            // Subscribe assignee (if different from creator)
            if ($creatorId !== $assigneeId) {
                $this->subscribeUser($interactionId, $assigneeId, 'assignee');
            }

            // NEW LOGIC: Subscribe everyone who worked on this visitor + purpose before
            $this->subscribePreviousWorkers($interactionId, $visitorId, $purpose, [$creatorId, $assigneeId]);

        } catch (\Exception $e) {
            \Log::error('Failed to auto-subscribe users: ' . $e->getMessage());
        }
    }

    /**
     * Subscribe everyone who previously worked on this visitor + purpose
     */
    private function subscribePreviousWorkers(int $interactionId, int $visitorId, string $purpose, array $excludeUserIds = []): void
    {
        try {
            // Find all users who worked on interactions with same visitor_id and purpose
            $previousWorkers = \App\Models\InteractionHistory::where('visitor_id', $visitorId)
                ->where('purpose', $purpose)
                ->where('interaction_id', '!=', $interactionId) // Exclude current interaction
                ->where(function($query) {
                    $query->whereNotNull('meeting_with')
                          ->orWhereNotNull('assigned_by')
                          ->orWhereNotNull('created_by');
                })
                ->get()
                ->flatMap(function($interaction) {
                    $users = collect();
                    if ($interaction->meeting_with) $users->push($interaction->meeting_with);
                    if ($interaction->assigned_by) $users->push($interaction->assigned_by);
                    if ($interaction->created_by) $users->push($interaction->created_by);
                    return $users;
                })
                ->unique()
                ->filter(function($userId) use ($excludeUserIds) {
                    return !in_array($userId, $excludeUserIds);
                });

            // Subscribe each previous worker
            foreach ($previousWorkers as $userId) {
                $this->subscribeUser($interactionId, $userId, 'manual');
            }

            \Log::info("Auto-subscribed {$previousWorkers->count()} previous workers for interaction {$interactionId}");

        } catch (\Exception $e) {
            \Log::error('Failed to subscribe previous workers: ' . $e->getMessage());
        }
    }

    /**
     * Get notification subscribers for an interaction
     */
    public function getSubscribers(int $interactionId): array
    {
        return InteractionNotification::where('interaction_id', $interactionId)
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->toArray();
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications(int $userId, int $limit = 50): array
    {
        return NotificationLog::where('user_id', $userId)
            ->with(['interaction', 'triggeredBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return NotificationLog::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(int $userId, array $notificationIds = null): bool
    {
        try {
            $query = NotificationLog::where('user_id', $userId);
            
            if ($notificationIds) {
                $query->whereIn('id', $notificationIds);
            }
            
            $query->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to mark notifications as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear subscriptions when case is completed
     */
    public function clearSubscriptionsForCompletedCase(int $visitorId, string $purpose): bool
    {
        try {
            // Find all interactions for this visitor + purpose
            $interactions = \App\Models\InteractionHistory::where('visitor_id', $visitorId)
                ->where('purpose', $purpose)
                ->pluck('interaction_id');

            if ($interactions->isEmpty()) {
                return true;
            }

            // Deactivate subscriptions (don't delete, just mark as inactive)
            $clearedCount = InteractionNotification::whereIn('interaction_id', $interactions)
                ->update(['is_active' => false]);

            \Log::info("Cleared {$clearedCount} subscriptions for completed case: Visitor {$visitorId}, Purpose: {$purpose}");

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to clear subscriptions for completed case: ' . $e->getMessage());
            return false;
        }
    }

}