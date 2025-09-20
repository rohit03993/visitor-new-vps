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
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/drive.file'])
            ->redirect();
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
