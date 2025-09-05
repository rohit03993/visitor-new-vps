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
use App\Helpers\DateTimeHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            return Remark::with(['interaction.visitor', 'addedBy', 'isEditableBy'])
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
        
        // Format mobile number with +91 prefix for database search
        $formattedMobile = '+91' . $mobileNumber;
        
        // Search for visitor with +91 prefix first, then without prefix as fallback
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();
        
        if (!$visitor) {
            return back()->withErrors(['error' => 'No visitor found with this mobile number.']);
        }

        // Get all interactions for this visitor (Admin can see all interactions)
        $interactions = $visitor->interactions()
            ->with(['address', 'meetingWith', 'createdBy', 'remarks.addedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.visitor-profile', compact('visitor', 'interactions', 'mobileNumber'));
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
            'role' => 'required|in:admin,frontdesk,employee',
            'mobile_number' => 'nullable|string|max:15',
            'branch_id' => 'required|exists:branches,branch_id',
            'can_view_remarks' => 'boolean',
            'can_download_excel' => 'boolean',
        ]);

        VmsUser::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
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
            'role' => 'required|in:admin,frontdesk,employee',
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
        }

        $user->update($updateData);

        return redirect()->route('admin.manage-users')
            ->with('success', 'User updated successfully!');
    }

    public function manageBranches()
    {
        $branches = Branch::with(['users', 'createdBy'])->orderBy('branch_name')->get();
        
        return view('admin.manage-branches', compact('branches'));
    }

    public function createBranch(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255|unique:branches,branch_name',
        ]);

        Branch::create([
            'branch_name' => $request->branch_name,
            'created_by' => auth()->user()->user_id,
        ]);

        return redirect()->route('admin.manage-branches')
            ->with('success', 'Branch created successfully!');
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
        $topEmployees = VmsUser::where('role', 'employee')
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
            Cache::forget('active_employees_list');

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
            Cache::forget('active_employees_list');

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
}
