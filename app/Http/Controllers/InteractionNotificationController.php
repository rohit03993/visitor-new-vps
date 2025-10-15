<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InteractionNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notification settings for an interaction
     */
    public function getSettings(int $interactionId)
    {
        $interaction = \App\Models\InteractionHistory::find($interactionId);
        if (!$interaction) {
            return response()->json(['success' => false, 'message' => 'Interaction not found'], 404);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        $canModify = ($interaction->meeting_with === $user->user_id) || ($user->role === 'admin');

        $subscriptions = \App\Models\InteractionNotification::where('interaction_id', $interactionId)
            ->with('user:user_id,name,role')
            ->get();

        $allStaff = \App\Models\VmsUser::where('role', 'staff')
            ->orWhere('role', 'admin')
            ->select('user_id', 'name', 'role')
            ->get();

        $subscribedUserIds = $subscriptions->pluck('user_id')->toArray();

        $data = [
            'can_modify' => $canModify,
            'privacy_level' => $subscriptions->first()->privacy_level ?? 'public',
            'subscribed_users' => $subscriptions->map(function ($sub) use ($interaction) {
                return [
                    'user_id' => $sub->user->user_id,
                    'name' => $sub->user->name,
                    'role' => $sub->user->role,
                    'is_active' => $sub->is_active,
                    'subscribed_by' => $sub->subscribed_by,
                    'is_current_assignee' => $sub->user->user_id === $interaction->meeting_with,
                    'is_admin' => $sub->user->role === 'admin',
                ];
            })->values()->toArray(),
            'available_staff' => $allStaff->filter(function ($staff) use ($subscribedUserIds) {
                return !in_array($staff->user_id, $subscribedUserIds);
            })->map(function ($staff) {
                return [
                    'user_id' => $staff->user_id,
                    'name' => $staff->name,
                    'role' => $staff->role,
                ];
            })->values()->toArray(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Subscribe a user to notifications
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'interaction_id' => 'required|integer',
            'user_id' => 'required|integer',
            'subscribed_by' => 'string|in:manual,admin'
        ]);

        try {
            $success = $this->notificationService->subscribeUser(
                $request->interaction_id,
                $request->user_id,
                $request->subscribed_by ?? 'manual'
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'User subscribed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to subscribe user'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe a user from notifications
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'interaction_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);

        try {
            $success = $this->notificationService->unsubscribeUser(
                $request->interaction_id,
                $request->user_id
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'User unsubscribed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to unsubscribe user'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set privacy level for notifications
     */
    public function setPrivacy(Request $request): JsonResponse
    {
        $request->validate([
            'interaction_id' => 'required|integer',
            'privacy_level' => 'required|in:public,private'
        ]);

        try {
            $success = $this->notificationService->setPrivacyLevel(
                $request->interaction_id,
                $request->privacy_level
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Privacy level updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update privacy level'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update privacy level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $limit = $request->get('limit', 50);
            
            $notifications = $this->notificationService->getUserNotifications($userId, $limit);
            $unreadCount = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'array',
            'notification_ids.*' => 'integer'
        ]);

        try {
            $userId = auth()->id();
            $notificationIds = $request->get('notification_ids');
            
            $success = $this->notificationService->markAsRead($userId, $notificationIds);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifications marked as read'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark notifications as read'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $count = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscribers for an interaction (alias for getSettings)
     */
    public function getSubscribers(int $interactionId)
    {
        return $this->getSettings($interactionId);
    }
}