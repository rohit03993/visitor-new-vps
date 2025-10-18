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
            // ✅ DUPLICATE PREVENTION: Check if user is already actively subscribed
            $existingActiveSubscription = InteractionNotification::where('interaction_id', $interactionId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if ($existingActiveSubscription) {
                \Log::info("User {$userId} already actively subscribed to interaction {$interactionId}, skipping duplicate subscription");
                return true; // Return true since they're already subscribed
            }
            
            // ✅ SMART LOGIC: Handle inactive subscriptions based on subscription type
            $existingSubscription = InteractionNotification::where('interaction_id', $interactionId)
                ->where('user_id', $userId)
                ->first();
            
            if ($existingSubscription) {
                // If subscription exists but is inactive
                if (!$existingSubscription->is_active) {
                    // ✅ MANUAL SUBSCRIPTIONS: Allow manual subscriptions to re-activate inactive subscriptions
                    if ($subscribedBy === 'manual' || $subscribedBy === 'admin') {
                        $existingSubscription->update([
                            'subscribed_by' => $subscribedBy,
                            'is_active' => true,
                            'subscribed_at' => now()
                        ]);
                        \Log::info("Re-activated inactive subscription for user {$userId} to interaction {$interactionId} via manual subscription");
                        return true;
                    } else {
                        // ✅ AUTO SUBSCRIPTIONS: Don't re-activate inactive subscriptions (respect unsubscribe decisions)
                        \Log::info("User {$userId} has inactive subscription for interaction {$interactionId} - respecting unsubscribe decision, not re-activating via auto-subscription");
                        return false;
                    }
                }
                // If subscription exists and is active, update it
                $existingSubscription->update([
                    'subscribed_by' => $subscribedBy,
                    'subscribed_at' => now()
                ]);
            } else {
                // Create new subscription with default can_view_remarks=true
                InteractionNotification::create([
                    'interaction_id' => $interactionId,
                    'user_id' => $userId,
                    'subscribed_by' => $subscribedBy,
                    'is_active' => true,
                    'can_view_remarks' => true, // ✅ Default: Users can view remarks
                    'subscribed_at' => now()
                ]);
            }
            
            \Log::info("Successfully subscribed user {$userId} to interaction {$interactionId} with subscribed_by: {$subscribedBy}");
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
                ->update(['is_active' => false]); // ✅ Mark as inactive instead of delete to preserve unsubscribe history
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
     * Toggle remark viewing permission for a user on an interaction
     * 
     * @param int $interactionId
     * @param int $userId
     * @param bool $canView
     * @return bool
     */
    public function setRemarkPermission(int $interactionId, int $userId, bool $canView): bool
    {
        try {
            // Check if subscription exists
            $subscription = InteractionNotification::where('interaction_id', $interactionId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$subscription) {
                \Log::warning("Cannot set remark permission: User {$userId} not subscribed to interaction {$interactionId}");
                return false;
            }
            
            // Update permission
            $subscription->update(['can_view_remarks' => $canView]);
            
            \Log::info("Updated remark permission for user {$userId} on interaction {$interactionId}: can_view_remarks=" . ($canView ? 'true' : 'false'));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to set remark permission: ' . $e->getMessage());
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

            // Subscribe each previous worker ONLY if they were never manually unsubscribed
            foreach ($previousWorkers as $userId) {
                // Check if user is already subscribed to current interaction
                $existingSubscription = InteractionNotification::where('interaction_id', $interactionId)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($existingSubscription) {
                    \Log::info("User {$userId} already subscribed to interaction {$interactionId}, skipping");
                    continue;
                }

                // ✅ ENHANCED LOGIC: Check if user was ever manually unsubscribed from this visitor/purpose
                $wasEverUnsubscribed = InteractionNotification::whereHas('interaction', function($q) use ($visitorId, $purpose) {
                    $q->where('visitor_id', $visitorId)->where('purpose', $purpose);
                })->where('user_id', $userId)
                  ->where('is_active', false)
                  ->where('subscribed_by', 'manual')
                  ->exists();
                
                // ✅ ADDITIONAL CHECK: If user was recently unsubscribed (within last 24 hours), don't auto-subscribe
                $recentlyUnsubscribed = InteractionNotification::whereHas('interaction', function($q) use ($visitorId, $purpose) {
                    $q->where('visitor_id', $visitorId)->where('purpose', $purpose);
                })->where('user_id', $userId)
                  ->where('is_active', false)
                  ->where('updated_at', '>=', now()->subHours(24)) // Check if unsubscribed within last 24 hours
                  ->exists();
                
                // Only auto-subscribe if they were never manually unsubscribed AND not recently unsubscribed
                if (!$wasEverUnsubscribed && !$recentlyUnsubscribed) {
                    $this->subscribeUser($interactionId, $userId, 'manual');
                    \Log::info("Auto-subscribed user {$userId} to interaction {$interactionId} - no previous unsubscribe history");
                } else {
                    \Log::info("Skipped auto-subscription for user {$userId} - was previously or recently manually unsubscribed from visitor {$visitorId} purpose {$purpose}");
                }
            }

            \Log::info("Auto-subscription completed for interaction {$interactionId}, respecting unsubscribe history");

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
            ->where('is_active', true) // ✅ Only return active subscriptions
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

    /**
     * Copy manual subscriptions from one interaction to another
     */
    public function copyManualSubscriptions(int $fromInteractionId, int $toInteractionId): bool
    {
        try {
            // Get all active manual subscriptions from the source interaction
            $manualSubscriptions = InteractionNotification::where('interaction_id', $fromInteractionId)
                ->where('subscribed_by', 'manual')
                ->where('is_active', true)
                ->get();

            \Log::info("Copying {$manualSubscriptions->count()} manual subscriptions from interaction {$fromInteractionId} to {$toInteractionId}");

            foreach ($manualSubscriptions as $subscription) {
                // Check if user is already subscribed to the new interaction (avoid duplicates)
                $alreadySubscribed = InteractionNotification::where('interaction_id', $toInteractionId)
                    ->where('user_id', $subscription->user_id)
                    ->where('is_active', true)
                    ->exists();
                
                if (!$alreadySubscribed) {
                    // Copy the subscription to the new interaction
                    $this->subscribeUser($toInteractionId, $subscription->user_id, 'manual');
                    
                    // Send immediate notification to the copied subscriber
                    $this->sendImmediateSubscriptionNotification(
                        $toInteractionId,
                        $subscription->user_id,
                        auth()->user()->user_id
                    );
                } else {
                    \Log::info("User {$subscription->user_id} already subscribed to interaction {$toInteractionId}, skipping duplicate subscription");
                }
            }

            \Log::info("Successfully copied manual subscriptions from interaction {$fromInteractionId} to {$toInteractionId}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to copy manual subscriptions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send immediate notification when user is subscribed to an interaction
     */
    public function sendImmediateSubscriptionNotification(int $interactionId, int $subscribedUserId, int $subscribedByUserId): bool
    {
        try {
            $interaction = InteractionHistory::find($interactionId);
            $subscribedUser = VmsUser::find($subscribedUserId);
            $subscribedByUser = VmsUser::find($subscribedByUserId);

            if (!$interaction || !$subscribedUser || !$subscribedByUser) {
                \Log::error('Missing data for subscription notification');
                return false;
            }

            // Don't notify if user subscribed themselves
            if ($subscribedUserId === $subscribedByUserId) {
                return true;
            }

            // Send notification to the newly subscribed user
            $this->sendNotification(
                $interactionId,
                $subscribedByUserId, // triggered by the person who added the subscription
                'subscription_added',
                "You've been added to receive notifications for interaction #{$interactionId}: {$interaction->name_entered} - {$interaction->purpose} by {$subscribedByUser->name}"
            );

            \Log::info("Immediate subscription notification sent to user {$subscribedUser->name} for interaction {$interactionId}");

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send immediate subscription notification: ' . $e->getMessage());
            return false;
        }
    }

}