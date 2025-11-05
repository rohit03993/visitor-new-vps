<?php

namespace App\Http\Controllers\Homework\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassStudent;
use App\Models\SchoolClass;
use App\Models\HomeworkUser;
use App\Models\HomeworkUserPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeworkUser::with(['schoolClasses', 'phoneNumbers'])->withCount(['schoolClasses', 'homeworkNotifications']);
        
        // Only show students (exclude admin and teacher roles)
        $query->where('role', 'student');
        
        // Filter by class if provided
        if ($request->has('class_id') && $request->class_id !== '') {
            $query->whereHas('schoolClasses', function($q) use ($request) {
                $q->where('school_classes.id', $request->class_id);
            });
        }
        
        $users = $query->latest('id')->paginate(20)->withQueryString();
        $classes = SchoolClass::all();
        
        return view('homework.admin.users.index', compact('users', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        return view('homework.admin.users.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'roll_number' => 'required|string|max:50|unique:homework_users,roll_number',
            'mobile_number' => [
                'required',
                'string',
                'regex:/^[6-9]\d{9}$/',
                function ($attribute, $value, $fail) {
                    $exists = HomeworkUser::where('mobile_number', '+91' . $value)->exists();
                    if ($exists) {
                        $fail('The mobile number has already been taken.');
                    }
                },
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone_numbers' => 'nullable|array|max:5',
            'phone_numbers.*' => 'nullable|string|regex:/^[6-9]\d{9}$/',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $user = HomeworkUser::create([
            'name' => $validated['name'],
            'mobile_number' => '+91' . $validated['mobile_number'],
            'roll_number' => $validated['roll_number'],
            'password' => Hash::make($validated['password']),
            'password_plain' => $validated['password'], // Store plain password for admin view
            'role' => 'student',
        ]);

        // Add additional phone numbers if provided
        if ($request->has('phone_numbers') && !empty($validated['phone_numbers'])) {
            foreach ($validated['phone_numbers'] as $phoneNumber) {
                if (!empty($phoneNumber)) {
                    HomeworkUserPhoneNumber::create([
                        'homework_user_id' => $user->id,
                        'phone_number' => '+91' . $phoneNumber,
                        'whatsapp_enabled' => true,
                    ]);
                }
            }
        }

        // Assign to classes
        if ($request->has('class_ids') && !empty($request->class_ids)) {
            foreach ($request->class_ids as $classId) {
                ClassStudent::create([
                    'class_id' => $classId,
                    'student_id' => $user->id,
                ]);
            }
        }

        return redirect()->route('homework.admin.users.index')->with('success', 'Student created successfully!');
    }

    public function edit(HomeworkUser $user)
    {
        $user->load(['schoolClasses']);
        $classes = SchoolClass::all();
        $additionalPhones = $user->phoneNumbers()->get();
        
        return view('homework.admin.users.edit', compact('user', 'classes', 'additionalPhones'));
    }

    public function update(Request $request, HomeworkUser $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'roll_number' => ['required', 'string', 'max:50', Rule::unique('homework_users', 'roll_number')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'mobile_number' => [
                'required', 
                'string', 
                'regex:/^[6-9]\d{9}$/',
                function ($attribute, $value, $fail) use ($user) {
                    $exists = HomeworkUser::where('mobile_number', '+91' . $value)
                        ->where('id', '!=', $user->id)
                        ->exists();
                    if ($exists) {
                        $fail('The mobile number has already been taken.');
                    }
                },
            ],
            'phone_numbers' => 'nullable|array|max:4',
            'phone_numbers.*' => 'nullable|string|regex:/^[6-9]\d{9}$/',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $user->name = $validated['name'];
        $user->mobile_number = '+91' . $validated['mobile_number'];
        $user->roll_number = $validated['roll_number'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->password_plain = $validated['password']; // Store plain password for admin view
        }

        $user->save();

        // Delete all existing additional phone numbers and recreate
        $user->phoneNumbers()->delete();

        if ($request->has('phone_numbers') && !empty($validated['phone_numbers'])) {
            foreach ($validated['phone_numbers'] as $phoneNumber) {
                if (!empty($phoneNumber)) {
                    HomeworkUserPhoneNumber::create([
                        'homework_user_id' => $user->id,
                        'phone_number' => '+91' . $phoneNumber,
                        'whatsapp_enabled' => true,
                    ]);
                }
            }
        }

        // Update class assignments for students
        if ($request->has('class_ids')) {
            $user->schoolClasses()->sync($request->class_ids);
        } else {
            $user->schoolClasses()->sync([]);
        }

        return redirect()->route('homework.admin.users.index')->with('success', 'Student updated successfully!');
    }

    public function destroy(HomeworkUser $user)
    {
        $user->delete();

        return redirect()->route('homework.admin.users.index')->with('success', 'Student deleted successfully!');
    }

    public function downloadTemplate()
    {
        $classes = SchoolClass::all();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_bulk_upload_template.csv"',
        ];

        $callback = function() use ($classes) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers - Correct order: name, roll_number, mobile_number, additional phone numbers, then classes
            $headers = ['name', 'roll_number', 'mobile_number', 'additional_phone_number_1', 'additional_phone_number_2', 'additional_phone_number_3', 'additional_phone_number_4'];
            foreach ($classes as $class) {
                $headers[] = $class->name;
            }
            fputcsv($file, $headers);
            
            // Sample row
            $sampleRow = ['John Doe', '2024001', '9876543210', '9876543211', '', '', ''];
            foreach ($classes as $class) {
                $sampleRow[] = 'YES';
            }
            fputcsv($file, $sampleRow);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function showBulkUpload()
    {
        return view('homework.admin.users.bulk-upload');
    }

    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $classes = SchoolClass::all();
        
        // Get class names for validation
        $classNames = $classes->pluck('name')->toArray();
        
        // Read CSV file
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Remove BOM if present
        if (!empty($lines[0]) && substr($lines[0], 0, 3) === "\xEF\xBB\xBF") {
            $lines[0] = substr($lines[0], 3);
        }
        
        // Get headers (first line)
        $headers = str_getcsv(array_shift($lines));
        
        // Extract class columns from headers
        $classColumns = [];
        foreach ($headers as $index => $header) {
            if (in_array(trim($header), $classNames)) {
                $classColumns[trim($header)] = $index;
            }
        }
        
        $successCount = 0;
        $errors = [];
        
        foreach ($lines as $lineNumber => $line) {
            $row = str_getcsv($line);
            
            if (count($row) < 3) {
                $errors[] = "Row " . ($lineNumber + 2) . ": Insufficient data";
                continue;
            }
            
            // Parse student data - Correct order: name, roll_number, mobile_number
            $name = isset($row[0]) ? trim($row[0]) : '';
            $rollNumber = isset($row[1]) ? trim($row[1]) : '';
            $mobileNumber = isset($row[2]) ? trim($row[2]) : '';
            
            // Parse phone numbers - additional_phone_number_1 starts at index 3 (after mobile_number)
            $phoneNumbers = [];
            for ($i = 1; $i <= 4; $i++) {
                $phoneIndex = 2 + $i; // additional_phone_number_1 is at index 3
                if (isset($row[$phoneIndex]) && !empty(trim($row[$phoneIndex]))) {
                    $phoneNumbers[] = trim($row[$phoneIndex]);
                }
            }
            
               // Validate required fields
               if (empty($name) || empty($mobileNumber) || empty($rollNumber)) {
                   $errors[] = "Row " . ($lineNumber + 2) . ": Name, mobile number, and roll number are required";
                   continue;
               }
            
            // Validate mobile number format
            if (!preg_match('/^[6-9]\d{9}$/', preg_replace('/[^0-9]/', '', $mobileNumber))) {
                $errors[] = "Row " . ($lineNumber + 2) . ": Invalid mobile number format: $mobileNumber";
                continue;
            }
            
            // Validate and clean phone numbers
            $cleanedPhoneNumbers = [];
            foreach ($phoneNumbers as $phoneNumber) {
                $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
                if (!preg_match('/^[6-9]\d{9}$/', $cleaned)) {
                    $errors[] = "Row " . ($lineNumber + 2) . ": Invalid phone number format: $phoneNumber";
                    continue 2;
                }
                $cleanedPhoneNumbers[] = $cleaned;
            }
            $phoneNumbers = $cleanedPhoneNumbers;
            
            // Clean mobile number
            $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
            if (strlen($mobileNumber) === 10) {
                $mobileNumber = '+91' . $mobileNumber;
            }
            
            try {
                $defaultPassword = 'password123';
                
                // Check if user exists by roll number or mobile
                $existingUser = HomeworkUser::where('roll_number', $rollNumber)
                    ->orWhere('mobile_number', $mobileNumber)
                    ->first();
                
                if ($existingUser) {
                    $user = $existingUser;
                    $user->name = $name;
                    $user->roll_number = $rollNumber;
                    $user->mobile_number = $mobileNumber;
                    if (!$user->password) {
                        $user->password = Hash::make($defaultPassword);
                        $user->password_plain = $defaultPassword;
                    }
                    $user->save();
                    
                    // Clear existing additional phone numbers for updates
                    $user->phoneNumbers()->delete();
                } else {
                    // Create new user
                    $user = HomeworkUser::create([
                        'name' => $name,
                        'mobile_number' => $mobileNumber,
                        'roll_number' => $rollNumber,
                        'password' => Hash::make($defaultPassword),
                        'password_plain' => $defaultPassword,
                        'role' => 'student',
                    ]);
                }
                
                // Add additional phone numbers
                foreach ($phoneNumbers as $phoneNumber) {
                    HomeworkUserPhoneNumber::create([
                        'homework_user_id' => $user->id,
                        'phone_number' => '+91' . $phoneNumber,
                        'whatsapp_enabled' => true,
                    ]);
                }
                
                // Assign to classes
                $classIds = [];
                foreach ($classColumns as $className => $columnIndex) {
                    if (isset($row[$columnIndex])) {
                        $value = strtoupper(trim($row[$columnIndex]));
                        if ($value === 'YES' || $value === 'Y') {
                            $class = $classes->firstWhere('name', $className);
                            if ($class) {
                                $classIds[] = $class->id;
                            }
                        }
                    }
                }
                
                // Remove existing class assignments and add new ones
                if (!empty($classIds)) {
                    ClassStudent::where('student_id', $user->id)->delete();
                    foreach ($classIds as $classId) {
                        ClassStudent::create([
                            'class_id' => $classId,
                            'student_id' => $user->id,
                        ]);
                    }
                }
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row " . ($lineNumber + 2) . ": " . $e->getMessage();
            }
        }
        
        // Prepare response message
        $message = "Bulk upload completed! Successfully imported $successCount student(s).";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " error(s) occurred.";
        }
        
        return redirect()->route('homework.admin.users.index')
            ->with($errors ? 'warning' : 'success', $message)
            ->with('bulk_upload_errors', $errors);
    }

    /**
     * Export all students data to CSV
     * This is a critical data backup feature - exports ALL student data
     * ADMIN ONLY - For data security and backup purposes
     */
    public function exportAllStudents()
    {
        // Double-check: Only admin can access this
        $user = auth()->guard('web')->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can export all student data.');
        }
        // Get all students with their relationships
        $students = HomeworkUser::with(['schoolClasses', 'phoneNumbers'])
            ->where('role', 'student')
            ->orderBy('id')
            ->get();
        
        // Get all classes for dynamic columns
        $allClasses = SchoolClass::orderBy('name')->get();
        
        $filename = 'students_backup_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($students, $allClasses) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Build header row
            $headers = [
                'ID',
                'Name',
                'Roll Number',
                'Mobile Number',
                'Additional Phone 1',
                'Additional Phone 2',
                'Additional Phone 3',
                'Additional Phone 4',
                'Password (Plain Text)',
                'Registration Date',
                'Last Updated',
            ];
            
            // Add class columns dynamically
            foreach ($allClasses as $class) {
                $headers[] = 'Class: ' . $class->name;
            }
            
            fputcsv($file, $headers);
            
            // Write student data
            foreach ($students as $student) {
                // Get all phone numbers
                $phoneNumbers = collect([$student->mobile_number])
                    ->merge($student->phoneNumbers->pluck('phone_number'))
                    ->filter()
                    ->values();
                
                // Build row data
                $row = [
                    $student->id,
                    $student->name ?? '',
                    $student->roll_number ?? '',
                    $phoneNumbers->get(0) ?? '', // Primary mobile
                    $phoneNumbers->get(1) ?? '', // Additional phone 1
                    $phoneNumbers->get(2) ?? '', // Additional phone 2
                    $phoneNumbers->get(3) ?? '', // Additional phone 3
                    $phoneNumbers->get(4) ?? '', // Additional phone 4
                    $student->password_plain ?? '', // Plain text password for backup
                    $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : '',
                    $student->updated_at ? $student->updated_at->format('Y-m-d H:i:s') : '',
                ];
                
                // Add class enrollment status (YES/NO for each class)
                $studentClassIds = $student->schoolClasses->pluck('id')->toArray();
                foreach ($allClasses as $class) {
                    $row[] = in_array($class->id, $studentClassIds) ? 'YES' : 'NO';
                }
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}

