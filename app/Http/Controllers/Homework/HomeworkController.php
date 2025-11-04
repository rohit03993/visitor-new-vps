<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkNotification;
use App\Models\HomeworkUser;
use App\Models\HomeworkUserPhoneNumber;
use App\Models\SchoolClass;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeworkController extends Controller
{
    public function create()
    {
        $classes = SchoolClass::all();
        
        return view('homework.homework.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        $validated = $request->validate($rules);

        // Auto-detect homework type based on file upload
        $homeworkType = 'text'; // Default
        if ($request->hasFile('file')) {
            $mimeType = $request->file('file')->getMimeType();
            if ($mimeType === 'application/pdf') {
                $homeworkType = 'pdf';
            } elseif (str_starts_with($mimeType, 'image/')) {
                $homeworkType = 'image';
            }
        }

        // Get or create HomeworkUser for the current staff/admin
        $staffUser = Auth::guard('web')->user();
        $homeworkUser = HomeworkUser::firstOrCreate(
            ['roll_number' => 'STAFF-' . $staffUser->user_id], // Unique identifier
            [
                'name' => $staffUser->name,
                'roll_number' => 'STAFF-' . $staffUser->user_id,
                'role' => $staffUser->isAdmin() ? 'admin' : 'teacher',
                'password' => bcrypt('temp'), // Temp password, not used for login
                'mobile_number' => $staffUser->mobile_number ?? '+910000000000',
            ]
        );
        
        $homework = new Homework();
        $homework->class_id = $validated['class_id'];
        $homework->teacher_id = $homeworkUser->id; // Use HomeworkUser ID
        $homework->title = $validated['title'];
        $homework->description = $validated['description'];
        $homework->type = $homeworkType;

        // Handle file uploads if provided
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('homework', 'public');
            $homework->file_path = $path;
        }

        $homework->save();

        // Send notifications to all students in the class
        $class = SchoolClass::with('students')->find($validated['class_id']);
        $students = $class->students; // This will return Visitor models
        
        $notificationCount = 0;
        $whatsappCount = 0;
        
        if ($students->count() > 0) {
            $whatsappService = new AiSensyWhatsAppService();
            $homeworkLink = route('homework.homework.show', $homework->id);
            
            foreach ($students as $student) {
                // Create in-app notification
                HomeworkNotification::create([
                    'student_id' => $student->id,
                    'homework_id' => $homework->id,
                    'title' => 'New Homework: ' . $homework->title,
                    'message' => 'A new homework has been uploaded in ' . $homework->schoolClass->name,
                    'is_read' => false,
                ]);
                $notificationCount++;
                
                // Send WhatsApp notification only if send_whatsapp is checked
                if ($request->has('send_whatsapp') && $request->send_whatsapp) {
                    // Send to primary mobile_number
                    if (!empty($student->mobile_number)) {
                        $result = $whatsappService->sendHomeworkNotification(
                            $student->mobile_number,
                            $student->name,
                            $homework->title,
                            $homeworkLink
                        );
                        
                        if ($result['success'] ?? false) {
                            $whatsappCount++;
                        }
                    }
                    
                    // Send to additional phone numbers
                    $additionalPhones = $student->phoneNumbers()->where('whatsapp_enabled', true)->get();
                    foreach ($additionalPhones as $phoneNumber) {
                        $result = $whatsappService->sendHomeworkNotification(
                            $phoneNumber->phone_number,
                            $student->name,
                            $homework->title,
                            $homeworkLink
                        );
                        
                        if ($result['success'] ?? false) {
                            $whatsappCount++;
                        }
                    }
                }
            }
        }

        $message = 'Homework uploaded successfully!';
        if ($notificationCount > 0) {
            $message .= ' ' . $notificationCount . ' in-app notifications sent.';
            if ($whatsappCount > 0) {
                $message .= ' ' . $whatsappCount . ' WhatsApp messages sent.';
            }
        } else {
            $message .= ' No students enrolled in this class yet.';
        }

        return redirect()->route('homework.dashboard')->with('success', $message);
    }

    public function index(Request $request)
    {
        // Check if authenticated as staff/admin (web guard)
        $staffUser = Auth::guard('web')->user();
        // Check if authenticated as student (student guard)
        $student = Auth::guard('student')->user();
        
        if ($staffUser) {
            // Staff/Admin logic - Both admin and staff see ALL homework (they act as admin in homework section)
            if ($staffUser->isAdmin() || $staffUser->isStaff()) {
                // Admin and Staff can see all homework
                $homeworkQuery = Homework::with(['schoolClass.students', 'teacher', 'views']);
            } else {
                $homeworkQuery = Homework::whereRaw('1 = 0'); // Empty query
            }
        } elseif ($student) {
            // Students can see homework for their classes
            $classes = $student->schoolClasses()->get();
            $classIds = $classes->pluck('id');
            if ($classIds->isNotEmpty()) {
                $homeworkQuery = Homework::whereIn('class_id', $classIds)
                    ->with(['schoolClass', 'teacher', 'views']);
            } else {
                $homeworkQuery = Homework::whereRaw('1 = 0'); // Empty query
            }
        } else {
            $homeworkQuery = Homework::whereRaw('1 = 0'); // Empty query
        }
        
        // Apply class filter if provided
        if ($request->has('class_id') && $request->class_id) {
            $homeworkQuery->where('class_id', $request->class_id);
        }
        
        $homework = $homeworkQuery->latest()->paginate(10)->withQueryString();

        return view('homework.homework.index', compact('homework'));
    }

    public function show(Homework $homework)
    {
        $homework->load('schoolClass', 'teacher');
        
        // Check if authenticated as student
        $student = Auth::guard('student')->user();
        
        // Mark as viewed if student
        if ($student) {
            $homework->views()->firstOrCreate([
                'student_id' => $student->id,
                'homework_id' => $homework->id,
            ], [
                'viewed_at' => now(),
            ]);

            // Mark notification as read
            HomeworkNotification::where('student_id', $student->id)
                ->where('homework_id', $homework->id)
                ->update(['is_read' => true]);
        }

        return view('homework.homework.show', compact('homework'));
    }

    public function studentStats(Homework $homework)
    {
        $staffUser = Auth::guard('web')->user();
        
        // Admin and Staff can view stats for all homework (they act as admin in homework section)
        if (!$staffUser || (!$staffUser->isAdmin() && !$staffUser->isStaff())) {
            abort(403, 'Unauthorized action.');
        }

        $homework->load(['schoolClass.students', 'teacher', 'views']);
        
        // Get all students in the class
        $students = $homework->schoolClass->students;
        
        // Get viewed student IDs
        $viewedStudentIds = $homework->views->pluck('student_id')->toArray();
        
        // Separate viewed and not viewed students
        $viewedStudents = $students->filter(function($student) use ($viewedStudentIds) {
            return in_array($student->id, $viewedStudentIds);
        });
        
        $notViewedStudents = $students->filter(function($student) use ($viewedStudentIds) {
            return !in_array($student->id, $viewedStudentIds);
        });
        
        return view('homework.homework.stats', compact('homework', 'viewedStudents', 'notViewedStudents'));
    }

    public function edit(Homework $homework)
    {
        $staffUser = Auth::guard('web')->user();
        
        // Admin and Staff can edit all homework (they act as admin in homework section)
        if (!$staffUser || (!$staffUser->isAdmin() && !$staffUser->isStaff())) {
            abort(403, 'Unauthorized action.');
        }

        $classes = SchoolClass::all();
        
        return view('homework.homework.edit', compact('homework', 'classes'));
    }

    public function update(Request $request, Homework $homework)
    {
        $staffUser = Auth::guard('web')->user();
        
        // Admin and Staff can update all homework (they act as admin in homework section)
        if (!$staffUser || (!$staffUser->isAdmin() && !$staffUser->isStaff())) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        $validated = $request->validate($rules);

        // Auto-detect homework type based on file upload
        $homeworkType = 'text'; // Default
        if ($request->hasFile('file')) {
            $mimeType = $request->file('file')->getMimeType();
            if ($mimeType === 'application/pdf') {
                $homeworkType = 'pdf';
            } elseif (str_starts_with($mimeType, 'image/')) {
                $homeworkType = 'image';
            }
        } else {
            // Keep existing type if no new file uploaded
            $homeworkType = $homework->type;
        }

        // Handle file uploads (only if new file is uploaded)
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($homework->file_path) {
                Storage::disk('public')->delete($homework->file_path);
            }
            $path = $request->file('file')->store('homework', 'public');
            $homework->file_path = $path;
        } elseif ($homeworkType === 'text' && $homework->file_path) {
            // If no file and changing to text, delete the old file
            Storage::disk('public')->delete($homework->file_path);
            $homework->file_path = null;
        }

        $homework->class_id = $validated['class_id'];
        $homework->title = $validated['title'];
        $homework->description = $validated['description'];
        $homework->type = $homeworkType;

        $homework->save();

        return redirect()->route('homework.dashboard')->with('success', 'Homework updated successfully!');
    }

    public function destroy(Homework $homework)
    {
        $staffUser = Auth::guard('web')->user();
        
        // Admin and Staff can delete all homework (they act as admin in homework section)
        if (!$staffUser || (!$staffUser->isAdmin() && !$staffUser->isStaff())) {
            abort(403, 'Unauthorized action.');
        }

        // Delete file if exists
        if ($homework->file_path) {
            Storage::disk('public')->delete($homework->file_path);
        }

        $homework->delete();

        return redirect()->route('homework.dashboard')->with('success', 'Homework deleted successfully!');
    }

    public function download(Homework $homework)
    {
        if (!$homework->file_path) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($homework->file_path);
    }
}

