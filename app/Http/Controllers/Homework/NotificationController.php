<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Check if authenticated as student
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            abort(403, 'Unauthorized');
        }
        
        $notifications = $student->homeworkNotifications()
            ->with('homework.schoolClass')
            ->latest()
            ->paginate(15);
        
        return view('homework.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            abort(403, 'Unauthorized');
        }
        
        $notification = $student->homeworkNotifications()->findOrFail($id);
        $notification->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            abort(403, 'Unauthorized');
        }
        
        $student->homeworkNotifications()->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
}

