<?php

namespace App\Http\Controllers\Homework\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassStudent;
use App\Models\HomeworkUser;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::withCount(['students', 'homework'])->latest()->get();
        return view('homework.admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('homework.admin.classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class = SchoolClass::create($validated);

        return redirect()->route('homework.admin.classes.index')->with('success', 'Class created successfully!');
    }

    public function show(SchoolClass $class)
    {
        $class->load('students', 'homework.teacher');
        return view('homework.admin.classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        return view('homework.admin.classes.edit', compact('class'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class->update($validated);

        return redirect()->route('homework.admin.classes.index')->with('success', 'Class updated successfully!');
    }

    public function destroy(SchoolClass $class)
    {
        // SAFETY CHECK: Prevent deletion if class has students or homework
        $studentCount = $class->students()->count();
        $homeworkCount = $class->homework()->count();
        
        if ($studentCount > 0 || $homeworkCount > 0) {
            $message = 'Cannot delete class. ';
            $reasons = [];
            
            if ($studentCount > 0) {
                $reasons[] = "It has {$studentCount} student(s) enrolled";
            }
            if ($homeworkCount > 0) {
                $reasons[] = "It has {$homeworkCount} homework assignment(s)";
            }
            
            $message .= implode(' and ', $reasons) . '.';
            
            return redirect()->route('homework.admin.classes.index')
                ->with('error', $message);
        }
        
        $class->delete();
        return redirect()->route('homework.admin.classes.index')->with('success', 'Class deleted successfully!');
    }

    public function assignStudents(SchoolClass $class)
    {
        // Get all homework users with role 'student'
        $students = HomeworkUser::where('role', 'student')->orderBy('name')->get();
        $assignedStudentIds = $class->students->pluck('id')->toArray();
        return view('homework.admin.classes.assign-students', compact('class', 'students', 'assignedStudentIds'));
    }

    public function storeStudents(Request $request, SchoolClass $class)
    {
        // Remove all existing students
        ClassStudent::where('class_id', $class->id)->delete();

        // Add new students
        if ($request->has('student_ids')) {
            foreach ($request->student_ids as $studentId) {
                ClassStudent::create([
                    'class_id' => $class->id,
                    'student_id' => $studentId, // This is homework_users.id
                ]);
            }
        }

        return redirect()->route('homework.admin.classes.show', $class)->with('success', 'Students assigned successfully!');
    }
}

