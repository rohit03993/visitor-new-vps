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
            
            // Search for visitor
            $visitor = Visitor::where('mobile_number', '+91' . $cleanMobile)
                ->orWhere('mobile_number', $cleanMobile)
                ->first();
            
            if ($visitor) {
                // Redirect to the visitor profile page (single source of truth)
                return redirect()->route('staff.visitor-profile', $visitor->visitor_id);
            }
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
            ->with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
            ->get()
            ->filter(function($interaction) {
                // If interaction has no remarks, show it (truly pending)
                if ($interaction->remarks->count() === 0) {
                    return true;
                }
                
                // If interaction has only 1 remark and it's a transfer remark, show it (transferred interaction)
                if ($interaction->remarks->count() === 1) {
                    $remark = $interaction->remarks->first();
                    return strpos($remark->remark_text, 'Transferred from') !== false;
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
        
        // Debug: Log the count for troubleshooting
        \Log::info('Assigned interactions for user ' . $user->user_id . ': ' . $assignedInteractions->count());
        
        // Debug: Log visitor IDs
        foreach($assignedInteractions as $interaction) {
            \Log::info('Interaction ID: ' . $interaction->interaction_id . ', Visitor ID: ' . $interaction->visitor_id . ', Visitor Name: ' . $interaction->visitor->name);
        }
        
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
        
        // Search for visitor
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();
        
        if ($visitor) {
            // Redirect to the visitor profile page (single source of truth)
            return redirect()->route('staff.visitor-profile', $visitor->visitor_id);
        } else {
            // Visitor not found - redirect to visitor form
            return redirect()->route('staff.visitor-form', [
                'mobile' => $mobileNumber
            ]);
        }
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

        // Build the search query
        $query = Visitor::with(['interactions' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'course', 'interactions.meetingWith.branch']);

        // Apply search filters
        if (!empty($studentName)) {
            $query->where('student_name', 'LIKE', "%{$studentName}%");
        }

        if (!empty($fatherName)) {
            $query->where('father_name', 'LIKE', "%{$fatherName}%");
        }

        if (!empty($contactPerson)) {
            $query->where('name', 'LIKE', "%{$contactPerson}%");
        }

        if (!empty($courseId)) {
            $query->where('course_id', $courseId);
        }

        // Search by purpose in interactions
        if (!empty($purpose)) {
            $query->whereHas('interactions', function($q) use ($purpose) {
                $q->where('purpose', $purpose);
            });
        }

        // Date range filter on interactions
        if (!empty($dateFrom) || !empty($dateTo)) {
            $query->whereHas('interactions', function($q) use ($dateFrom, $dateTo) {
                if (!empty($dateFrom)) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                }
                if (!empty($dateTo)) {
                    $q->whereDate('created_at', '<=', $dateTo);
                }
            });
        }

        // Get results with pagination
        $visitors = $query->orderBy('updated_at', 'desc')->paginate(15);

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
        
        // Check if visitor exists and get last details
        $isExistingVisitor = false;
        $lastInteractionDetails = null;
        if (!empty($prefilledMobile)) {
            $formattedMobile = '+91' . $prefilledMobile;
            $visitor = Visitor::where('mobile_number', $formattedMobile)
                ->orWhere('mobile_number', $prefilledMobile)
                ->first();
            $isExistingVisitor = $visitor ? true : false;
            
            // Get last interaction details for auto-fill
            if ($visitor) {
                $lastInteraction = InteractionHistory::where('visitor_id', $visitor->visitor_id)
                    ->with(['meetingWith', 'address'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastInteraction) {
                    $lastInteractionDetails = [
                        'course_id' => $visitor->course_id,
                        'student_name' => $visitor->student_name,
                        'father_name' => $visitor->father_name,
                        'mode' => $lastInteraction->mode,
                        'meeting_with' => $lastInteraction->meeting_with,
                        'address_id' => $lastInteraction->address_id,
                        'address_name' => $lastInteraction->address->address_name ?? '',
                        'tags' => $visitor->tags->pluck('id')->toArray(),
                    ];
                }
            }
        }
        
        return view('staff.visitor-form', compact('employees', 'addresses', 'tags', 'courses', 'prefilledMobile', 'prefilledName', 'isExistingVisitor', 'originalMobileNumber', 'lastInteractionDetails'));
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

        // Find or create visitor
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();
        
        if (!$visitor) {
            $visitor = Visitor::create([
                'mobile_number' => $formattedMobile,
                'name' => $request->name,
                'course_id' => $request->course_id,
                'student_name' => $request->student_name,
                'father_name' => $request->father_name,
                'last_updated_by' => $user->user_id,
            ]);
        } else {
            // Update visitor information if different
            $updateData = ['last_updated_by' => $user->user_id];
            
            if ($visitor->name !== $request->name) {
                $updateData['name'] = $request->name;
            }
            if ($visitor->course_id != $request->course_id) {
                $updateData['course_id'] = $request->course_id;
            }
            if ($visitor->student_name !== $request->student_name) {
                $updateData['student_name'] = $request->student_name;
            }
            if ($visitor->father_name !== $request->father_name) {
                $updateData['father_name'] = $request->father_name;
            }
            
            if (count($updateData) > 1) { // More than just last_updated_by
                $visitor->update($updateData);
            }
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
            'is_completed' => false, // PENDING - will be completed after meeting
        ]);

        // Send notification to assigned staff member
        $assignedUser = VmsUser::find($request->meeting_with);
        if ($assignedUser) {
            // Send notification to assigned staff member (including self for testing)
            $notificationController = new \App\Http\Controllers\NotificationController();
            $success = $notificationController->sendVisitAssignmentNotification(
                $interaction->interaction_id,
                $assignedUser->user_id,
                $request->name,
                $purpose
            );
            
            // Log notification status for debugging
            \Log::info("Notification sent for interaction {$interaction->interaction_id} to user {$assignedUser->user_id}: " . ($success ? 'SUCCESS' : 'FAILED'));
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
        ]);

        $user = auth()->user();
        $interaction = InteractionHistory::findOrFail($interactionId);

        // Check if user can update this interaction
        if ($interaction->meeting_with != $user->user_id) {
            return back()->withErrors(['error' => 'You can only update remarks for visitors assigned to you.']);
        }

        // Create new remark (simple remark, no outcome)
        Remark::create([
            'interaction_id' => $interactionId,
            'remark_text' => $request->remark_text,
            'outcome' => 'in_process', // Always in_process for simple remarks
            'added_by' => $user->user_id,
            'added_by_name' => $user->name,
        ]);

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
        
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();
        
        if ($visitor) {
            return response()->json([
                'exists' => true,
                'visitor' => [
                    'name' => $visitor->name,
                    'mobile_number' => $visitor->mobile_number,
                ]
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
            $session = StudentSession::with(['visitor', 'starter'])->findOrFail($sessionId);
            
            if (!$session->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session is already completed or cancelled.'
                ], 400);
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
    public function showVisitorProfile($visitorId)
    {
        try {
            $user = Auth::user();
            \Log::info('=== METHOD CALLED: showVisitorProfile with ID: ' . $visitorId . ' ===');
            
            // Get visitor with basic relationships
            $visitor = Visitor::with(['course', 'tags'])->findOrFail($visitorId);
            \Log::info('Visitor found: ' . $visitor->name);
            
            // Get all interactions for this visitor
            $interactions = InteractionHistory::where('visitor_id', $visitorId)
                ->with(['meetingWith.branch', 'address', 'remarks.addedBy.branch', 'studentSession'])
                ->orderBy('created_at', 'desc')
                ->get();
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
            
            // Mask mobile number for staff privacy (display only)
            $visitor->mobile_number = $this->maskMobileNumber($visitor->mobile_number);
            
            \Log::info('About to return view with visitor: ' . $visitor->name);
            
            return view('staff.visitor-profile', [
                'visitor' => $visitor,
                'interactions' => $filteredInteractions,
                'groupedInteractions' => $groupedInteractions,
                'assignedInteractions' => $assignedInteractions,
                'user' => $user,
                'originalMobileNumber' => $originalMobileNumber
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
            \Log::info('assignInteraction called with interaction ID: ' . $interactionId);
            \Log::info('Request data: ' . json_encode($request->all()));
            
            // Validate the request
            $request->validate([
                'team_member_id' => 'required|exists:vms_users,user_id',
                'assignment_notes' => 'nullable|string|max:500',
            ]);
            
            // Get the interaction
            $interaction = InteractionHistory::findOrFail($interactionId);
            \Log::info('Found interaction: ' . json_encode($interaction->toArray()));
            
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
                'is_completed' => false, // New interaction is not completed
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Step 4: Add transfer context remark to X's new interaction
            $userBranch = $user->branch ? $user->branch->branch_name : 'Unknown Branch';
            $transferContextForX = "Transferred from {$user->name} ({$userBranch})";
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
                $notificationController = new \App\Http\Controllers\NotificationController();
                $notificationController->sendVisitAssignmentNotification(
                    $newInteraction->interaction_id,
                    $targetMember->user_id,
                    $visitor->name,
                    $interaction->purpose
                );
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
            \Log::error('assignInteraction Error: ' . $e->getMessage());
            \Log::error('assignInteraction Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer interaction: ' . $e->getMessage()
            ], 500);
        }
    }
}
