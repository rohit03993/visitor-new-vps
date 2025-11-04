<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\Address;
use App\Models\VmsUser;
use App\Models\Branch;
use App\Models\UserBranchPermission;
use App\Models\Tag;
use App\Models\Course;
use App\Models\StudentSession;
use App\Helpers\DateTimeHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get all visitors with filters (cached for 5 minutes)
        $visitors = Cache::remember('admin_visitors_page_' . request()->get('page', 1), 300, function() {
            return Visitor::with(['interactions', 'lastUpdatedBy'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        });

        // Get all interactions with filters (cached for 5 minutes)
        $interactions = Cache::remember('admin_interactions_page_' . request()->get('page', 1), 300, function() {
            return InteractionHistory::with(['visitor', 'meetingWith', 'address', 'remarks', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        });

        // Get all remarks with timestamps (cached for 5 minutes)
        $remarks = Cache::remember('admin_remarks_page_' . request()->get('page', 1), 300, function() {
            return Remark::with(['interaction.visitor', 'addedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        });

        // Statistics (cached for different durations based on change frequency)
        $totalVisitors = Cache::remember('total_visitors', 3600, function() { // 1 hour
            return Visitor::count();
        });
        
        $totalInteractions = Cache::remember('total_interactions', 1800, function() { // 30 minutes
            return InteractionHistory::count();
        });
        
        $totalUsers = Cache::remember('total_users', 7200, function() { // 2 hours
            return VmsUser::count();
        });

        $usersByRole = Cache::remember('users_by_role', 7200, function() { // 2 hours
            return VmsUser::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray();
        });
        
        $todayInteractions = Cache::remember('today_interactions', 300, function() { // 5 minutes
            return InteractionHistory::whereDate('created_at', DateTimeHelper::today())->count();
        });

        // Branch statistics (cached for 1 hour)
        $totalBranches = Cache::remember('total_branches', 3600, function() {
            return Branch::count();
        });

        $branchStats = Cache::remember('branch_stats', 3600, function() {
            return Branch::withCount(['users', 'interactions'])->get();
        });

        return view('admin.dashboard', compact('visitors', 'interactions', 'remarks', 'totalVisitors', 'totalInteractions', 'totalUsers', 'usersByRole', 'todayInteractions', 'totalBranches', 'branchStats'));
    }

    public function showSearchForm()
    {
        return view('admin.search-mobile');
    }

    public function searchByMobile(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $mobileNumber = $request->input('mobile_number');
        $formattedMobile = '+91' . $mobileNumber;
        
        // Search for ALL students with this phone number
        $students = $this->findStudentsByPhoneNumber($mobileNumber, $formattedMobile);
        
        if (count($students) === 0) {
            return back()->withErrors(['error' => 'No students found with this mobile number.']);
        } elseif (count($students) === 1) {
            // Single student found - show profile directly
            $visitor = Visitor::findOrFail($students[0]['visitor_id']);
            
            // Get all interactions for this visitor (Admin can see all interactions)
            $interactions = $visitor->interactions()
                ->with(['address', 'meetingWith', 'createdBy', 'remarks.addedBy'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Load tags for the visitor
            $visitor->load('tags');

            return view('admin.visitor-profile', compact('visitor', 'interactions', 'mobileNumber'));
        } else {
            // Multiple students found - show selection screen
            return view('admin.student-selection', [
                'students' => $students,
                'phoneNumber' => $mobileNumber,
                'formattedPhone' => $formattedMobile
            ]);
        }
    }
    
    /**
     * Find all students associated with a phone number (same as StaffController)
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
        
        return $students->toArray();
    }

    public function exportVisitorProfile($visitorId)
    {
        $visitor = Visitor::findOrFail($visitorId);
        
        $interactions = $visitor->interactions()
            ->with(['address', 'meetingWith', 'createdBy', 'remarks.addedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'visitor' => $visitor,
            'interactions' => $interactions,
            'first_interaction' => $interactions->last(),
            'last_interaction' => $interactions->first(),
            'total_interactions' => $interactions->count(),
        ];

        return view('admin.visitor-profile', $data);
    }

    public function manageUsers()
    {
        $users = VmsUser::with('branch')->orderBy('name')->get();
        $branches = Branch::orderBy('branch_name')->get();
        
        return view('admin.manage-users', compact('users', 'branches'));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:vms_users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff',
            'mobile_number' => 'nullable|string|max:15',
            'branch_id' => 'required|exists:branches,branch_id',
            'can_view_remarks' => 'boolean',
            'can_download_excel' => 'boolean',
        ]);

        VmsUser::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'temp_password' => $request->password, // Store original password for admin convenience
            'role' => $request->role,
            'mobile_number' => $request->mobile_number,
            'branch_id' => $request->branch_id,
            'can_view_remarks' => $request->boolean('can_view_remarks'),
            'can_download_excel' => $request->boolean('can_download_excel'),
        ]);

        return redirect()->route('admin.manage-users')
            ->with('success', 'User created successfully!');
    }

    public function updateUser(Request $request, $userId)
    {
        $user = VmsUser::findOrFail($userId);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:vms_users,username,' . $userId . ',user_id',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,staff',
            'mobile_number' => 'nullable|string|max:15',
            'branch_id' => 'required|exists:branches,branch_id',
            'can_view_remarks' => 'boolean',
            'can_download_excel' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'mobile_number' => $request->mobile_number,
            'branch_id' => $request->branch_id,
            'can_view_remarks' => $request->boolean('can_view_remarks'),
            'can_download_excel' => $request->boolean('can_download_excel'),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
            $updateData['temp_password'] = $request->password; // Store original password for admin convenience
        }

        $user->update($updateData);

        return redirect()->route('admin.manage-users')
            ->with('success', 'User updated successfully!');
    }

    public function getCurrentPassword($userId)
    {
        $user = VmsUser::findOrFail($userId);
        
        // Check if we have a temporary password stored (for admin convenience)
        $tempPassword = $user->temp_password ?? null;
        
        if ($tempPassword) {
            return response()->json([
                'success' => true,
                'password' => $tempPassword,
                'message' => 'Current password retrieved successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'password' => '',
                'message' => 'No temporary password stored. Password was set before this feature was added. To reset password, enter a new one above.'
            ]);
        }
    }

    public function manageBranches()
    {
        $branches = Branch::with(['users', 'createdBy'])->orderBy('branch_name')->get();
        
        return view('admin.manage-branches', compact('branches'));
    }

    public function createBranch(Request $request)
    {
        try {
            $request->validate([
                'branch_name' => 'required|string|max:255|unique:branches,branch_name',
                'branch_code' => 'nullable|string|max:50|unique:branches,branch_code',
                'address' => 'nullable|string|max:500',
            ]);

            // Generate branch code if not provided
            $branchCode = $request->branch_code;
            if (empty($branchCode)) {
                $branchCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->branch_name), 0, 10));
                
                // Ensure uniqueness
                $counter = 1;
                $originalCode = $branchCode;
                while (Branch::where('branch_code', $branchCode)->exists()) {
                    $branchCode = $originalCode . $counter;
                    $counter++;
                }
            }

            Branch::create([
                'branch_name' => $request->branch_name,
                'branch_code' => $branchCode,
                'address' => $request->address,
                'created_by' => auth()->user()->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the branch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBranch(Request $request, $branchId)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255|unique:branches,branch_name,' . $branchId . ',branch_id',
            'branch_code' => 'nullable|string|max:50|unique:branches,branch_code,' . $branchId . ',branch_id',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $branch = Branch::findOrFail($branchId);
            $branch->update([
                'branch_name' => $request->branch_name,
                'branch_code' => $request->branch_code,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully!',
                'branch' => $branch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update branch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteBranch($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        
        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete branch. It has associated users.']);
        }
        
        $branch->delete();
        
        return redirect()->route('admin.manage-branches')
            ->with('success', "Branch '{$branch->branch_name}' deleted successfully!");
    }

    public function manageLocations()
    {
        $addresses = Address::with('createdBy')->orderBy('address_name')->get();
        
        return view('admin.manage-locations', compact('addresses'));
    }

    public function createLocation(Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255|unique:addresses,address_name',
            'full_address' => 'required|string|max:500',
        ]);

        Address::create([
            'address_name' => $request->address_name,
            'full_address' => $request->full_address,
            'created_by' => auth()->user()->user_id,
        ]);

        return redirect()->route('admin.manage-locations')
            ->with('success', 'Address created successfully!');
    }

    public function deleteLocation($addressId)
    {
        $address = Address::findOrFail($addressId);
        
        // Get count of interactions for this address
        $interactionCount = $address->interactions()->count();
        
        if ($interactionCount > 0) {
            // First, delete all remarks associated with these interactions
            foreach ($address->interactions as $interaction) {
                $interaction->remarks()->delete();
            }
            
            // Then delete all associated interactions
            $address->interactions()->delete();
            
            // Finally delete the address
            $address->delete();
            
            return redirect()->route('admin.manage-locations')
                ->with('success', "Address '{$address->address_name}' deleted successfully! {$interactionCount} associated interactions and their remarks were also removed.");
        } else {
            // No interactions, just delete the address
            $address->delete();
            
            return redirect()->route('admin.manage-locations')
                ->with('success', "Address '{$address->address_name}' deleted successfully!");
        }
    }

    public function analytics()
    {
        // Frequent visitors
        $frequentVisitors = Visitor::withCount('interactions')
            ->orderBy('interactions_count', 'desc')
            ->limit(10)
            ->get();

        // Top employees by assigned interactions
        $topEmployees = VmsUser::where('role', 'staff')
            ->withCount('assignedInteractions')
            ->orderBy('assigned_interactions_count', 'desc')
            ->get();

        // Interactions by purpose
        $interactionsByPurpose = InteractionHistory::selectRaw('purpose, COUNT(*) as count')
            ->groupBy('purpose')
            ->orderBy('count', 'desc')
            ->get();

        // Interactions by address
        $interactionsByAddress = Address::withCount('interactions')
            ->orderBy('interactions_count', 'desc')
            ->get();

        return view('admin.analytics', compact('frequentVisitors', 'topEmployees', 'interactionsByPurpose', 'interactionsByAddress'));
    }

    public function getBranchPermissions($userId)
    {
        $user = VmsUser::findOrFail($userId);
        $branches = Branch::all();
        $permissions = $user->branchPermissions()->get();

        return response()->json([
            'branches' => $branches,
            'permissions' => $permissions
        ]);
    }

    public function saveBranchPermissions(Request $request, $userId)
    {
        \Log::info('SaveBranchPermissions called', [
            'userId' => $userId,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'permissions' => 'required|string'
        ]);

        try {
            $permissions = json_decode($request->permissions, true);
            \Log::info('Decoded permissions', ['permissions' => $permissions]);
            
            // Delete existing permissions for this user
            UserBranchPermission::where('user_id', $userId)->delete();
            
            // Create new permissions
            foreach ($permissions as $permission) {
                if ($permission['can_view_remarks'] || $permission['can_download_excel']) {
                    UserBranchPermission::create([
                        'user_id' => $userId,
                        'branch_id' => $permission['branch_id'],
                        'can_view_remarks' => $permission['can_view_remarks'],
                        'can_download_excel' => $permission['can_download_excel'],
                    ]);
                }
            }

            \Log::info('Permissions saved successfully for user', ['userId' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Branch permissions updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving branch permissions', [
                'userId' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about what will be affected when deactivating a user
     */
    public function getUserDeactivateStats($userId)
    {
        try {
            $user = VmsUser::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Count interactions created by this user
            $interactionsCreated = InteractionHistory::where('created_by', $userId)->count();
            
            // Count interactions assigned to this user (will still show user name)
            $interactionsAssigned = InteractionHistory::where('meeting_with', $userId)->count();
            
            // Count remarks added by this user
            $remarksAdded = Remark::where('added_by', $userId)->count();
            
            // Count visitors created by this user (for frontdesk users)
            $visitorsCreated = 0;
            if ($user->role === 'frontdesk') {
                $visitorsCreated = Visitor::where('created_by', $userId)->count();
            }

            // Get sample interactions that will be affected
            $sampleInteractions = InteractionHistory::where('created_by', $userId)
                ->with(['visitor', 'address'])
                ->limit(5)
                ->get()
                ->map(function($interaction) {
                    return [
                        'visitor_name' => $interaction->name_entered,
                        'purpose' => $interaction->purpose,
                        'date' => $interaction->created_at->format('M d, Y'),
                        'address' => $interaction->address->address_name ?? 'N/A'
                    ];
                });

            // Get sample assigned interactions (will show "No Data")
            $sampleAssigned = InteractionHistory::where('meeting_with', $userId)
                ->with(['visitor', 'address'])
                ->limit(5)
                ->get()
                ->map(function($interaction) {
                    return [
                        'visitor_name' => $interaction->name_entered,
                        'purpose' => $interaction->purpose,
                        'date' => $interaction->created_at->format('M d, Y'),
                        'address' => $interaction->address->address_name ?? 'N/A'
                    ];
                });

            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'user_id' => $user->user_id
                ],
                'statistics' => [
                    'interactions_created' => $interactionsCreated,
                    'interactions_assigned' => $interactionsAssigned,
                    'remarks_added' => $remarksAdded,
                    'visitors_created' => $visitorsCreated
                ],
                'samples' => [
                    'interactions_created' => $sampleInteractions,
                    'interactions_assigned' => $sampleAssigned
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting user statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a user (soft delete by setting is_active to false)
     */
    public function deactivateUser($userId)
    {
        try {
            // Check if user exists
            $user = VmsUser::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Prevent self-deactivation
            if ($user->user_id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 400);
            }

            // Prevent deactivation of the last admin
            if ($user->role === 'admin') {
                $activeAdminCount = VmsUser::where('role', 'admin')->where('is_active', true)->count();
                if ($activeAdminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot deactivate the last admin user'
                    ], 400);
                }
            }

            \Log::info('Starting user deactivation process', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'role' => $user->role
            ]);

            // Simply deactivate the user
            $user->update(['is_active' => false]);

            // Clear the employees cache to update meeting dropdowns
            Cache::forget('active_staff_list');

            \Log::info('User deactivation completed successfully', [
                'user_id' => $userId,
                'user_name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deactivating user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deactivating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a user (set is_active to true)
     */
    public function reactivateUser($userId)
    {
        try {
            // Check if user exists
            $user = VmsUser::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user is already active
            if ($user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already active'
                ], 400);
            }

            \Log::info('Starting user reactivation process', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'role' => $user->role
            ]);

            // Simply reactivate the user
            $user->update(['is_active' => true]);

            // Clear the employees cache to update meeting dropdowns
            Cache::forget('active_staff_list');

            \Log::info('User reactivation completed successfully', [
                'user_id' => $userId,
                'user_name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User reactivated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reactivating user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reactivating user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function allVisitors()
    {
        $visitors = Visitor::with(['interactions', 'lastUpdatedBy'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.all-visitors', compact('visitors'));
    }

    public function allInteractions()
    {
        $interactions = InteractionHistory::with(['visitor', 'meetingWith.branch', 'address', 'remarks', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.all-interactions', compact('interactions'));
    }

    public function todayInteractions()
    {
        $interactions = InteractionHistory::with(['visitor', 'meetingWith.branch', 'address', 'remarks', 'createdBy'])
            ->whereDate('created_at', DateTimeHelper::today())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.today-interactions', compact('interactions'));
    }

    public function manageTags()
    {
        $tags = \App\Models\Tag::withCount('visitors')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.manage-tags', compact('tags'));
    }

    public function createTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ]);

        \App\Models\Tag::create([
            'name' => $request->name,
            'color' => $request->color ?? '#007bff',
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully!'
        ]);
    }

    public function updateTag(Request $request, $tagId)
    {
        try {
            $tag = \App\Models\Tag::findOrFail($tagId);

            // Log the incoming request data
            \Log::info('UpdateTag request data', [
                'tagId' => $tagId,
                'request_data' => $request->all(),
                'request_method' => $request->method()
            ]);

            $request->validate([
                'name' => 'required|string|max:255|unique:tags,name,' . $tagId,
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'description' => 'nullable|string|max:500',
            ]);

            $oldName = $tag->name;
            
            $tag->update([
                'name' => $request->name,
                'color' => $request->color ?? $tag->color,
                'description' => $request->description,
            ]);

            // Update all historical interaction purposes that use this tag
            if ($oldName !== $request->name) {
                \App\Models\InteractionHistory::where('purpose', $oldName)
                    ->update(['purpose' => $request->name]);
            }

            \Log::info('Tag updated successfully', [
                'tagId' => $tagId,
                'old_name' => $oldName,
                'new_name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating tag', [
                'tagId' => $tagId,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the tag: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteTag($tagId)
    {
        $tag = \App\Models\Tag::findOrFail($tagId);

        if (!$tag->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tag. It is being used by ' . $tag->visitors()->count() . ' visitor(s).'
            ], 400);
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully!'
        ]);
    }

    public function toggleTagStatus($tagId)
    {
        $tag = \App\Models\Tag::findOrFail($tagId);
        $tag->update(['is_active' => !$tag->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Tag status updated successfully!'
        ]);
    }

    public function filterVisitors(Request $request)
    {
        $query = Visitor::with(['tags', 'interactions', 'lastUpdatedBy']);

        // Filter by tags - simple OR logic (any selected tag)
        if ($request->has('tags') && !empty($request->tags)) {
            $tagIds = $request->tags;
            $query->whereHas('tags', function($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        $visitors = $query->orderBy('updated_at', 'desc')->paginate(20);

        // Get all tags for filter dropdown
        $allTags = \App\Models\Tag::active()->orderBy('name')->get();

        return view('admin.filtered-visitors', compact('visitors', 'allTags'));
    }

    public function filterInteractions(Request $request)
    {
        $query = InteractionHistory::with(['visitor.tags', 'meetingWith.branch', 'address', 'remarks', 'createdBy']);

        // Filter by visitor tags - simple OR logic (any selected tag)
        if ($request->has('tags') && !empty($request->tags)) {
            $tagIds = $request->tags;
            $query->whereHas('visitor.tags', function($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        $interactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all tags for filter dropdown
        $allTags = \App\Models\Tag::active()->orderBy('name')->get();

        return view('admin.filtered-interactions', compact('interactions', 'allTags'));
    }

    // ==================== COURSE MANAGEMENT ====================

    public function manageCourses()
    {
        $courses = Course::with('creator')->orderBy('course_name')->paginate(20);
        return view('admin.manage-courses', compact('courses'));
    }

    public function createCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255|unique:courses,course_name',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate course code if not provided
        $courseCode = $request->course_code;
        if (empty($courseCode)) {
            $courseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->course_name), 0, 10));
            
            // Ensure uniqueness
            $counter = 1;
            $originalCode = $courseCode;
            while (Course::where('course_code', $courseCode)->exists()) {
                $courseCode = $originalCode . $counter;
                $counter++;
            }
        }

        Course::create([
            'course_name' => $request->course_name,
            'course_code' => $courseCode,
            'description' => $request->description,
            'created_by' => auth()->user()->user_id,
        ]);

        // Clear cache
        Cache::forget('active_courses_list');

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully!'
        ]);
    }

    public function updateCourse(Request $request, $courseId)
    {
        $request->validate([
            'course_name' => 'required|string|max:255|unique:courses,course_name,' . $courseId . ',course_id',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code,' . $courseId . ',course_id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $course = Course::findOrFail($courseId);
            $course->update([
                'course_name' => $request->course_name,
                'course_code' => $request->course_code,
                'description' => $request->description,
                'is_active' => $request->has('is_active') ? $request->is_active : $course->is_active,
            ]);

            // Clear cache
            Cache::forget('active_courses_list');

            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the course.'
            ], 500);
        }
    }

    public function deleteCourse($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            
            // Check if course is being used by any visitors
            $visitorCount = $course->visitors()->count();
            if ($visitorCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete course. It is being used by {$visitorCount} visitor(s)."
                ], 400);
            }

            $course->delete();

            // Clear cache
            Cache::forget('active_courses_list');

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the course.'
            ], 500);
        }
    }

    /**
     * Reset all visitor data while preserving system data
     */
    public function resetVisitorData()
    {
        try {
            \DB::beginTransaction();

            // Get count of data to be deleted for confirmation
            $visitorCount = Visitor::count();
            $interactionCount = InteractionHistory::count();
            $remarkCount = Remark::count();
            $sessionCount = StudentSession::count();
            $phoneCount = \App\Models\VisitorPhoneNumber::count();
            $attachmentCount = \App\Models\InteractionAttachment::count();

            // Delete in correct order (respecting foreign key constraints)
            
            // 1. Delete interaction attachments and their Google Drive files
            $attachments = \App\Models\InteractionAttachment::all();
            foreach ($attachments as $attachment) {
                try {
                    // Delete from Google Drive
                    $googleDriveService = new \App\Services\GoogleDriveService();
                    $googleDriveService->deleteFile($attachment->google_drive_file_id);
                } catch (\Exception $e) {
                    \Log::warning('Could not delete Google Drive file: ' . $e->getMessage());
                }
            }
            \App\Models\InteractionAttachment::query()->delete();

            // 2. Delete remarks (has foreign key to interaction_history)
            Remark::query()->delete();

            // 3. Delete interaction history (has foreign key to visitors)
            InteractionHistory::query()->delete();

            // 4. Delete student sessions (has foreign key to visitors)
            StudentSession::query()->delete();

            // 5. Delete visitor phone numbers (has foreign key to visitors)
            \App\Models\VisitorPhoneNumber::query()->delete();

            // 6. Delete visitor-tag associations (pivot table)
            \DB::table('visitor_tags')->delete();

            // 7. Delete visitors (main table)
            Visitor::query()->delete();

            \DB::commit();

            // Clear relevant caches
            Cache::flush();

            \Log::info('Visitor data reset completed by admin user: ' . auth()->user()->name, [
                'deleted_counts' => [
                    'visitors' => $visitorCount,
                    'interactions' => $interactionCount,
                    'remarks' => $remarkCount,
                    'sessions' => $sessionCount,
                    'phone_numbers' => $phoneCount,
                    'attachments' => $attachmentCount
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All visitor data has been successfully reset!',
                'deleted_counts' => [
                    'visitors' => $visitorCount,
                    'interactions' => $interactionCount,
                    'remarks' => $remarkCount,
                    'sessions' => $sessionCount,
                    'phone_numbers' => $phoneCount,
                    'attachments' => $attachmentCount
                ]
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error resetting visitor data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset visitor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about what will be affected by reset
     */
    public function getResetStats()
    {
        try {
            $stats = [
                'visitors' => Visitor::count(),
                'interactions' => InteractionHistory::count(),
                'remarks' => Remark::count(),
                'sessions' => StudentSession::count(),
                'phone_numbers' => \App\Models\VisitorPhoneNumber::count(),
                'attachments' => \App\Models\InteractionAttachment::count(),
            ];

            $totalRecords = array_sum($stats);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'total_records' => $totalRecords,
                'message' => $totalRecords > 0 
                    ? "This will delete {$totalRecords} records across all visitor-related tables."
                    : "No visitor data found to reset."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get reset statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show file management interface
     */
    public function fileManagement()
    {
        try {
            // Show only non-deleted files
            $files = \App\Models\FileManagement::with(['uploadedBy', 'transferredBy', 'interaction', 'deletedBy'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $stats = [
                'total_files' => \App\Models\FileManagement::whereNull('deleted_at')->count(),
                'server_files' => \App\Models\FileManagement::where('status', 'server')->whereNull('deleted_at')->count(),
                'drive_files' => \App\Models\FileManagement::where('status', 'drive')->whereNull('deleted_at')->count(),
                'pending_files' => \App\Models\FileManagement::where('status', 'pending')->whereNull('deleted_at')->count(),
                'failed_files' => \App\Models\FileManagement::where('status', 'failed')->whereNull('deleted_at')->count(),
                'deleted_files' => \App\Models\FileManagement::whereNotNull('deleted_at')->count(),
                'total_size' => \App\Models\FileManagement::whereNull('deleted_at')->sum('file_size'),
            ];

            return view('admin.file-management', compact('files', 'stats'));
        } catch (\Exception $e) {
            \Log::error('File management error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load file management: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file (soft delete with physical file removal)
     */
    public function deleteFile(Request $request, $fileId)
    {
        try {
            $file = \App\Models\FileManagement::findOrFail($fileId);
            
            // Check if already deleted
            if ($file->isDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is already deleted'
                ], 400);
            }
            
            // Delete physical file if on server
            if ($file->status === 'server' && $file->server_path) {
                $fullPath = storage_path('app/public/' . $file->server_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            // Mark as deleted in database (keep original status for audit)
            $file->update([
                'deleted_at' => now(),
                'deleted_by' => auth()->id(),
                'deletion_reason' => $request->deletion_reason ?? 'Deleted by admin'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('File deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer single file to Google Drive
     */
    public function transferFilesToDrive(Request $request)
    {
        try {
            $request->validate([
                'file_id' => 'required|exists:file_management,id',
            ]);

            $file = \App\Models\FileManagement::findOrFail($request->file_id);
            
            if ($file->status !== 'server') {
                return response()->json([
                    'success' => false,
                    'message' => 'File is not on server or already transferred'
                ], 400);
            }

            // Initialize Google Drive service
            $googleDriveService = new \App\Services\GoogleDriveService();
            
            // Get file from server
            $serverPath = storage_path('app/public/' . $file->server_path);
            if (!file_exists($serverPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            // Upload to Google Drive
            $uploadResult = $googleDriveService->uploadFileFromPath($serverPath, $file->original_filename, $file->interaction_id);

            // Update file record
            $file->update([
                'status' => 'drive',
                'google_drive_file_id' => $uploadResult['google_drive_file_id'],
                'google_drive_url' => $uploadResult['google_drive_url'],
                'transferred_by' => auth()->user()->user_id,
                'transferred_at' => now(),
            ]);

            // Update InteractionAttachment if exists
            if ($file->interaction_id) {
                $attachment = \App\Models\InteractionAttachment::where('interaction_id', $file->interaction_id)
                    ->where('original_filename', $file->original_filename)
                    ->first();
                
                if ($attachment) {
                    $attachment->update([
                        'google_drive_file_id' => $uploadResult['google_drive_file_id'],
                        'google_drive_url' => $uploadResult['google_drive_url'],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'File transferred to Google Drive successfully!',
                'file' => $file->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('File transfer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk transfer files to Google Drive
     */
    public function bulkTransferFiles(Request $request)
    {
        try {
            $request->validate([
                'file_ids' => 'required|array',
                'file_ids.*' => 'exists:file_management,id',
            ]);

            $files = \App\Models\FileManagement::whereIn('id', $request->file_ids)
                ->where('status', 'server')
                ->get();

            if ($files->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files found to transfer'
                ], 400);
            }

            $googleDriveService = new \App\Services\GoogleDriveService();
            $transferred = 0;
            $failed = 0;
            $errors = [];

            foreach ($files as $file) {
                try {
                    $serverPath = storage_path('app/public/' . $file->server_path);
                    if (!file_exists($serverPath)) {
                        $errors[] = "File not found: {$file->original_filename}";
                        $failed++;
                        continue;
                    }

                    $uploadResult = $googleDriveService->uploadFileFromPath($serverPath, $file->original_filename, $file->interaction_id);

                    $file->update([
                        'status' => 'drive',
                        'google_drive_file_id' => $uploadResult['google_drive_file_id'],
                        'google_drive_url' => $uploadResult['google_drive_url'],
                        'transferred_by' => auth()->user()->user_id,
                        'transferred_at' => now(),
                    ]);

                    // Update InteractionAttachment if exists
                    if ($file->interaction_id) {
                        $attachment = \App\Models\InteractionAttachment::where('interaction_id', $file->interaction_id)
                            ->where('original_filename', $file->original_filename)
                            ->first();
                        
                        if ($attachment) {
                            $attachment->update([
                                'google_drive_file_id' => $uploadResult['google_drive_file_id'],
                                'google_drive_url' => $uploadResult['google_drive_url'],
                            ]);
                        }
                    }

                    $transferred++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to transfer {$file->original_filename}: " . $e->getMessage();
                    $file->update(['status' => 'failed']);
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Transfer completed: {$transferred} successful, {$failed} failed",
                'transferred' => $transferred,
                'failed' => $failed,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk transfer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file management status
     */
    public function getFileManagementStatus()
    {
        try {
            $stats = [
                'total_files' => \App\Models\FileManagement::count(),
                'server_files' => \App\Models\FileManagement::where('status', 'server')->count(),
                'drive_files' => \App\Models\FileManagement::where('status', 'drive')->count(),
                'pending_files' => \App\Models\FileManagement::where('status', 'pending')->count(),
                'failed_files' => \App\Models\FileManagement::where('status', 'failed')->count(),
                'total_size' => \App\Models\FileManagement::sum('file_size'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('File management status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get file management status'
            ], 500);
        }
    }

    /**
     * Show app settings page
     */
    public function settings()
    {
        try {
            // Check if settings table exists
            $tableExists = false;
            try {
                $tableExists = Schema::hasTable('settings');
            } catch (\Exception $e) {
                Log::warning('Could not check settings table: ' . $e->getMessage());
            }

            if (!$tableExists) {
                // Safe config access with defaults
                $whatsappSettings = [
                    'api_key' => '',
                    'api_url' => 'https://backend.aisensy.com',
                    'template_id' => '',
                    'campaign_name' => 'Homework Notifications',
                ];
                
                try {
                    $whatsappSettings['api_key'] = config('services.sensy.api_key', '');
                    $whatsappSettings['api_url'] = config('services.sensy.api_url', 'https://backend.aisensy.com');
                    $whatsappSettings['template_id'] = config('services.sensy.template_id', '');
                    $whatsappSettings['campaign_name'] = config('services.sensy.campaign_name', 'Homework Notifications');
                } catch (\Exception $e) {
                    // Use defaults if config fails
                }
                
                return view('admin.settings', [
                    'currentLogo' => 'images/logos/default-logo.svg',
                    'tableNotExists' => true,
                    'whatsappSettings' => $whatsappSettings
                ]);
            }

            // Get logo with fallback
            $currentLogo = 'images/logos/default-logo.svg';
            try {
                $currentLogo = Setting::get('app_logo', 'images/logos/default-logo.svg');
            } catch (\Exception $e) {
                Log::warning('Could not get logo setting: ' . $e->getMessage());
            }
            
            // Get WhatsApp settings from database, fallback to config (with error handling)
            $whatsappSettings = [
                'api_key' => '',
                'api_url' => 'https://backend.aisensy.com',
                'template_id' => '',
                'campaign_name' => 'Homework Notifications',
            ];
            
            try {
                $dbApiKey = Setting::get('whatsapp_api_key');
                $dbApiUrl = Setting::get('whatsapp_api_url');
                $dbTemplateId = Setting::get('whatsapp_template_id');
                $dbCampaignName = Setting::get('whatsapp_campaign_name');
                
                // Get config values with try-catch for each
                $configApiKey = '';
                $configApiUrl = 'https://backend.aisensy.com';
                $configTemplateId = '';
                $configCampaignName = 'Homework Notifications';
                
                try {
                    $configApiKey = config('services.sensy.api_key', '');
                } catch (\Exception $e) {}
                
                try {
                    $configApiUrl = config('services.sensy.api_url', 'https://backend.aisensy.com');
                } catch (\Exception $e) {}
                
                try {
                    $configTemplateId = config('services.sensy.template_id', '');
                } catch (\Exception $e) {}
                
                try {
                    $configCampaignName = config('services.sensy.campaign_name', 'Homework Notifications');
                } catch (\Exception $e) {}
                
                $whatsappSettings = [
                    'api_key' => $dbApiKey ?: $configApiKey,
                    'api_url' => $dbApiUrl ?: $configApiUrl,
                    'template_id' => $dbTemplateId ?: $configTemplateId,
                    'campaign_name' => $dbCampaignName ?: $configCampaignName,
                ];
            } catch (\Exception $e) {
                Log::warning('Could not get WhatsApp settings from database, using defaults: ' . $e->getMessage());
                // Fallback to defaults only
                $whatsappSettings = [
                    'api_key' => '',
                    'api_url' => 'https://backend.aisensy.com',
                    'template_id' => '',
                    'campaign_name' => 'Homework Notifications',
                ];
            }
            
            return view('admin.settings', compact('currentLogo', 'whatsappSettings'));
        } catch (\Exception $e) {
            Log::error('Settings page error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return with default values if everything fails
            return view('admin.settings', [
                'currentLogo' => 'images/logos/default-logo.svg',
                'whatsappSettings' => [
                    'api_key' => '',
                    'api_url' => 'https://backend.aisensy.com',
                    'template_id' => '',
                    'campaign_name' => 'Homework Notifications',
                ]
            ]);
        }
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        // Check if settings table exists
        if (!Schema::hasTable('settings')) {
            return back()->with('error', 'Settings table not found. Please run: php artisan migrate');
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        try {
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'company-logo-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->move(public_path('images/logos'), $filename);

                // Update setting
                Setting::set('app_logo', 'images/logos/' . $filename);

                return redirect()->route('admin.settings')
                    ->with('success', 'Logo uploaded successfully!');
            }

            return back()->with('error', 'No file uploaded');

        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
    }

    /**
     * Save WhatsApp settings
     */
    public function saveWhatsAppSettings(Request $request)
    {
        // Check if settings table exists
        if (!Schema::hasTable('settings')) {
            return back()->with('error', 'Settings table not found. Please run: php artisan migrate');
        }

        $request->validate([
            'admin_password' => 'required|string',
            'api_key' => 'nullable|string|max:500',
            'api_url' => 'nullable|url|max:255',
            'template_id' => 'nullable|string|max:100',
            'campaign_name' => 'nullable|string|max:100',
        ]);

        // Verify admin password
        $correctPassword = '@Rohitpal95#';
        if ($request->admin_password !== $correctPassword) {
            return back()->withErrors(['admin_password' => 'Invalid admin password. Access denied.'])
                ->withInput($request->except('admin_password'));
        }

        try {
            // Save WhatsApp settings
            if ($request->has('api_key')) {
                Setting::set('whatsapp_api_key', $request->api_key ?? '');
            }
            if ($request->has('api_url')) {
                Setting::set('whatsapp_api_url', $request->api_url ?? '');
            }
            if ($request->has('template_id')) {
                Setting::set('whatsapp_template_id', $request->template_id ?? '');
            }
            if ($request->has('campaign_name')) {
                Setting::set('whatsapp_campaign_name', $request->campaign_name ?? 'Homework Notifications');
            }

            return redirect()->route('admin.settings')
                ->with('success', 'Server settings saved successfully!');
        } catch (\Exception $e) {
            \Log::error('WhatsApp settings save error: ' . $e->getMessage());
            return back()->with('error', 'Failed to save server settings: ' . $e->getMessage());
        }
    }

}
