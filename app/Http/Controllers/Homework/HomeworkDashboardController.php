<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\SchoolClass;
use App\Models\HomeworkUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeworkDashboardController extends Controller
{
    public function index()
    {
        // Check if authenticated as staff/admin (web guard)
        $staffUser = Auth::guard('web')->user();
        // Check if authenticated as student (student guard)
        $student = Auth::guard('student')->user();
        
        if ($staffUser) {
            // Staff/Admin logic - ALL staff and admin see admin dashboard
            if ($staffUser->isAdmin() || $staffUser->isStaff()) {
                return $this->adminDashboard($staffUser);
            } else {
                abort(403, 'Unauthorized');
            }
        } elseif ($student) {
            // Student logic
            return $this->studentDashboard($student);
        } else {
            // Not authenticated - redirect to login
            return redirect()->route('homework.login');
        }
    }

    private function adminDashboard($user)
    {
        $stats = [
            'total_students' => HomeworkUser::where('role', 'student')->count(),
            'total_teachers' => HomeworkUser::whereIn('role', ['admin', 'teacher'])->count(),
            'total_classes' => SchoolClass::count(),
            'total_homework' => Homework::count(),
        ];

        return view('homework.admin.dashboard', compact('stats'));
    }

    private function teacherDashboard($user)
    {
        // Get all classes for teachers to upload homework
        $classes = SchoolClass::with('homework.teacher')->get();

        return view('homework.teacher.dashboard', compact('classes'));
    }

    private function studentDashboard($student)
    {
        // Get classes this student is enrolled in
        $classes = $student->schoolClasses()->get();
        
        // Get date filter from request or use defaults
        $selectedDate = request('date_filter');
        
        // Get homework for all student's classes
        $classIds = $classes->pluck('id');
        $homeworkQuery = Homework::whereIn('class_id', $classIds)
            ->with('schoolClass', 'teacher');
        
        // Apply date filter if provided
        if ($selectedDate) {
            $homeworkQuery->whereDate('created_at', $selectedDate);
        }
        
        $allHomework = $homeworkQuery->latest()->get();
        
        // Separate assigned (unopened) and viewed homework
        $viewedHomeworkIds = $student->homeworkViews()->pluck('homework_id')->toArray();
        
        $assignedHomework = $allHomework->filter(function($hw) use ($viewedHomeworkIds) {
            return !in_array($hw->id, $viewedHomeworkIds);
        })->take(10);
        
        $viewedHomework = $allHomework->filter(function($hw) use ($viewedHomeworkIds) {
            return in_array($hw->id, $viewedHomeworkIds);
        })->take(10);
        
        // Get unread notifications
        $unreadCount = $student->homeworkNotifications()->where('is_read', false)->count();
        
        return view('homework.student.dashboard', compact('classes', 'assignedHomework', 'viewedHomework', 'unreadCount', 'selectedDate'));
    }
}

