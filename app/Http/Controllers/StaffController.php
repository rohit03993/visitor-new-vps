<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Models\VmsUser;
use App\Models\Visitor;
use App\Models\InteractionHistory;
use App\Models\Address;
use App\Models\Remark;
use App\Models\Branch;
use App\Models\Course;
use App\Models\StudentSession;

class StaffController extends Controller
{
    /**
     * Mask mobile number for staff privacy
     * Shows first 4 digits and last 2 digits, masks the middle
     * Example: +918320936486 becomes +9183XXXXXX86
     */
    private function maskMobileNumber($mobileNumber)
    {
        if (empty($mobileNumber)) {
            return $mobileNumber;
        }
        
        // Remove any spaces or special characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $mobileNumber);
        
        // If number is too short, return as is
        if (strlen($cleaned) < 8) {
            return $mobileNumber;
        }
        
        // Extract country code (+91) and mask the middle
        if (strpos($cleaned, '+91') === 0) {
            $countryCode = '+91';
            $number = substr($cleaned, 3); // Remove +91
        } else {
            $countryCode = '';
            $number = $cleaned;
        }
        
        // Show first 2 digits and last 2 digits, mask the rest
        if (strlen($number) >= 6) {
            $firstTwo = substr($number, 0, 2);
            $lastTwo = substr($number, -2);
            $masked = $firstTwo . str_repeat('X', strlen($number) - 4) . $lastTwo;
        } else {
            $masked = $number;
        }
        
        return $countryCode . $masked;
    }

    public function dashboard()
    {
        $user = auth()->user();
        
        // Get user's permitted branch IDs for viewing all data
        $permittedBranchIds = $user->getAllowedBranchIds('can_view_remarks');
        
        // Get assigned visitors (visitors assigned to this staff member)
        $assignedInteractions = InteractionHistory::where('meeting_with', $user->user_id)
            ->with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'assigned_page');
        
        // Get all interactions (ALL visitors - no branch filtering for viewing)
        $allInteractions = InteractionHistory::with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'all_page');
        
        // Mask mobile numbers for staff privacy (display only)
        foreach ($assignedInteractions as $interaction) {
            if ($interaction->visitor) {
                // Store original mobile number for "Add Revisit" functionality
                $interaction->visitor->original_mobile_number = $interaction->visitor->mobile_number;
                // Mask for display
                $interaction->visitor->mobile_number = $this->maskMobileNumber($interaction->visitor->mobile_number);
            }
        }
        
        foreach ($allInteractions as $interaction) {
            if ($interaction->visitor) {
                // Store original mobile number for "Add Revisit" functionality
                $interaction->visitor->original_mobile_number = $interaction->visitor->mobile_number;
                // Mask for display
                $interaction->visitor->mobile_number = $this->maskMobileNumber($interaction->visitor->mobile_number);
            }
        }
        
        return view('staff.dashboard', compact('assignedInteractions', 'allInteractions'));
    }

    public function showVisitorSearch(Request $request)
    {
        $prefilledMobile = $request->get('mobile', '');
        
        // If mobile number is provided, auto-search
        if (!empty($prefilledMobile)) {
            // Clean the mobile number (remove +91 if present)
            $cleanMobile = preg_replace('/^\+91/', '', $prefilledMobile);
            $formattedMobile = '+91' . $cleanMobile;
            
            // Search for ALL students with this phone number
            $students = $this->findStudentsByPhoneNumber($cleanMobile, $formattedMobile);
            
            if (count($students) === 1) {
                // Single student found - redirect directly to profile with searched number
                return redirect()->route('staff.visitor-profile', $students[0]['visitor_id'])
                    ->with('searched_mobile', $cleanMobile);
            } elseif (count($students) > 1) {
                // Multiple students found - show selection screen
                return view('staff.student-selection', [
                    'students' => $students,
                    'phoneNumber' => $cleanMobile,
                    'formattedPhone' => $formattedMobile
                ]);
            }
            // If no students found, continue to show search form
        }
        
        // Get dropdown data for advanced search
        $tags = \App\Models\Tag::active()->orderBy('name')->get();
        $courses = Course::active()->orderByRaw("CASE WHEN course_name = 'None' THEN 0 ELSE 1 END, course_name")->get();
        
        return view('staff.visitor-search', compact('prefilledMobile', 'tags', 'courses'));
    }

    public function showAssignedToMe()
    {
        $user = auth()->user();
        
        // Get assigned visitors (visitors assigned to this staff member) - show only pending interactions
        $assignedInteractions = InteractionHistory::where('meeting_with', $user->user_id)
            ->where('is_completed', false) // Only show interactions that are not completed
            ->where(function($query) {
                // Show immediate assignments OR scheduled assignments where date has arrived
                $query->where('is_scheduled', false)
                      ->orWhere(function($subQuery) {
                          $subQuery->where('is_scheduled', true)
                                   ->where('scheduled_date', '<=', now());
                      })
                      ->orWhereNull('is_scheduled'); // Also show legacy records without scheduling
            })
            ->with(['visitor', 'meetingWith.branch', 'address', 'remarks', 'assignedBy'])
            ->orderBy('created_at', 'desc') // Latest first
            ->get()
            ->filter(function($interaction) {
                // If interaction has no remarks, show it (truly pending)
                if ($interaction->remarks->count() === 0) {
                    return true;
                }
                
                // If interaction has only 1 remark and it's a transfer remark, show it (transferred interaction)
                if ($interaction->remarks->count() === 1) {
                    $remark = $interaction->remarks->first();
                    return strpos($remark->remark_text, 'Transferred from') !== false ||
                           strpos($remark->remark_text, '📅 Scheduled Assignment from') !== false;
                }
                
                // If interaction has multiple remarks, don't show it (already worked on)
                return false;
            })
            ->values(); // Reset array keys
        
        // Convert back to paginated collection
        $assignedInteractions = new \Illuminate\Pagination\LengthAwarePaginator(
            $assignedInteractions,
            $assignedInteractions->count(),
            20,
            1,
            ['path' => request()->url()]
        );
        
        
        // Mask mobile numbers for staff privacy (display only)
        foreach ($assignedInteractions as $interaction) {
            if ($interaction->visitor) {
                // Store original mobile number for "Add Revisit" functionality
                $interaction->visitor->original_mobile_number = $interaction->visitor->mobile_number;
                // Mask for display
                $interaction->visitor->mobile_number = $this->maskMobileNumber($interaction->visitor->mobile_number);
            }
        }
        
        return view('staff.assigned-to-me', compact('assignedInteractions'));
    }

    /**
     * Check for changes in assigned interactions (lightweight API)
     */
    public function checkAssignedChanges(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get current assigned interactions count and latest update time
            $assignedInteractions = InteractionHistory::where('meeting_with', $user->user_id)
                ->where('is_completed', false)
                ->with(['visitor', 'remarks'])
                ->orderBy('created_at', 'desc') // Latest first
                ->get()
                ->filter(function($interaction) {
                    // Same filtering logic as showAssignedToMe
                    if ($interaction->remarks->count() === 0) {
                        return true;
                    }
                    
                    if ($interaction->remarks->count() === 1) {
                        $remark = $interaction->remarks->first();
                        return strpos($remark->remark_text, '🔄 **Transferred from') !== false ||
                               strpos($remark->remark_text, 'Transferred from') !== false;
                    }
                    
                    return false;
                });

            $currentCount = $assignedInteractions->count();
            $lastUpdate = $assignedInteractions->max('updated_at') ?: $assignedInteractions->max('created_at');
            
            // Get client's last known state
            $clientCount = $request->input('last_count', 0);
            $clientLastUpdate = $request->input('last_update', '');
            
            // Check if there are changes
            $hasChanges = ($currentCount != $clientCount) || 
                         ($lastUpdate && $lastUpdate->toISOString() !== $clientLastUpdate);
            
            return response()->json([
                'success' => true,
                'has_changes' => $hasChanges,
                'current_count' => $currentCount,
                'last_update' => $lastUpdate ? $lastUpdate->toISOString() : null,
                'message' => $hasChanges ? 'Changes detected' : 'No changes'
            ]);

        } catch (\Exception $e) {
            \Log::error('Check assigned changes error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to check changes'], 500);
        }
    }

    /**
     * Check if interaction has actual work remarks (not just transfer remarks)
     */
    private function hasWorkRemarks($interaction)
    {
        foreach($interaction->remarks as $remark) {
            // If remark doesn't contain transfer indicators, it's a work remark
            if (strpos($remark->remark_text, 'Transferred from') === false) {
                return true; // Found a work remark
            }
        }
        return false; // Only transfer remarks or no remarks
    }

    public function searchVisitor(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $mobileNumber = $request->input('mobile_number');
        $formattedMobile = '+91' . $mobileNumber;
        
        // Get user's permitted branch IDs
        $user = auth()->user();
        $permittedBranchIds = $user->getAllowedBranchIds('can_view_remarks');
        
        // Search for ALL students with this phone number
        $students = $this->findStudentsByPhoneNumber($mobileNumber, $formattedMobile);
        
        if (count($students) === 0) {
            // No students found - redirect to visitor form for new student
            return redirect()->route('staff.visitor-form', [
                'mobile' => $mobileNumber
            ]);
        } elseif (count($students) === 1) {
            // Single student found - redirect directly to profile with searched number
            return redirect()->route('staff.visitor-profile', $students[0]['visitor_id'])
->with('searched_mobile', $mobileNumber);
        } else {
            // Multiple students found - show selection screen
            return view('staff.student-selection', [
                'students' => $students,
                'phoneNumber' => $mobileNumber,
                'formattedPhone' => $formattedMobile
            ]);
        }
    }
    
    /**
     * Find all students associated with a phone number
     */
    private function findStudentsByPhoneNumber($mobileNumber, $formattedMobile)
    {
        $students = collect();
        
        // Find students with this as PRIMARY number
        $primaryStudents = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->with(['interactions', 'course'])
            ->get()
            ->map(function($student) {
                $student->phone_type = 'primary';
                return $student;
            });
        
        // Find students with this as ADDITIONAL number
        $additionalStudents = collect();
        $phoneRecords = \App\Models\VisitorPhoneNumber::where('phone_number', $formattedMobile)
            ->orWhere('phone_number', $mobileNumber)
            ->with(['visitor.interactions', 'visitor.course'])
            ->get();
        
        foreach ($phoneRecords as $phoneRecord) {
            if ($phoneRecord->visitor) {
                $student = $phoneRecord->visitor;
                $student->phone_type = 'additional';
                $additionalStudents->push($student);
            }
        }
        
        // Combine results: Primary students first, then additional
        $students = $primaryStudents->concat($additionalStudents);
        
        // Add interaction count for each student
        foreach ($students as $student) {
            $student->interaction_count = $student->interactions->count();
            $student->latest_interaction = $student->interactions->sortByDesc('created_at')->first();
        }
        
        // Convert to array format that the view expects
        return $students->map(function($student) {
            return [
                'visitor_id' => $student->visitor_id,
                'name' => $student->name,
                'student_name' => $student->student_name,
                'father_name' => $student->father_name,
                'mobile_number' => $student->mobile_number,
                'course_id' => $student->course_id,
                'phone_type' => $student->phone_type,
                'interaction_count' => $student->interaction_count,
                'latest_interaction' => $student->latest_interaction ? [
                    'created_at' => $student->latest_interaction->created_at,
                    'mode' => $student->latest_interaction->mode,
                ] : null,
                'course' => $student->course ? [
                    'course_name' => $student->course->course_name,
                ] : null,
                'interactions' => $student->interactions->toArray(),
            ];
        })->toArray();
    }

    public function advancedSearch(Request $request)
    {
        $request->validate([
            'student_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'purpose' => 'nullable|exists:tags,id',
            'course_id' => 'nullable|exists:courses,course_id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        // Get search parameters
        $studentName = $request->input('student_name');
        $fatherName = $request->input('father_name');
        $contactPerson = $request->input('contact_person');
        $purpose = $request->input('purpose');
        $courseId = $request->input('course_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Build the search query with OR logic
        $query = Visitor::with(['interactions' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'course', 'interactions.meetingWith.branch']);

        // Apply OR search filters - if ANY criteria matches, show the result
        $query->where(function($q) use ($studentName, $fatherName, $contactPerson, $courseId, $purpose, $dateFrom, $dateTo) {
            $hasConditions = false;

            // Student name search
            if (!empty($studentName)) {
                $q->where('student_name', 'LIKE', "%{$studentName}%");
                $hasConditions = true;
            }

            // Father name search
            if (!empty($fatherName)) {
                if ($hasConditions) {
                    $q->orWhere('father_name', 'LIKE', "%{$fatherName}%");
                } else {
                    $q->where('father_name', 'LIKE', "%{$fatherName}%");
                    $hasConditions = true;
                }
            }

            // Contact person search
            if (!empty($contactPerson)) {
                if ($hasConditions) {
                    $q->orWhere('name', 'LIKE', "%{$contactPerson}%");
                } else {
                    $q->where('name', 'LIKE', "%{$contactPerson}%");
                    $hasConditions = true;
                }
            }

            // Course search
            if (!empty($courseId)) {
                if ($hasConditions) {
                    $q->orWhere('course_id', $courseId);
                } else {
                    $q->where('course_id', $courseId);
                    $hasConditions = true;
                }
            }

            // Purpose search in interactions
            if (!empty($purpose)) {
                if ($hasConditions) {
                    $q->orWhereHas('interactions', function($subQ) use ($purpose) {
                        $subQ->where('purpose', $purpose);
                    });
                } else {
                    $q->whereHas('interactions', function($subQ) use ($purpose) {
                        $subQ->where('purpose', $purpose);
                    });
                    $hasConditions = true;
                }
            }

            // Date range search in interactions
            if (!empty($dateFrom) || !empty($dateTo)) {
                if ($hasConditions) {
                    $q->orWhereHas('interactions', function($subQ) use ($dateFrom, $dateTo) {
                        if (!empty($dateFrom)) {
                            $subQ->whereDate('created_at', '>=', $dateFrom);
                        }
                        if (!empty($dateTo)) {
                            if (!empty($dateFrom)) {
                                $subQ->whereDate('created_at', '<=', $dateTo);
                            } else {
                                $subQ->whereDate('created_at', '<=', $dateTo);
                            }
                        }
                    });
                } else {
                    $q->whereHas('interactions', function($subQ) use ($dateFrom, $dateTo) {
                        if (!empty($dateFrom)) {
                            $subQ->whereDate('created_at', '>=', $dateFrom);
                        }
                        if (!empty($dateTo)) {
                            if (!empty($dateFrom)) {
                                $subQ->whereDate('created_at', '<=', $dateTo);
                            } else {
                                $subQ->whereDate('created_at', '<=', $dateTo);
                            }
                        }
                    });
                    $hasConditions = true;
                }
            }
        });

        // Get results with pagination (10 per page)
        $visitors = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Mask mobile numbers for staff privacy
        foreach ($visitors as $visitor) {
            $visitor->original_mobile_number = $visitor->mobile_number;
            $visitor->mobile_number = $this->maskMobileNumber($visitor->mobile_number);
        }

        // Get dropdown data for the search form
        $tags = \App\Models\Tag::active()->orderBy('name')->get();
        $courses = Course::active()->orderByRaw("CASE WHEN course_name = 'None' THEN 0 ELSE 1 END, course_name")->get();

        return view('staff.advanced-search-results', compact(
            'visitors', 
            'tags', 
            'courses',
            'studentName',
            'fatherName', 
            'contactPerson',
            'purpose',
            'courseId',
            'dateFrom',
            'dateTo'
        ));
    }

    public function showVisitorForm(Request $request)
    {
        // Get all active staff for assignment (including current user)
        $employees = Cache::remember('active_staff_list', 3600, function() {
            return VmsUser::where('role', 'staff')->where('is_active', true)->get();
        });
        
        // Get all addresses
        $addresses = Cache::remember('addresses_list', 3600, function() {
            return Address::all();
        });

        // Get all active tags
        $tags = Cache::remember('active_tags_list', 3600, function() {
            return \App\Models\Tag::active()->orderBy('name')->get();
        });

        // Get all active courses with "None" first
        $courses = Cache::remember('active_courses_list', 3600, function() {
            return Course::active()->orderByRaw("CASE WHEN course_name = 'None' THEN 0 ELSE 1 END, course_name")->get();
        });

        // Get prefilled data
        $prefilledMobile = $request->get('mobile', '');
        $prefilledName = $request->get('name', '');
        
        // Store original mobile number for form submission
        $originalMobileNumber = $prefilledMobile;
        
        // Clean mobile number - remove +91 prefix if present
        if (!empty($prefilledMobile)) {
            // Remove +91 prefix if it exists
            $prefilledMobile = preg_replace('/^\+91/', '', $prefilledMobile);
        }
        
        // Check if this is "Add Interaction" with visitor_id (most reliable)
        $isExistingContact = false;
        $contactPersonDetails = null;
        $lastInteractionDetails = null;
        $existingStudentsCount = 0;
        
        // PRIORITY 1: If visitor_id is provided (from "Add Interaction" button)
        if ($request->has('visitor_id') && $request->visitor_id) {
            $visitor = Visitor::with(['interactions', 'tags'])->find($request->visitor_id);
            
            if ($visitor) {
                $isExistingContact = true;
                
                // Get last interaction details for auto-fill
                $lastInteraction = $visitor->interactions()->with(['meetingWith', 'address'])->orderBy('created_at', 'desc')->first();
                
                    $lastInteractionDetails = [
                    'contact_name' => $visitor->name,
                    'student_name' => $visitor->student_name,
                        'father_name' => $visitor->father_name,
                    'course_id' => $visitor->course_id,
                    'mode' => $lastInteraction->mode ?? 'In-Campus',
                    'meeting_with' => $lastInteraction->meeting_with ?? null,
                    'address_id' => $lastInteraction->address_id ?? null,
                        'address_name' => $lastInteraction->address->address_name ?? '',
                        'tags' => $visitor->tags->pluck('id')->toArray(),
                    ];
                }
        } elseif (!empty($prefilledMobile)) {
            $formattedMobile = '+91' . $prefilledMobile;
            
            // Search for ALL students with this phone number
            $students = $this->findStudentsByPhoneNumber($prefilledMobile, $formattedMobile);
            $existingStudentsCount = count($students);
            
            if ($existingStudentsCount > 0) {
                $isExistingContact = true;
                
                // Check if this is for a specific existing visitor (Add Interaction) 
                $targetStudentName = $prefilledName ?? '';
                $targetStudent = null;
                
                if (!empty($targetStudentName)) {
                    // Find the specific student this interaction is for
                    $targetStudent = collect($students)->first(function($student) use ($targetStudentName) {
                        return $student['name'] === $targetStudentName;
                    });
                }
                
                if ($targetStudent) {
                    // This is "Add Interaction" for existing visitor - pre-fill ALL details
                    $lastInteraction = collect($targetStudent['interactions'])->sortByDesc('created_at')->first();
                    
                    $lastInteractionDetails = [
                        'contact_name' => $targetStudent['name'],
                        'student_name' => $targetStudent['student_name'],
                        'father_name' => $targetStudent['father_name'],
                        'course_id' => $targetStudent['course_id'],
                        'mode' => $lastInteraction['mode'] ?? 'In-Campus',
                        'meeting_with' => $lastInteraction['meeting_with'] ?? null,
                        'address_id' => $lastInteraction['address_id'] ?? null,
                        'address_name' => $lastInteraction['address']['address_name'] ?? '',
                        'tags' => [], // Will be loaded from visitor tags
                    ];
                } else {
                    // This is "Add Another Student" - only pre-fill contact details
                    $firstStudent = collect($students)->first();
                    $lastInteraction = collect($firstStudent['interactions'])->sortByDesc('created_at')->first();
                    
                    $contactPersonDetails = [
                        'contact_name' => $firstStudent['name'], // Contact person name
                        'student_name' => '', // Will be filled by user for new student
                        'father_name' => $firstStudent['father_name'],
                        'course_id' => null, // New student, new course
                        'mode' => $lastInteraction['mode'] ?? 'In-Campus',
                        'meeting_with' => $lastInteraction['meeting_with'] ?? null,
                        'address_id' => $lastInteraction['address_id'] ?? null,
                        'address_name' => $lastInteraction['address']['address_name'] ?? '',
                        'tags' => [], // New student, new purpose
                    ];
                    
                    // For backward compatibility
                    $lastInteractionDetails = $contactPersonDetails;
                }
            }
        }
        
        return view('staff.visitor-form', compact('employees', 'addresses', 'tags', 'courses', 'prefilledMobile', 'prefilledName', 'isExistingContact', 'contactPersonDetails', 'existingStudentsCount', 'originalMobileNumber', 'lastInteractionDetails'));
    }

    public function storeVisitor(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,course_id',
            'student_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'purpose' => 'required|exists:tags,id',
            'address_id' => 'nullable|exists:addresses,address_id',
            'address_input' => 'nullable|string|max:255',
            'meeting_with' => 'required|exists:vms_users,user_id',
            'mode' => 'required|in:In-Campus,Out-Campus,Telephonic',
            'initial_notes' => 'nullable|string|max:500',
        ]);

        // Custom validation: If course is not "None", student_name and father_name are required
        $selectedCourse = Course::find($request->course_id);
        if ($selectedCourse && $selectedCourse->course_code !== 'NONE') {
            if (empty($request->student_name)) {
                return back()->withErrors(['student_name' => 'Student name is required when selecting a course.'])->withInput();
            }
            if (empty($request->father_name)) {
            return back()->withErrors(['father_name' => 'Father\'s name is required when selecting a course.'])->withInput();
            }
        }

        // Custom validation: Either address_id or address_input must be provided
        if (!$request->address_id && !$request->address_input) {
            return back()->withErrors(['address_id' => 'Please select an address or enter a new one.'])->withInput();
        }

        $user = auth()->user();
        $mobileNumber = $request->mobile_number;
        $formattedMobile = '+91' . $mobileNumber;

        // Fallback: If address_id is not provided but address_input is, create the address FIRST
        if (!$request->address_id && $request->address_input) {
            $address = Address::findOrCreate(
                $request->address_input,
                $request->address_input,
                $user->user_id
            );
            $request->merge(['address_id' => $address->address_id]);
        }

        // Check if this is for an existing visitor (Add Interaction) or new student (Add Another Student)
        // Use the action parameter to determine the intent
        $isAddingInteraction = $request->get('action') === 'add_interaction';
        $existingVisitor = null;
        
        // If adding interaction, try to find existing visitor
        if ($isAddingInteraction) {
            // First, try to find by visitor_id if provided (most reliable)
            if ($request->has('visitor_id') && $request->visitor_id) {
                $existingVisitor = Visitor::find($request->visitor_id);
            }
            
            // If not found by ID, search by name match (case-insensitive and trimmed)
            if (!$existingVisitor) {
                $existingVisitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
                    ->whereRaw('LOWER(TRIM(name)) = LOWER(TRIM(?))', [trim($request->name)])
                    ->whereRaw('LOWER(TRIM(student_name)) = LOWER(TRIM(?))', [trim($request->student_name)])
            ->first();
            }
            
            // If not found in primary search, try additional phone numbers
            if (!$existingVisitor) {
                $phoneRecord = \App\Models\VisitorPhoneNumber::where('phone_number', $formattedMobile)
                    ->orWhere('phone_number', $mobileNumber)
                    ->with('visitor')
                    ->first();
                
                if ($phoneRecord && $phoneRecord->visitor && 
                    $phoneRecord->visitor->name === $request->name &&
                    $phoneRecord->visitor->student_name === $request->student_name) {
                    $existingVisitor = $phoneRecord->visitor;
                }
            }
        }
        
        if ($existingVisitor) {
            // Update existing visitor information if different
            $updateData = ['last_updated_by' => $user->user_id];
            
            if ($existingVisitor->name !== $request->name) {
                $updateData['name'] = $request->name;
            }
            if ($existingVisitor->course_id != $request->course_id) {
                $updateData['course_id'] = $request->course_id;
            }
            if ($existingVisitor->student_name !== $request->student_name) {
                $updateData['student_name'] = $request->student_name;
            }
            if ($existingVisitor->father_name !== $request->father_name) {
                $updateData['father_name'] = $request->father_name;
            }
            
            if (count($updateData) > 1) { // More than just last_updated_by
                $existingVisitor->update($updateData);
            }
            
            $visitor = $existingVisitor;
        } else {
            // Create new visitor (allows multiple students with same phone number)
            $visitor = Visitor::create([
                'mobile_number' => $formattedMobile,
                'name' => $request->name,
                'course_id' => $request->course_id,
                'student_name' => $request->student_name,
                'father_name' => $request->father_name,
                'last_updated_by' => $user->user_id,
            ]);
        }

        // Assign single purpose tag to visitor
        if ($request->has('purpose')) {
            $visitor->tags()->sync([$request->purpose]);
            
            // Get purpose name from selected tag
            $selectedTag = \App\Models\Tag::find($request->purpose);
            $purpose = $selectedTag ? $selectedTag->name : 'General Visit';
    } else {
        $purpose = 'General Visit';
    }

    // Check if this purpose should create a student session
    $shouldCreateSession = $this->shouldCreateStudentSession($purpose, $selectedCourse);
    $sessionId = null;

    if ($shouldCreateSession) {
        // Check if visitor already has an active session for this purpose
        $existingSession = $visitor->activeSessions()
            ->where('purpose', $purpose)
            ->first();

        if (!$existingSession) {
            // Create new student session
            $session = StudentSession::create([
                'visitor_id' => $visitor->visitor_id,
                'purpose' => $purpose,
                'status' => 'active',
                'started_at' => now(),
                'started_by' => $user->user_id,
            ]);
            $sessionId = $session->session_id;
        } else {
            // Use existing active session
            $sessionId = $existingSession->session_id;
        }
    }

        // Create interaction as PENDING (no remark creation here)
        $interaction = InteractionHistory::create([
            'visitor_id' => $visitor->visitor_id,
            'session_id' => $sessionId,
            'name_entered' => $request->name,
            'mobile_number' => $formattedMobile,
            'purpose' => $purpose,
            'initial_notes' => $request->initial_notes, // Optional initial notes
            'meeting_with' => $request->meeting_with,
            'address_id' => $request->address_id,
            'mode' => $request->mode,
            'created_by' => $user->user_id,
            'created_by_role' => 'staff',
            'interaction_type' => 'new', // NEW interaction created via visitor form
            'is_completed' => false, // PENDING - will be completed after meeting
        ]);

        // Handle visitor file uploads if any
        if ($request->has('visitor_files') && is_array($request->visitor_files)) {
            $this->handleVisitorFileUploads($request->visitor_files, $interaction->interaction_id, $user->user_id);
        }

        // Send notification to assigned staff member
        $assignedUser = VmsUser::find($request->meeting_with);
        if ($assignedUser) {
            // HYBRID APPROACH: Send both file-based and push notifications
            
            // 1. Send file-based notification (working)
            $fileSuccess = false;
            try {
                $notificationController = new \App\Http\Controllers\NotificationController();
                $fileSuccess = $notificationController->sendVisitAssignmentNotification(
                    $interaction->interaction_id,
                    $assignedUser->user_id,
                    $request->name,
                    $purpose
                );
                \Log::info("File notification sent for interaction {$interaction->interaction_id} to user {$assignedUser->user_id}: " . ($fileSuccess ? 'SUCCESS' : 'FAILED'));
            } catch (\Exception $e) {
                \Log::error("File notification failed for interaction {$interaction->interaction_id}: " . $e->getMessage());
            }
            
            // 2. Send push notification (SAFE APPROACH - no user switching)
            \Log::info("🔔 SENDING PUSH NOTIFICATION FOR NEW VISITOR ASSIGNMENT - Interaction {$interaction->interaction_id} to user {$assignedUser->user_id}");
            
            try {
                $pushController = new \App\Http\Controllers\PushNotificationController();
                
                $result = $pushController->sendPushNotificationToUser(
                    $assignedUser->user_id,
                    'New Visit Assigned to You!',
                    "You have been assigned a new visit: {$request->name} - {$purpose}",
                    [
                        'interaction_id' => $interaction->interaction_id,
                        'visitor_name' => $request->name,
                        'purpose' => $purpose,
                        'assigned_by' => auth()->user()->name,
                        'url' => '/staff/assigned-to-me'
                    ]
                );
                
                \Log::info("Push notification result for new assignment {$interaction->interaction_id}: " . json_encode($result));
                
            } catch (\Exception $e) {
                \Log::error("Push notification error for new assignment {$interaction->interaction_id}: " . $e->getMessage());
                \Log::error("Push notification stack trace: " . $e->getTraceAsString());
            }
        }

        // Clear cache
        Cache::forget('active_staff_list');

        $successMessage = 'Visitor entry created successfully!';
        
        // Add notification info to success message
        if ($assignedUser) {
            if ($assignedUser->user_id == $user->user_id) {
                $successMessage .= ' 🔔 You should receive a notification within 15 seconds.';
            } else {
                $successMessage .= " 🔔 Notification sent to {$assignedUser->name}.";
            }
        }

        return redirect()->route('staff.visitor-search')
            ->with('success', $successMessage);
    }

    public function updateRemark(Request $request, $interactionId)
    {
        
        $request->validate([
            'remark_text' => 'required|string|max:1000',
            'meeting_duration' => 'required|integer|min:5|max:180',
        ]);

        $user = auth()->user();
        $interaction = InteractionHistory::findOrFail($interactionId);

        // Check if user can update this interaction
        if ($interaction->meeting_with != $user->user_id) {
            return back()->withErrors(['error' => 'You can only update remarks for visitors assigned to you.']);
        }

        // Create new remark (simple remark, no outcome)
        $remarkData = [
            'interaction_id' => $interactionId,
            'remark_text' => $request->remark_text,
            'meeting_duration' => $request->meeting_duration,
            'outcome' => 'in_process', // Always in_process for simple remarks
            'added_by' => $user->user_id,
            'added_by_name' => $user->name,
        ];
        
        $remark = Remark::create($remarkData);

        // Update interaction (don't mark as completed, just update tracking)
        $interaction->update([
            'last_updated_by' => $user->user_id,
        ]);

        // Update visitor's last_updated_by
        $visitor = $interaction->visitor;
        if ($visitor) {
            $visitor->update(['last_updated_by' => $user->user_id]);
        }

        // Check if this is an AJAX request (from search results)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Remark added successfully!'
            ]);
        }
        
        // Regular form submission (from assigned-to-me page)
        return redirect()->route('staff.assigned-to-me')->with('success', 'Remark added successfully!');
    }

    public function markAsCompleted($interactionId)
    {
        $user = auth()->user();
        $interaction = InteractionHistory::findOrFail($interactionId);

        // Check if user can complete this interaction (must be assigned to them)
        if ($interaction->meeting_with != $user->user_id) {
            return back()->withErrors(['error' => 'You can only complete interactions assigned to you.']);
        }

        try {
            // Mark as completed
            $interaction->markAsCompleted($user->user_id);
            return redirect()->route('staff.assigned-to-me')->with('success', 'Interaction marked as completed successfully!');
        } catch (Exception $e) {
            // If column doesn't exist yet, just redirect with a message
            return redirect()->route('staff.assigned-to-me')->with('info', 'Completion feature will be available after database update.');
        }
    }

    public function getInteractionRemarks($interactionId)
    {
        $user = auth()->user();
        $interaction = InteractionHistory::with(['visitor', 'meetingWith', 'remarks'])->findOrFail($interactionId);

        // Check permissions
        $canViewRemarks = $user->canViewRemarksForInteraction($interaction);
        
        if (!$canViewRemarks) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view remarks for this interaction.'
            ], 403);
        }

        // Filter remarks based on user permissions
        $filteredRemarks = $interaction->remarks->filter(function($remark) use ($user) {
            return $user->canViewRemark($remark);
        });

        return response()->json([
            'success' => true,
            'remarks' => $filteredRemarks->map(function($remark) {
                return [
                    'remark_text' => $remark->remark_text,
                    'added_by_name' => $remark->added_by_name,
                    'created_at' => $remark->created_at->format('M d, Y H:i A'),
                ];
            }),
            'interaction' => [
                'visitor_name' => $interaction->name_entered,
                'purpose' => $interaction->purpose,
                'meeting_with' => $interaction->meetingWith->name ?? 'Unknown',
                'date' => $interaction->created_at->format('M d, Y H:i A'),
            ]
        ]);
    }

    public function checkMobile(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $mobileNumber = $request->input('mobile_number');
        $formattedMobile = '+91' . $mobileNumber;
        
        // Search for ALL students with this phone number
        $students = $this->findStudentsByPhoneNumber($mobileNumber, $formattedMobile);
        
        if (count($students) > 0) {
            return response()->json([
                'exists' => true,
                'count' => count($students),
                'students' => array_map(function($student) {
                    return [
                        'name' => $student['name'],
                        'student_name' => $student['student_name'],
                        'mobile_number' => $student['mobile_number'],
                        'phone_type' => $student['phone_type']
                    ];
                }, $students)
            ]);
        }
        
        return response()->json(['exists' => false]);
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255|unique:addresses,address_name',
        ]);

        $address = Address::create([
            'address_name' => $request->address_name,
        ]);

        return response()->json([
            'success' => true,
            'address_id' => $address->address_id,
            'address_name' => $address->address_name,
        ]);
    }

    /**
     * Determine if a student session should be created based on purpose and course
     */
    private function shouldCreateStudentSession($purpose, $course)
    {
        // Session-based purposes (student tracking)
        $sessionPurposes = [
            'Admission Inquiry',
            'Fee Discussion', 
            'Admission Final',
            'Course Selection',
            'Fee Issue',
            'Academic Complaint',
            'Infrastructure Complaint',
            'Teacher Complaint',
            'Result Issue',
            'Admission Follow-up',
            'Fee Follow-up',
            'Complaint Follow-up'
        ];

        // Check if purpose matches session-based purposes
        foreach ($sessionPurposes as $sessionPurpose) {
            if (stripos($purpose, $sessionPurpose) !== false) {
                return true;
            }
        }

        // Also create session if course is selected (not "None")
        if ($course && $course->course_code !== 'NONE') {
            return true;
        }

        return false;
    }

    /**
     * Complete a student session with outcome
     */
    public function completeSession(Request $request, $sessionId)
    {
        $request->validate([
            'outcome' => 'required|in:success,failed,pending',
            'outcome_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = StudentSession::findOrFail($sessionId);
            
            // Check if session is active
            if (!$session->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session is already completed or cancelled.'
                ], 400);
            }

            // Update session
            $session->update([
                'status' => 'completed',
                'outcome' => $request->outcome,
                'outcome_notes' => $request->outcome_notes,
                'completed_at' => now(),
                'completed_by' => auth()->user()->user_id,
            ]);
            
            // Mark all interactions in this session as completed
            $session->interactions()->update([
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => auth()->user()->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session completed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the session.'
            ], 500);
        }
    }

    /**
     * Show session completion modal
     */
    public function showCompleteSessionModal($sessionId)
    {
        try {
            $session = StudentSession::with(['visitor', 'starter', 'interactions.remarks'])->findOrFail($sessionId);
            
            if (!$session->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session is already completed or cancelled.'
                ], 400);
            }

            // Get the latest interaction and its latest remark
            $latestInteraction = $session->interactions()->with('remarks')->orderBy('created_at', 'desc')->first();
            $latestRemark = null;
            
            if ($latestInteraction && $latestInteraction->remarks->count() > 0) {
                // Get the latest remark from the latest interaction
                $latestRemark = $latestInteraction->remarks()
                    ->where('added_by', auth()->user()->user_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                // If no remark from current user, get the latest remark from any user
                if (!$latestRemark) {
                    $latestRemark = $latestInteraction->remarks()
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'session_id' => $session->session_id,
                    'purpose' => $session->purpose,
                    'visitor_name' => $session->visitor->name,
                    'student_name' => $session->visitor->student_name,
                    'started_at' => $session->started_at->format('M d, Y H:i A'),
                    'started_by' => $session->starter->name ?? 'Unknown',
                    'interaction_count' => $session->interactions->count(),
                    'latest_remark' => $latestRemark ? $latestRemark->remark_text : '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found.'
            ], 404);
        }
    }

    /**
     * Get interaction details for modal display
     */
    public function getInteractionDetails($interactionId)
    {
        try {
            $user = Auth::user();
            
            // Get interaction with all related data
            $interaction = InteractionHistory::with([
                'visitor',
                'meetingWith.branch',
                'address',
                'remarks.addedBy.branch',
                'course',
                'tags'
            ])->findOrFail($interactionId);
            
            // Check if user has permission to view this interaction
            if (!$user->canViewInteraction($interaction)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this interaction.'
                ], 403);
            }
            
            // Filter remarks based on user permissions
            $filteredRemarks = $interaction->remarks->filter(function($remark) use ($user) {
                return $user->canViewRemark($remark);
            });
            
            // Generate HTML content for the modal
            $html = view('staff.partials.interaction-details', [
                'interaction' => $interaction,
                'remarks' => $filteredRemarks,
                'user' => $user
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading interaction details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show visitor profile for staff
     */
    public function showVisitorProfile($visitorId, Request $request)
    {
        try {
            $user = Auth::user();
            \Log::info('=== METHOD CALLED: showVisitorProfile with ID: ' . $visitorId . ' ===');
            
            // Get visitor with basic relationships (including phone numbers)
            $visitor = Visitor::with(['course', 'tags', 'additionalPhoneNumbers'])->findOrFail($visitorId);
            \Log::info('Visitor found: ' . $visitor->name);
            
            // Get all interactions for this visitor
            $interactions = InteractionHistory::where('visitor_id', $visitorId)
                ->with(['meetingWith.branch', 'address', 'remarks' => function($query) {
                    $query->select('remark_id', 'interaction_id', 'remark_text', 'meeting_duration', 'outcome', 'added_by', 'added_by_name', 'created_at');
                }, 'remarks.addedBy.branch', 'studentSession.completer.branch', 'attachments.uploadedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            
            // Load file management data for each interaction
            foreach ($interactions as $interaction) {
                $fileManagement = \App\Models\FileManagement::where('interaction_id', $interaction->interaction_id)->get();
                $interaction->file_management = $fileManagement;
            }
            \Log::info('Interactions found: ' . $interactions->count());
            
            // Show ALL interactions for this visitor (staff can view all interactions)
            $filteredInteractions = $interactions;
            
            // Group interactions by purpose
            $groupedInteractions = $filteredInteractions->groupBy('purpose');
            
            // Get assigned interactions for this user
            $assignedInteractions = $filteredInteractions->filter(function($interaction) use ($user) {
                return $interaction->meeting_with == $user->user_id;
            });
            
            // Store original mobile number for "Add Revisit" functionality
            $originalMobileNumber = $visitor->mobile_number;
            
            // Get the searched mobile number (if provided)
            $searchedMobile = session('searched_mobile', '');
            
            // Clear the session after using it
            session()->forget('searched_mobile');
            
            // Mask mobile number for staff privacy (display only)
            $visitor->mobile_number = $this->maskMobileNumber($visitor->mobile_number);
            
            \Log::info('About to return view with visitor: ' . $visitor->name);
            
            return view('staff.visitor-profile', [
                'visitor' => $visitor,
                'interactions' => $filteredInteractions,
                'groupedInteractions' => $groupedInteractions,
                'assignedInteractions' => $assignedInteractions,
                'user' => $user,
                'originalMobileNumber' => $originalMobileNumber,
                'searchedMobile' => $searchedMobile
            ]);
            
        } catch (\Exception $e) {
            \Log::error('showVisitorProfile Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('staff.assigned-to-me')
                ->with('error', 'Visitor not found or you do not have permission to view this profile.');
        }
    }

    /**
     * Assign interaction to another team member
     */
    public function assignInteraction(Request $request, $interactionId)
    {
        try {
            $user = Auth::user();
            
            // Check if this is a reschedule (assigning to self) or transfer (assigning to someone else)
            $isRescheduling = ($request->team_member_id == $user->user_id);
            
            // Validate the request
            $request->validate([
                'team_member_id' => 'required|exists:vms_users,user_id',
                'assignment_notes' => 'nullable|string|max:500',
                // Meeting duration is optional for reschedule, required for transfer
                'meeting_duration' => $isRescheduling ? 'nullable|integer|min:5|max:180' : 'required|integer|min:5|max:180',
                'scheduled_date' => 'nullable|date|after_or_equal:today',
                'scheduled_hour' => 'nullable|string',
                'scheduled_minute' => 'nullable|string',
            ]);
            
            // Get the interaction
            $interaction = InteractionHistory::findOrFail($interactionId);
            
            // Check if the current user has permission to assign this interaction
            if ($interaction->meeting_with != $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign interactions that are assigned to you.'
                ], 403);
            }
            
            // Check if the target team member is active and is a staff member
            $targetMember = VmsUser::where('user_id', $request->team_member_id)
                ->where('role', 'staff')
                ->where('is_active', true)
                ->first();
                
            if (!$targetMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected team member is not available or not a staff member.'
                ], 400);
            }
            
            // Step 1: Add transfer remark to Y's original interaction
            $assignmentNotes = $request->assignment_notes ? trim($request->assignment_notes) : '';
            $targetBranch = $targetMember->branch ? $targetMember->branch->branch_name : 'Unknown Branch';
            $transferContext = "Completed & Transferred to {$targetMember->name} ({$targetBranch})";
            
            $remarkText = $transferContext;
            if (!empty($assignmentNotes)) {
                $remarkText .= "\nNotes: " . $assignmentNotes;
            }
            
            // Create remark on Y's original interaction
            $remark = \App\Models\Remark::create([
                'interaction_id' => $interactionId,
                'remark_text' => $remarkText,
                'meeting_duration' => $request->meeting_duration ?? null, // Optional for reschedule
                'added_by' => $user->user_id,
                'added_by_name' => $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Step 2: Mark Y's interaction as completed to remove it from "Assigned to Me"
            // This makes it disappear from Y's assigned list but keeps the interaction record
            $interaction->update([
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => $user->user_id,
                'updated_at' => now(),
            ]);
            
            // Check if this is a scheduled assignment
            $isScheduled = $request->has('schedule_assignment') && $request->schedule_assignment;
            $scheduledDate = null;
            
            if ($isScheduled && $request->scheduled_date) {
                $scheduledHour = $request->scheduled_hour ?? '09';
                $scheduledMinute = $request->scheduled_minute ?? '00';
                $scheduledDate = $request->scheduled_date . ' ' . $scheduledHour . ':' . $scheduledMinute . ':00';
                \Log::info('Scheduled datetime constructed: ' . $scheduledDate);
            }
            
            // Step 3: Create new interaction for X with transfer context
            $newInteraction = InteractionHistory::create([
                'visitor_id' => $interaction->visitor_id,
                'session_id' => $interaction->session_id, // Keep same session_id to stay in same purpose group
                'purpose' => $interaction->purpose,
                'meeting_with' => $request->team_member_id, // Assign to X
                'mode' => $interaction->mode,
                'address_id' => $interaction->address_id,
                'initial_notes' => $interaction->initial_notes,
                'name_entered' => $interaction->name_entered,
                'mobile_number' => $interaction->mobile_number, // Include mobile_number
                'created_by' => $user->user_id, // Y created this new interaction for X
                'created_by_role' => $user->role, // Include role
                'interaction_type' => 'assigned', // ASSIGNED interaction created via transfer
                'is_completed' => false, // New interaction is not completed
                'scheduled_date' => $scheduledDate, // NEW: Scheduled date
                'assigned_by' => $user->user_id, // NEW: Who assigned it
                'is_scheduled' => $isScheduled, // NEW: Is this scheduled
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Step 4: Add transfer context remark to X's new interaction
            $userBranch = $user->branch ? $user->branch->branch_name : 'Unknown Branch';
            
            // Update transfer context based on scheduling
            if ($isScheduled) {
                $transferContextForX = "📅 Scheduled Assignment from {$user->name} ({$userBranch}) - " . date('M d, Y', strtotime($scheduledDate));
            } else {
                $transferContextForX = "Transferred from {$user->name} ({$userBranch})";
            }
            
            $contextForX = $transferContextForX;
            if (!empty($assignmentNotes)) {
                $contextForX .= "\nNotes: " . $assignmentNotes;
            }
            
            \App\Models\Remark::create([
                'interaction_id' => $newInteraction->interaction_id,
                'remark_text' => $contextForX,
                'added_by' => $user->user_id,
                'added_by_name' => $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Send notification to assigned staff member
            $visitor = \App\Models\Visitor::find($interaction->visitor_id);
            if ($visitor) {
                // 1. Send file-based notification (existing)
                $notificationController = new \App\Http\Controllers\NotificationController();
                $notificationController->sendVisitAssignmentNotification(
                    $newInteraction->interaction_id,
                    $targetMember->user_id,
                    $visitor->name,
                    $interaction->purpose
                );
                
                // 2. Send push notification (NEW)
                \Log::info("🔔 SENDING PUSH NOTIFICATION FOR TRANSFER - Interaction {$newInteraction->interaction_id} to user {$targetMember->user_id}");
                
                try {
                    // SAFE APPROACH: Send push notification without user switching
                    $pushController = new \App\Http\Controllers\PushNotificationController();
                    
                    $result = $pushController->sendPushNotificationToUser(
                        $targetMember->user_id,
                        'New Visit Transferred to You!',
                        "You have been assigned a transferred visit: {$visitor->name} - {$interaction->purpose}",
                        [
                            'interaction_id' => $newInteraction->interaction_id,
                            'visitor_name' => $visitor->name,
                            'purpose' => $interaction->purpose,
                            'transferred_by' => auth()->user()->name,
                            'url' => '/staff/assigned-to-me'
                        ]
                    );
                    
                    \Log::info("Push notification result for transfer {$newInteraction->interaction_id}: " . json_encode($result));
                    
                } catch (\Exception $e) {
                    \Log::error("Push notification error for transfer {$newInteraction->interaction_id}: " . $e->getMessage());
                    \Log::error("Push notification stack trace: " . $e->getTraceAsString());
                }
            }

            // Log the assignment
            \Log::info("Interaction {$interactionId} transferred from user {$user->user_id} to user {$request->team_member_id}. New interaction ID: {$newInteraction->interaction_id}");
            \Log::info("New interaction details: " . json_encode($newInteraction->toArray()));
            
            return response()->json([
                'success' => true,
                'message' => "Interaction successfully transferred to {$targetMember->name}. New interaction created with transfer context.",
                'assigned_to' => $targetMember->name,
                'new_interaction_id' => $newInteraction->interaction_id,
                'original_interaction_id' => $interactionId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer interaction: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== PHONE NUMBER MANAGEMENT METHODS (NEW FEATURE) ==========
    
    /**
     * Add additional phone number to visitor
     */
    public function addPhoneNumber(Request $request, $visitorId)
    {
        try {
            \Log::info('=== ADD PHONE NUMBER REQUEST ===');
            \Log::info('Visitor ID: ' . $visitorId);
            \Log::info('Request data: ' . json_encode($request->all()));
            $request->validate([
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            ], [
                'phone_number.regex' => 'Phone number must be exactly 10 digits.',
            ]);

            $visitor = Visitor::findOrFail($visitorId);
            
            // Check if visitor can add more phone numbers (max 4 total)
            if (!$visitor->canAddMorePhoneNumbers()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 4 phone numbers allowed per visitor.'
                ], 400);
            }
            
            // Check if phone number already exists
            $phoneNumber = $request->phone_number;
            $formattedPhone = '+91' . $phoneNumber;
            
            // Check against primary mobile_number
            if ($visitor->mobile_number === $phoneNumber || $visitor->mobile_number === $formattedPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'This number is already the primary mobile number.'
                ], 400);
            }
            
            // Check against this visitor's existing additional phone numbers (prevent duplicates for same visitor)
            $existingPhone = \App\Models\VisitorPhoneNumber::where('phone_number', $formattedPhone)
                ->orWhere('phone_number', $phoneNumber)
                ->where('visitor_id', $visitor->visitor_id)
                ->first();
                
            if ($existingPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already added as additional number for this student.'
                ], 400);
            }
            
            // Note: We now ALLOW sharing phone numbers between different students
            // This enables family members to share contact numbers as requested
            
            // Add the phone number
            $newPhone = \App\Models\VisitorPhoneNumber::create([
                'visitor_id' => $visitor->visitor_id,
                'phone_number' => $formattedPhone,
                'is_primary' => false,
            ]);
            
            \Log::info('Phone number created successfully: ' . $newPhone->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Phone number added successfully.',
                'phone_numbers' => $visitor->fresh()->getAllPhoneNumbers()
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Add phone number error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add phone number. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Remove additional phone number from visitor
     */
    public function removePhoneNumber(Request $request, $visitorId, $phoneId)
    {
        try {
            $visitor = Visitor::findOrFail($visitorId);
            
            $phoneRecord = \App\Models\VisitorPhoneNumber::where('id', $phoneId)
                ->where('visitor_id', $visitor->visitor_id)
                ->first();
            
            if (!$phoneRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number not found.'
                ], 404);
            }
            
            // Cannot remove primary phone number (should not happen, but safety check)
            if ($phoneRecord->is_primary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove primary phone number.'
                ], 400);
            }
            
            $phoneRecord->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Phone number removed successfully.',
                'phone_numbers' => $visitor->fresh()->getAllPhoneNumbers()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Remove phone number error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove phone number. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Upload file attachment to Google Drive
     */
    public function uploadAttachment(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:20480', // 20MB max
                'interaction_id' => 'required|string', // Allow string for visitor uploads
            ]);
            
            $user = auth()->user();
            $interactionId = $request->interaction_id;
            $file = $request->file('file');
            
            // Check if this is a visitor upload (temporary ID)
            $isVisitorUpload = strpos($interactionId, 'visitor_temp_') === 0;
            
            if (!$isVisitorUpload) {
                // Regular interaction upload - validate interaction exists
            $interaction = InteractionHistory::find($interactionId);
            if (!$interaction || $interaction->meeting_with != $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add attachments to this interaction.'
                ], 403);
                }
            }
            
            // Check file limit per interaction (max 5 files) - skip for visitor uploads
            if (!$isVisitorUpload) {
            $existingFiles = \App\Models\InteractionAttachment::where('interaction_id', $interactionId)->count();
            if ($existingFiles >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 5 files allowed per interaction.'
                ], 400);
                }
            }
            
            // Create uploads directory if it doesn't exist
            $uploadPath = 'uploads/' . date('Y/m');
            $fullPath = storage_path('app/public/' . $uploadPath);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $serverPath = $uploadPath . '/' . $filename;
            
            // Get file info BEFORE moving the file
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            // Move file to server storage
            $file->move($fullPath, $filename);
            
            // Save to file management system
            $fileManagement = \App\Models\FileManagement::create([
                'original_filename' => $originalName,
                'server_path' => $serverPath,
                'file_type' => strtolower($extension),
                'file_size' => $fileSize,
                'status' => 'server',
                'uploaded_by' => $user->user_id,
                'interaction_id' => $isVisitorUpload ? null : $interactionId,
                'mime_type' => $mimeType,
            ]);
            
            // Prepare response
            if ($isVisitorUpload) {
                // For visitor uploads, return file info for temporary storage
                $attachment = [
                    'id' => 'temp_' . $fileManagement->id,
                    'filename' => $originalName,
                    'size' => $fileSize,
                    'type' => strtolower($extension),
                    'url' => asset('storage/' . $serverPath),
                    'file_management_id' => $fileManagement->id,
                ];
            } else {
                // Regular interaction upload - also create InteractionAttachment for compatibility
            $attachment = \App\Models\InteractionAttachment::create([
                'interaction_id' => $interactionId,
                    'original_filename' => $originalName,
                    'file_type' => strtolower($extension),
                    'file_size' => $fileSize,
                    'google_drive_file_id' => null, // Will be updated when transferred to Drive
                    'google_drive_url' => null, // Will be updated when transferred to Drive
                'uploaded_by' => $user->user_id,
            ]);
                
                // Update file management with interaction attachment ID
                $fileManagement->update(['interaction_id' => $interactionId]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'attachment' => [
                    'id' => $isVisitorUpload ? $attachment['id'] : $attachment->id,
                    'filename' => $isVisitorUpload ? $attachment['filename'] : $attachment->original_filename,
                    'size' => $isVisitorUpload ? $attachment['size'] : $attachment->getFileSizeFormatted(),
                    'type' => $isVisitorUpload ? $attachment['type'] : $attachment->file_type,
                    'url' => $isVisitorUpload ? $attachment['url'] : asset('storage/' . $fileManagement->server_path),
                    'file_management_id' => $fileManagement->id,
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('File upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file to server. Please try again.'
            ], 500);
        }
    }

    /**
     * Show password change form for staff members
     */
    public function showChangePasswordForm()
    {
        return view('staff.change-password');
    }

    /**
     * Handle password change for staff members
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
                'new_password_confirmation' => 'required|string|min:6',
            ], [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'New password must be at least 6 characters',
                'new_password.confirmed' => 'New password confirmation does not match',
                'new_password_confirmation.required' => 'Password confirmation is required',
            ]);

            $user = auth()->user();
            
            // Verify current password
            if (!password_verify($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
            }

            // Check if new password is different from current
            if (password_verify($request->new_password, $user->password)) {
                return back()->withErrors(['new_password' => 'New password must be different from current password'])->withInput();
            }

            // Update password and store in temp_password for admin visibility
            $user->update([
                'password' => bcrypt($request->new_password),
                'temp_password' => $request->new_password, // Store plain text for admin visibility
            ]);

            // Clear any relevant caches
            Cache::forget('user_' . $user->user_id . '_permissions');

            return redirect()->route('staff.change-password')->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            \Log::error('Password change error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while changing password. Please try again.'])->withInput();
        }
    }

    /**
     * Handle visitor file uploads after interaction creation
     */
    private function handleVisitorFileUploads($visitorFiles, $interactionId, $userId)
    {
        try {
            foreach ($visitorFiles as $fileData) {
                // Decode the JSON data
                $fileInfo = json_decode($fileData, true);
                
                if (!$fileInfo || !isset($fileInfo['name'])) {
                    continue; // Skip invalid file data
                }
                
                // Create the actual attachment record with Google Drive info
                \App\Models\InteractionAttachment::create([
                    'interaction_id' => $interactionId,
                    'original_filename' => $fileInfo['name'],
                    'file_type' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
                    'file_size' => $fileInfo['size'] ?? 0,
                    'google_drive_file_id' => $fileInfo['google_drive_file_id'] ?? null,
                    'google_drive_url' => $fileInfo['google_drive_url'] ?? null,
                    'uploaded_by' => $userId,
                ]);
            }
            
            \Log::info("Processed " . count($visitorFiles) . " visitor files for interaction {$interactionId}");
            
        } catch (\Exception $e) {
            \Log::error('Visitor file upload error: ' . $e->getMessage());
            // Don't throw error - just log it and continue
        }
    }

}
