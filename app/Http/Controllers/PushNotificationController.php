<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PushNotificationController extends Controller
{
    // Firebase Service Account Configuration
    private $serviceAccountEmail = 'firebase-adminsdk-fbsvc@vms-crm-notifications.iam.gserviceaccount.com';
    private $messagingSenderId = '197047969653'; // Updated from Firebase Console
    private $serviceAccountPrivateKey = '-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC1Yl9hJZegfzuu
N+hha+NC9TDoLRAkWfLRLoOI1mUkOYjWsmf/F0rjmMOentPN/is+JAwMjFY8gTjq
yu08UprjrcE1f1VsEElicDszjVVCKh8jVAZIV9ZoDm/EWvRA30AAolteDE4uUACF
/yYDBap6rqjgOXjEwOEbEQi5bMzuyoxsfKpSExqii9ofWplOWxKuljndIcBQakco
dYbAFDve/9Izt+HaiDpV104iDxs+AWAtxWt9Gvf2j2i1JklQK/5F9o+457ydL3jA
aqkUSOF+eUufUO4FvLMthqoBnouUxZ7An7BXzv5ZPMLM8PC8i3i5vy1dzQLEYnjy
x0U7Jnd3AgMBAAECggEAGOH2YK7WBqv9pXBA/kBdLF3TiD5KVRpLz7t4SujSdi44
Ye+Wia2J1gAqcdOrDbq89ujeCEimOeWmR7tv4RMZ8XrwIuUldE4lqw3naTKNzCZY
IDISLJF0NdEpLwAlOtMFhjC/pP6+KOdLsxYmAksgMHVOcHgh46fsGZj0H+/Xizhd
+5iIKL2T1+AFEv0J40oIh2ly5yJ2se5xHY66h4qnIZftB65rNGySKAkUOozZBZdd
TSsGY83y6Bpt0qyw5hzUSf5LNdFODInBggzEFqqg7w8cAXfPguEb6OPjaM5zdLb2
Errtl1F17qxCt0szuicsqs22h60a/j4yzuPLr967RQKBgQDaOcutEBAstYaSH8Db
NPN4fm8heVFZOdB3C7axHHt7FccMpfXc6KQiAGEwP5bdmBHincTmugON8E4SJh4t
q87squa+a/jUS+v4okb0rLYxUhEHJ43xn+1FQuyU1rPuOXn8FfvCl/u0pa121WtX
8ilwBv6w3kNEz+DNNmDRLT36SwKBgQDUyAaE+UuJeBu00+c5p64GYObVZrjm3t9R
X08cwrp2QuCgF6zzkNBGRj/SlITA2J+UcNsZ+StMLoI0g7Zw5nmPcIhjFjgTlsLh
PEznQxAAaMWiLNm5MUV59g3sM5dGzbyg1rgFxlgzIa3smq7eumIRTFUgwaqN1mdN
T93finU8BQKBgBocxFRnEahn6Dxf9FHGmkOWzXFx9Nv6YQl9q1SyFcx6pKDM0wim
Bc3Twc1mLoVBhxJY0pDRPU+kq5LcYMwSPOZw5L9waAvvMcNEl7z7Vam9KjBy+Tcq
bdfV1D1TG6Cr2/7gGooEaagKEyGfFAMoBPFUxPEhB2eagEnN8fPVuA7VAoGBALzc
AXVbNCGC+syIXK4+12aP8bKt5yX74aj++GAlsoyvFWLjQL465bHKPnGxIxdr7lA5
zy8Bit2mVik4UuFon7KiBlw0Z3dzk+uIsxV836INXIVyW5lVUz5KF9dzfyz4BRmZ
G2L8xmIz3YSpUtccBVknMFPPsYsNJ0lmvx7fbOjlAoGAMq+lHuDxOIbgzib1vN6A
z2aT3TDE2j2IqvFCWwDdYTR/+CLthXSGQCCYiunxiFc7sl/FMRIaVWECqusipfKQ
mE6SDswP9CbR2Ai71xgmqp1Q9FYKx+fWqVlMwnec1i/fzy826gjBEqOfxVf51V57
wmkdZwhZIiACDf/1uCvxzlU=
-----END PRIVATE KEY-----';
    private $firebaseProjectId = 'vms-crm-notifications';

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'endpoint' => 'required|string',
                'keys' => 'required|array',
                'keys.p256dh' => 'required|string',
                'keys.auth' => 'required|string',
            ]);

            // Store push subscription in session
            $subscription = [
                'endpoint' => $request->endpoint,
                'keys' => $request->keys,
                'subscribed_at' => now()->toISOString(),
                'user_id' => $user->user_id,
            ];

            session(['push_subscription' => $subscription]);

            Log::info("User {$user->user_id} subscribed to push notifications");

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to push notifications',
                'vapid_public_key' => 'BNUSY-e9yHJJq1URqcCsR5dWgv4RecL74SabGdR0T1JLtJnD4GRtDScNcit5A9RDeD0XOpGpkf_V3VXiPkV9XS8'
            ]);
        } catch (\Exception $e) {
            Log::error('Push subscription error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to subscribe'], 500);
        }
    }

    /**
     * Send push notification to specific user (FIREBASE FCM TOKENS)
     */
    public function sendPushNotificationToUser($userId, $title, $body, $data = [])
    {
        try {
            Log::info("🚀 SENDING FIREBASE FCM NOTIFICATION to user {$userId}");
            
            // Get user's FCM token from session storage
            $fcmToken = $this->getUserFCMToken($userId);
            
            if (!$fcmToken) {
                Log::warning("No FCM token found for user {$userId}");
                return ['success' => false, 'message' => 'No FCM token found for user'];
            }
            
            Log::info("📱 Found FCM token for user {$userId}, sending Firebase FCM notification");
            
            // Use Firebase Cloud Messaging API with Service Account
            $result = $this->sendFirebaseFCMNotification($fcmToken, $title, $body, $data);
            
            if ($result['success']) {
                Log::info('🎉 FIREBASE FCM NOTIFICATION SENT SUCCESSFULLY to user ' . $userId);
                return ['success' => true, 'message' => 'Firebase FCM notification sent successfully!'];
            } else {
                Log::error('❌ Firebase FCM notification failed for user ' . $userId . ': ' . $result['error']);
                return ['success' => false, 'message' => 'Failed to send: ' . $result['error']];
            }
            
        } catch (\Exception $e) {
            Log::error('Send Firebase FCM notification error for user ' . $userId . ': ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error sending Firebase FCM notification: ' . $e->getMessage()];
        }
    }

    /**
     * Get user's FCM token from session storage
     */
    private function getUserFCMToken($userId)
    {
        try {
            // Get all sessions from storage/framework/sessions
            $sessionsPath = storage_path('framework/sessions');
            
            if (!is_dir($sessionsPath)) {
                Log::warning("Sessions directory not found: {$sessionsPath}");
                return null;
            }
            
            $sessionFiles = glob($sessionsPath . '/*');
            Log::info("Checking " . count($sessionFiles) . " session files for user {$userId} FCM token");
            
            foreach ($sessionFiles as $sessionFile) {
                try {
                    if (!is_file($sessionFile)) {
                        continue;
                    }
                    
                    $sessionData = file_get_contents($sessionFile);
                    $decoded = @unserialize($sessionData);
                    
                    if ($decoded === false) {
                        $decoded = @unserialize(base64_decode($sessionData));
                    }
                    
                    // Check if this session has FCM token for our user
                    if (is_array($decoded) && isset($decoded['fcm_token'])) {
                        // Look for login session key that matches our user ID
                        // The actual session key format is: login_web_[provider_hash]
                        $loginKey = 'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';
                        Log::info("Checking session file: " . basename($sessionFile) . " for user {$userId}");
                        Log::info("Login key: {$loginKey}");
                        Log::info("Session login value: " . (isset($decoded[$loginKey]) ? $decoded[$loginKey] : 'NOT_SET'));
                        Log::info("Target user ID: {$userId}");
                        
                        if (isset($decoded[$loginKey]) && $decoded[$loginKey] == $userId) {
                            Log::info("✅ Found FCM token for user {$userId} in session file: " . basename($sessionFile));
                            return $decoded['fcm_token'];
                        }
                    }
                    
                } catch (\Exception $fileError) {
                    continue;
                }
            }
            
            Log::info("❌ No FCM token found for user {$userId} after checking all sessions");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error getting FCM token for user {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's push subscription from session storage
     */
    private function getUserPushSubscription($userId)
    {
        try {
            // Get all sessions from storage/framework/sessions (correct Laravel path)
            $sessionsPath = storage_path('framework/sessions');
            
            if (!is_dir($sessionsPath)) {
                Log::warning("Sessions directory not found: {$sessionsPath}");
                return null;
            }
            
            $sessionFiles = glob($sessionsPath . '/*');
            Log::info("Checking " . count($sessionFiles) . " session files for user {$userId} subscription");
            
            foreach ($sessionFiles as $sessionFile) {
                try {
                    // Skip if not a file
                    if (!is_file($sessionFile)) {
                        continue;
                    }
                    
                    // Read and decode session file
                    $sessionData = file_get_contents($sessionFile);
                    
                    // Laravel sessions are serialized differently, try to unserialize
                    $decoded = @unserialize($sessionData);
                    
                    // If unserialize fails, try base64 decode first (some Laravel configs)
                    if ($decoded === false) {
                        $decoded = @unserialize(base64_decode($sessionData));
                    }
                    
                    // Check if this session has push subscription for our user
                    if (is_array($decoded) && 
                        isset($decoded['push_subscription']) && 
                        isset($decoded['push_subscription']['user_id']) && 
                        $decoded['push_subscription']['user_id'] == $userId) {
                        
                        Log::info("✅ Found push subscription for user {$userId} in session file: " . basename($sessionFile));
                        return $decoded['push_subscription'];
                    }
                    
                } catch (\Exception $fileError) {
                    // Skip this file if there's an error reading it
                    continue;
                }
            }
            
            Log::info("❌ No push subscription found for user {$userId} after checking all sessions");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error getting push subscription for user {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send push notification directly (FIREBASE FCM TOKENS)
     */
    public function sendPushNotification(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            
            $fcmToken = session('fcm_token');
            
            if (!$fcmToken) {
                Log::warning('No FCM token found for current user');
                return response()->json(['success' => false, 'message' => 'No FCM token found. Please refresh the page to enable notifications.']);
            }
            
            Log::info('🚀 SENDING FIREBASE FCM TEST NOTIFICATION!');
            Log::info('FCM Token: ' . substr($fcmToken, 0, 20) . '...');
            
            $request->validate([
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'nullable|array',
            ]);
            
            // Format test data properly for FCM
            $testData = [
                'test' => 'true',
                'type' => 'test_notification',
                'source' => 'vms-crm-test'
            ];
            
            // Merge with any provided data
            $allData = array_merge($testData, $request->data ?? []);
            
            // Use Firebase FCM notification
            $result = $this->sendFirebaseFCMNotification($fcmToken, $request->title, $request->body, $allData);
            
            if ($result['success']) {
                Log::info('🎉 FIREBASE FCM TEST NOTIFICATION SENT SUCCESSFULLY!');
                return response()->json(['success' => true, 'message' => 'Firebase FCM notification sent successfully!']);
            } else {
                Log::error('❌ Firebase FCM test notification failed: ' . $result['error']);
                return response()->json(['success' => false, 'message' => 'Failed to send: ' . $result['error']]);
            }
            
        } catch (\Exception $e) {
            Log::error('Send FCM notification error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error sending FCM notification: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send Firebase FCM notification using FCM token
     */
    private function sendFirebaseFCMNotification($fcmToken, $title, $body, $data = [])
    {
        try {
            Log::info('🔔 Sending Firebase FCM notification with FCM token');
            
            // Step 1: Get access token using Service Account
            $accessToken = $this->getFirebaseAccessToken();
            
            if (!$accessToken) {
                return ['success' => false, 'error' => 'Failed to get Firebase access token'];
            }
            
            Log::info('✅ Firebase access token obtained successfully');
            
            // Step 2: Send FCM notification with proper FCM token
            $fcmData = $this->formatFCMData($data);
            
            $fcmPayload = [
                'message' => [
                    'token' => $fcmToken, // Use FCM token directly
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => $fcmData,
                    'android' => [
                        'notification' => [
                            'icon' => '/favicon.ico',
                            'sound' => 'default',
                            'channel_id' => 'vms-notifications'
                        ]
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon' => '/favicon.ico',
                            'badge' => '/favicon.ico',
                            'requireInteraction' => true,
                            'actions' => [
                                ['action' => 'open', 'title' => '📱 Open App'],
                                ['action' => 'view', 'title' => '👀 View Assignment'],
                                ['action' => 'dismiss', 'title' => '❌ Dismiss']
                            ]
                        ]
                    ]
                ]
            ];
            
            $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->firebaseProjectId}/messages:send";
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ];
            
            Log::info('FCM URL: ' . $fcmUrl);
            Log::info('FCM Token: ' . substr($fcmToken, 0, 20) . '...');
            Log::info('FCM Headers: ' . json_encode($headers));
            Log::info('FCM Payload: ' . json_encode($fcmPayload));
            
            // Send FCM notification
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            Log::info("FCM response: HTTP $httpCode - $response");
            
            if ($curlError) {
                return ['success' => false, 'error' => "cURL Error: $curlError"];
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('✅ Firebase FCM notification sent successfully');
                return ['success' => true, 'response' => $response];
            } else {
                return ['success' => false, 'error' => "HTTP $httpCode: $response"];
            }
            
        } catch (\Exception $e) {
            Log::error('Firebase FCM notification error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Firebase Cloud Messaging push notification using Service Account
     */
    private function sendFirebasePushNotification($subscription, $title, $body, $data = [])
    {
        try {
            Log::info('🔔 Sending Firebase Cloud Messaging notification with Service Account');
            
            // Step 1: Get access token using Service Account
            $accessToken = $this->getFirebaseAccessToken();
            
            if (!$accessToken) {
                return ['success' => false, 'error' => 'Failed to get Firebase access token'];
            }
            
            Log::info('✅ Firebase access token obtained successfully');
            
            // Step 2: Extract FCM token from Web Push endpoint
            $fcmToken = $this->extractFCMToken($subscription['endpoint']);
            
            if (!$fcmToken) {
                return ['success' => false, 'error' => 'Could not extract FCM token from endpoint'];
            }
            
            // Step 3: Send FCM notification
            // FCM requires all data values to be strings
            $fcmData = $this->formatFCMData($data);
            
            $fcmPayload = [
                'message' => [
                    'token' => $fcmToken, // Use extracted FCM token
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => $fcmData, // Properly formatted data
                    'android' => [
                        'notification' => [
                            'icon' => '/favicon.ico',
                            'sound' => 'default',
                            'channel_id' => 'vms-notifications'
                        ]
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon' => '/favicon.ico',
                            'badge' => '/favicon.ico',
                            'requireInteraction' => true,
                            'actions' => [
                                ['action' => 'open', 'title' => '📱 Open App'],
                                ['action' => 'view', 'title' => '👀 View Assignment'],
                                ['action' => 'dismiss', 'title' => '❌ Dismiss']
                            ]
                        ]
                    ]
                ]
            ];
            
            $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->firebaseProjectId}/messages:send";
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ];
            
            Log::info('FCM URL: ' . $fcmUrl);
            Log::info('FCM Token: ' . substr($fcmToken, 0, 20) . '...');
            Log::info('FCM Headers: ' . json_encode($headers));
            Log::info('FCM Payload: ' . json_encode($fcmPayload));
            
            // Send FCM notification
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            Log::info("FCM response: HTTP $httpCode - $response");
            
            if ($curlError) {
                return ['success' => false, 'error' => "cURL Error: $curlError"];
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('✅ Firebase Cloud Messaging notification sent successfully');
                return ['success' => true, 'response' => $response];
            } else {
                return ['success' => false, 'error' => "HTTP $httpCode: $response"];
            }
            
        } catch (\Exception $e) {
            Log::error('Firebase Cloud Messaging error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Extract FCM token from Web Push endpoint
     */
    private function extractFCMToken($endpoint)
    {
        // Extract FCM token from endpoint URL
        // Web Push endpoint format: https://fcm.googleapis.com/fcm/send/TOKEN_HERE
        if (preg_match('/\/send\/(.+)$/', $endpoint, $matches)) {
            $token = $matches[1];
            Log::info("✅ FCM token extracted from endpoint: " . substr($token, 0, 20) . "...");
            return $token;
        }
        
        Log::error("❌ Could not extract FCM token from endpoint: " . $endpoint);
        return null;
    }

    /**
     * Format data for FCM - all values must be strings
     */
    private function formatFCMData($data)
    {
        $fcmData = [];
        
        // Add default data fields
        $defaultData = [
            'url' => '/staff/assigned-to-me',
            'timestamp' => (string)time(),
            'app' => 'vms-crm'
        ];
        
        // Merge with provided data
        $allData = array_merge($defaultData, $data);
        
        // Convert all values to strings (FCM requirement)
        foreach ($allData as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to JSON strings
                $fcmData[$key] = json_encode($value);
            } elseif (is_bool($value)) {
                // Convert booleans to strings
                $fcmData[$key] = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                // Convert null to empty string
                $fcmData[$key] = '';
            } else {
                // Convert everything else to string
                $fcmData[$key] = (string)$value;
            }
        }
        
        Log::info('FCM data formatted: ' . json_encode($fcmData));
        return $fcmData;
    }

    /**
     * Get Firebase access token using Service Account JWT
     */
    private function getFirebaseAccessToken()
    {
        try {
            Log::info('🔑 Getting Firebase access token using Service Account');
            
            // Create JWT for OAuth2 token request
            $payload = [
                'iss' => $this->serviceAccountEmail,
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => time() + 3600,
                'iat' => time(),
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
            ];
            
            $jwt = JWT::encode($payload, $this->serviceAccountPrivateKey, 'RS256');
            
            Log::info('✅ JWT created for Firebase access token request');
            
            // Exchange JWT for access token
            $tokenUrl = 'https://oauth2.googleapis.com/token';
            $tokenData = [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ];
            
            $headers = [
                'Content-Type: application/x-www-form-urlencoded'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            Log::info("OAuth2 token response: HTTP $httpCode - $response");
            
            if ($curlError) {
                Log::error('OAuth2 token cURL error: ' . $curlError);
                return null;
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $tokenData = json_decode($response, true);
                if (isset($tokenData['access_token'])) {
                    Log::info('✅ Firebase access token obtained successfully');
                    return $tokenData['access_token'];
                }
            }
            
            Log::error('Failed to get Firebase access token: ' . $response);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Firebase access token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store FCM token from Firebase Web SDK
     */
    public function storeFCMToken(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'fcm_token' => 'required|string',
            ]);

            // Store FCM token in session
            session(['fcm_token' => $request->fcm_token]);

            Log::info("User {$user->user_id} stored FCM token: " . substr($request->fcm_token, 0, 20) . "...");

            return response()->json([
                'success' => true,
                'message' => 'FCM token stored successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Store FCM token error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to store FCM token'], 500);
        }
    }

    /**
     * Get push notification status
     */
    public function getStatus(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Check for FCM token (Firebase approach)
            $fcmToken = session('fcm_token');
            $isSubscribed = !empty($fcmToken);

            // Also check for legacy push subscription for compatibility
            $subscription = session('push_subscription');

            return response()->json([
                'success' => true,
                'isSubscribed' => $isSubscribed,
                'fcm_token' => $fcmToken ? substr($fcmToken, 0, 20) . '...' : null,
                'subscription' => $subscription,
                'vapid_public_key' => 'BNUSY-e9yHJJq1URqcCsR5dWgv4RecL74SabGdR0T1JLtJnD4GRtDScNcit5A9RDeD0XOpGpkf_V3VXiPkV9XS8'
            ]);
        } catch (\Exception $e) {
            Log::error('Get push status error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get status'], 500);
        }
    }
}