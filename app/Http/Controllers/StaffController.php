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

    public function showVisitorSearch()
    {
        return view('staff.visitor-search');
    }

    public function showAssignedToMe()
    {
        $user = auth()->user();
        
        // Get assigned visitors (visitors assigned to this staff member) - only those without remarks
        $assignedInteractions = InteractionHistory::where('meeting_with', $user->user_id)
            ->whereDoesntHave('remarks') // Only show interactions without remarks
            ->with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Debug: Log the count for troubleshooting
        \Log::info('Assigned interactions for user ' . $user->user_id . ': ' . $assignedInteractions->count());
        
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
            // Load visitor with course and session relationships
            $visitor->load(['course', 'studentSessions', 'activeSessions']);
            
            // Visitor found - show their history (ALL interactions, no branch filtering)
            $interactions = InteractionHistory::where('visitor_id', $visitor->visitor_id)
                ->with(['visitor', 'meetingWith.branch', 'address', 'remarks', 'studentSession', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            
            // Store original mobile number for "Add Revisit" functionality
            $originalMobileNumber = $visitor->mobile_number;
            
            // Mask mobile number for staff privacy (display only)
            $visitor->mobile_number = $this->maskMobileNumber($visitor->mobile_number);
            
            return view('staff.search-results-timeline', compact('visitor', 'interactions', 'mobileNumber', 'originalMobileNumber'));
        } else {
            // Visitor not found - redirect to visitor form
            return redirect()->route('staff.visitor-form', [
                'mobile' => $mobileNumber
            ]);
        }
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
            'father_name' => 'nullable|string|max:255',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'address_id' => 'nullable|exists:addresses,address_id',
            'address_input' => 'nullable|string|max:255',
            'meeting_with' => 'required|exists:vms_users,user_id',
            'mode' => 'required|in:In-Campus,Out-Campus,Telephonic',
            'initial_notes' => 'nullable|string|max:500',
        ]);

        // Custom validation: If course is not "None", father_name is required
        $selectedCourse = Course::find($request->course_id);
        if ($selectedCourse && $selectedCourse->course_code !== 'NONE' && empty($request->father_name)) {
            return back()->withErrors(['father_name' => 'Father\'s name is required when selecting a course.'])->withInput();
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
            if ($visitor->father_name !== $request->father_name) {
                $updateData['father_name'] = $request->father_name;
            }
            
            if (count($updateData) > 1) { // More than just last_updated_by
                $visitor->update($updateData);
            }
        }

        // Assign tags to visitor
        if ($request->has('tags')) {
            $visitor->tags()->sync($request->tags);
            
        // Create purpose string from selected tags
        $selectedTags = \App\Models\Tag::whereIn('id', $request->tags)->pluck('name')->toArray();
        $purpose = implode(', ', $selectedTags);
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

        // Clear cache
        Cache::forget('active_staff_list');

        return redirect()->route('staff.visitor-search')
            ->with('success', 'Visitor entry created successfully!');
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
}
