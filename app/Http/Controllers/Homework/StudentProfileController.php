<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\HomeworkUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StudentProfileController extends Controller
{
    /**
     * Show the student profile page
     */
    public function show()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('homework.login');
        }
        
        // Get classes directly from database to ensure we have fresh data
        $classes = \App\Models\SchoolClass::whereHas('students', function($query) use ($student) {
            $query->where('homework_users.id', $student->id);
        })->select('school_classes.*')->get();
        
        // Load relationships
        $student->load(['phoneNumbers']);
        
        // Set the classes relationship manually
        $student->setRelation('schoolClasses', $classes);
        
        return view('homework.student.profile', compact('student', 'classes'));
    }

    /**
     * Update student password
     */
    public function updatePassword(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('homework.login');
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $student->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ])->withInput();
        }

        // Update password (both hashed and plain)
        $student->password = Hash::make($validated['password']);
        $student->password_plain = $validated['password']; // Keep plain text for admin visibility
        $student->save();

        return redirect()->route('homework.student.profile')
            ->with('success', 'Password updated successfully.');
    }
}

