<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\HomeworkUser;
use App\Models\HomeworkUserPhoneNumber;
use App\Models\SchoolClass;
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

    /**
     * Show student registration form
     */
    public function showRegistrationForm()
    {
        // If already logged in as student, redirect to dashboard
        if (Auth::guard('student')->check()) {
            return redirect()->route('homework.dashboard');
        }

        // If staff/admin is logged in, redirect them to dashboard
        if (Auth::guard('web')->check()) {
            return redirect()->route('homework.dashboard');
        }

        // Get all active classes for selection
        $classes = SchoolClass::orderBy('name')->get();

        return view('homework.auth.register', compact('classes'));
    }

    /**
     * Handle student registration
     */
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'roll_number' => 'required|string|max:255|unique:homework_users,roll_number',
            'class_id' => 'required|exists:school_classes,id',
            'mobile_number' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                function ($attribute, $value, $fail) {
                    // Check if mobile number exists in homework_users.mobile_number (check both formats)
                    $existsInMain = HomeworkUser::where('mobile_number', $value)
                        ->orWhere('mobile_number', '+91' . $value)
                        ->exists();
                    
                    // Check if mobile number exists in homework_user_phone_numbers (check both formats)
                    $existsInPhoneNumbers = HomeworkUserPhoneNumber::where('phone_number', $value)
                        ->orWhere('phone_number', '+91' . $value)
                        ->exists();
                    
                    if ($existsInMain || $existsInPhoneNumbers) {
                        $fail('This mobile number is already registered. Please use a different number.');
                    }
                },
            ],
        ]);

        // Default password
        $defaultPassword = '12345678';

        // Create the student user
        $student = HomeworkUser::create([
            'name' => $validated['name'],
            'roll_number' => $validated['roll_number'],
            'mobile_number' => $validated['mobile_number'],
            'password' => Hash::make($defaultPassword),
            'password_plain' => $defaultPassword, // Store plain password for admin visibility
            'role' => 'student',
        ]);

        // Assign the selected class
        $student->schoolClasses()->attach($validated['class_id']);

        // Auto-login the student
        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        // Redirect to dashboard with success message showing credentials
        return redirect()->route('homework.dashboard')->with('registration_success', [
            'message' => 'Registration successful! Your login credentials are:',
            'roll_number' => $student->roll_number,
            'password' => $defaultPassword,
        ]);
    }
}
