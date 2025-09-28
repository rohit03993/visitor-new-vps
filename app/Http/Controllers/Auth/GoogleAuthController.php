<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google for authorization
     */
    public function redirectToGoogle()
    {
        try {
            // Debug: Log the attempt
            \Log::info('Google OAuth redirect attempted');
            
            // Check if user is logged in
            $user = Auth::user();
            if (!$user) {
                \Log::info('User not authenticated, redirecting to login');
                return redirect()->route('login')->with('error', 'Please login first to authorize Google Drive.');
            }
            
            \Log::info('User authenticated: ' . $user->name);
            
            // Create direct Google OAuth URL
            $clientId = env('GOOGLE_CLIENT_ID');
            $redirectUri = env('APP_ENV') === 'local' 
                ? env('GOOGLE_REDIRECT_URI_LOCAL', 'http://localhost:8000/auth/google/callback')
                : env('GOOGLE_REDIRECT_URI_PROD', 'https://motionagra.com/auth/google/callback');
            
            $scope = 'https://www.googleapis.com/auth/drive.file';
            $state = 'google_oauth_' . time();
            
            $googleUrl = "https://accounts.google.com/oauth/authorize?" . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'response_type' => 'code',
                'state' => $state,
                'access_type' => 'offline',
                'prompt' => 'consent'
            ]);
            
            \Log::info('Direct Google OAuth URL: ' . $googleUrl);
            
            return redirect($googleUrl);
                
        } catch (\Exception $e) {
            \Log::error('Google OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('staff.visitor-search')->with('error', 'Google OAuth setup error: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle Google callback and store tokens
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $currentUser = Auth::user();
            
            if (!$currentUser || !$currentUser->isAdmin()) {
                return redirect()->route('login')->with('error', 'Only administrators can authorize Google Drive access.');
            }
            
            // Store or update the tokens
            DB::table('google_drive_tokens')->updateOrInsert(
                ['user_id' => $currentUser->user_id],
                [
                    'access_token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                    'expires_in' => 3600, // 1 hour
                    'expires_at' => now()->addHour(),
                    'scope' => 'https://www.googleapis.com/auth/drive.file',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            
            return redirect()->route('staff.visitor-search')->with('success', 'Google Drive authorization successful! You can now upload files.');
            
        } catch (\Exception $e) {
            \Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('staff.visitor-search')->with('error', 'Google authorization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if Google Drive is authorized
     */
    public function checkAuthorization()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['authorized' => false, 'message' => 'Not logged in']);
        }
        
        $token = DB::table('google_drive_tokens')
            ->where('user_id', $user->user_id)
            ->first();
        
        if (!$token) {
            return response()->json(['authorized' => false, 'message' => 'Google Drive not authorized']);
        }
        
        return response()->json(['authorized' => true, 'message' => 'Google Drive authorized']);
    }
}
