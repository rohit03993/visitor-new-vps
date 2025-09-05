<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VmsUser;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Your account has been deactivated. Please contact administrator.',
                ])->withInput($request->only('username'));
            }
            
            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isFrontDesk()) {
                return redirect()->route('frontdesk.dashboard');
            } elseif ($user->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->withInput($request->only('username'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
