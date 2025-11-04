<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\HomeworkUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    /**
     * Show student login form
     */
    public function showLoginForm()
    {
        // If already logged in as student, redirect to dashboard
        if (Auth::guard('student')->check()) {
            return redirect()->route('homework.dashboard');
        }

        // If staff/admin is logged in, redirect them to dashboard (not student login)
        if (Auth::guard('web')->check()) {
            return redirect()->route('homework.dashboard');
        }

        return view('homework.auth.login');
    }

    /**
     * Handle student login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $rollNumber = $request->input('login');
        $password = $request->input('password');

        // Find user by roll number
        $user = HomeworkUser::where('roll_number', $rollNumber)->first();
        
        // If user found and password matches, log them in
        if ($user && Hash::check($password, $user->password)) {
            Auth::guard('student')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('homework.dashboard');
        }

        // If login failed, return with error
        return back()->withErrors([
            'login' => 'Invalid credentials. Please check your roll number and password.',
        ])->withInput($request->only('login'));
    }

    /**
     * Handle student logout
     */
    public function logout(Request $request)
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('homework.login');
    }
}
