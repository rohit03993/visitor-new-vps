<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\VmsUser;
use App\Models\InteractionHistory;

class NotificationController extends Controller
{
    /**
     * Stream real-time notifications to the browser using Server-Sent Events
     */
    public function stream(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response('Unauthorized', 401);
        }

        // Create streaming response
        return response()->stream(function () use ($user) {
            // Set execution time limit to 0 (no limit) for long-running connections
            set_time_limit(0);
            
            // Send initial connection confirmation
            echo "event: connected\n";
            echo "data: " . json_encode([
                'type' => 'connected',
                'message' => 'Connected to Task Book notification stream',
                'user_id' => $user->user_id,
                'timestamp' => now()->toISOString()
            ]) . "\n\n";
            
            // Flush output immediately
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
            
            $lastCheck = time();
            
            // Keep connection alive and check for notifications
            $maxRunTime = 300; // 5 minutes max
            $startTime = time();
            
            while (time() - $startTime < $maxRunTime) {
                try {
                    // Check for new notifications for this user from file storage
                    $notifications = $this->getFileNotifications($user->user_id);
                    
                    if (!empty($notifications)) {
                        foreach ($notifications as $index => $notification) {
                            echo "event: notification\n";
                            echo "data: " . json_encode($notification) . "\n\n";
                        }
                        
                        // Clear all sent notifications
                        $this->clearFileNotifications($user->user_id);
                        
                        flush();
                    }
                    
                    // Send keepalive every 30 seconds to prevent connection timeout
                    if (time() - $lastCheck >= 30) {
                        echo "event: keepalive\n";
                        echo "data: " . json_encode([
                            'type' => 'keepalive',
                            'timestamp' => now()->toISOString()
                        ]) . "\n\n";
                        flush();
                        $lastCheck = time();
                    }
                    
                    // Check for connection status
                    if (connection_aborted()) {
                        \Log::info("SSE connection aborted for user {$user->user_id}");
                        break;
                    }
                    
                    // Sleep for 2 seconds before next check (reduced frequency)
                    sleep(2);
                    
                } catch (\Exception $e) {
                    \Log::error("SSE loop error: " . $e->getMessage());
                    break;
                }
            }
            
            // Connection timeout reached
            echo "event: timeout\n";
            echo "data: " . json_encode(['message' => 'Connection timeout, please refresh']) . "\n\n";
            flush();
            
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable Nginx buffering
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control'
        ]);
    }

    /**
     * Send notification to specific user
     */
    public function sendNotification(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:vms_users,user_id',
                'type' => 'required|string',
                'title' => 'required|string',
                'message' => 'required|string',
                'data' => 'nullable|array'
            ]);

            $user = VmsUser::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Create notification data
            $notificationData = [
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'data' => $request->data ?? [],
                'timestamp' => now()->toISOString(),
                'user_id' => $user->user_id,
                'user_name' => $user->name
            ];

            // Store notification in file storage for this user
            $this->storeFileNotification($user->user_id, $notificationData);

            \Log::info("Notification sent to user {$user->user_id}: {$request->title}");

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'notification' => $notificationData
            ]);

        } catch (\Exception $e) {
            \Log::error('Send notification error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send notification'], 500);
        }
    }

    /**
     * Get pending notifications for current user
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                \Log::error('Get notifications: User not authenticated');
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            \Log::info("=== GET NOTIFICATIONS CALLED ===");
            \Log::info("User: {$user->name} (ID: {$user->user_id})");

            // Get notifications from file storage only
            $allNotifications = $this->getFileNotifications($user->user_id);
            
            // Clear notifications after retrieving
            $this->clearFileNotifications($user->user_id);

            // Debug logging
            \Log::info("=== GET NOTIFICATIONS DEBUG ===");
            \Log::info("User ID: {$user->user_id}");
            \Log::info("File notifications: " . count($allNotifications));
            \Log::info("Total notifications: " . count($allNotifications));
            \Log::info("Notifications data: " . json_encode($allNotifications));
            \Log::info("=== END GET DEBUG ===");

            return response()->json([
                'success' => true,
                'notifications' => $allNotifications,
                'debug' => [
                    'file_count' => count($allNotifications),
                    'total_count' => count($allNotifications)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get notifications error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get notifications'], 500);
        }
    }

    /**
     * Get notifications from file storage
     */
    private function getFileNotifications($userId)
    {
        try {
            $filePath = storage_path("app/notifications/user_{$userId}.json");
            
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                return json_decode($content, true) ?: [];
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error("Error reading file notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Store notification in file
     */
    private function storeFileNotification($userId, $notification)
    {
        try {
            $dirPath = storage_path('app/notifications');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            
            $filePath = storage_path("app/notifications/user_{$userId}.json");
            
            $existingNotifications = $this->getFileNotifications($userId);
            $existingNotifications[] = $notification;
            
            file_put_contents($filePath, json_encode($existingNotifications, JSON_PRETTY_PRINT));
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Error storing file notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear file notifications for user
     */
    private function clearFileNotifications($userId)
    {
        try {
            $filePath = storage_path("app/notifications/user_{$userId}.json");
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } catch (\Exception $e) {
            \Log::error("Error clearing file notifications: " . $e->getMessage());
        }
    }

    /**
     * Send visit assignment notification
     */
    public function sendVisitAssignmentNotification($interactionId, $assignedToUserId, $visitorName, $purpose)
    {
        try {
            $assignedUser = VmsUser::find($assignedToUserId);
            if (!$assignedUser) {
                \Log::error("Cannot send notification: User {$assignedToUserId} not found");
                return false;
            }

            $notificationData = [
                'type' => 'visit_assigned',
                'title' => 'New Visit Assigned!',
                'message' => "You have been assigned a new visit: {$visitorName} - {$purpose}",
                'data' => [
                    'interaction_id' => $interactionId,
                    'visitor_name' => $visitorName,
                    'purpose' => $purpose,
                    'assigned_to' => $assignedUser->name
                ],
                'timestamp' => now()->toISOString(),
                'user_id' => $assignedUser->user_id,
                'user_name' => $assignedUser->name
            ];

            // Store notification in file storage only
            $fileStored = $this->storeFileNotification($assignedUser->user_id, $notificationData);

            // Enhanced debug logging
            \Log::info("=== NOTIFICATION DEBUG ===");
            \Log::info("Assigned to user: {$assignedUser->name} (ID: {$assignedUser->user_id})");
            \Log::info("Visitor: {$visitorName}");
            \Log::info("Purpose: {$purpose}");
            \Log::info("Interaction ID: {$interactionId}");
            \Log::info("Notification data: " . json_encode($notificationData));
            \Log::info("File storage success: " . ($fileStored ? 'YES' : 'NO'));
            \Log::info("=== END DEBUG ===");

            return true;

        } catch (\Exception $e) {
            \Log::error('Send visit assignment notification error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}
