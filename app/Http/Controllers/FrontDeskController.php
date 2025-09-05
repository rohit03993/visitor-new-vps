<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\Address;
use App\Models\VmsUser;
use App\Helpers\DateTimeHelper;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessVisitorRegistration;

class FrontDeskController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get user's permitted branch IDs
        $permittedBranchIds = $user->getAllowedBranchIds('can_view_remarks');
        \Log::info('Dashboard - User permitted branches', [
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'permitted_branches' => $permittedBranchIds,
            'user_branch' => $user->branch_id ?? 'No branch assigned'
        ]);
        
        // Get all interactions - all frontdesk users can see all data
        $interactions = Cache::remember('frontdesk_interactions_' . $user->user_id . '_page_' . request()->get('page', 1), 300, function() {
            return InteractionHistory::with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
                ->orderBy('created_at', 'desc')
                ->paginate(5);
        });

        \Log::info('Dashboard - Interactions loaded', [
            'total_count' => $interactions->total(),
            'current_page' => $interactions->currentPage(),
            'per_page' => $interactions->perPage()
        ]);

        return view('frontdesk.dashboard', compact('interactions'));
    }

    public function showVisitorForm()
    {
        // Cache employees and locations for 1 hour (they don't change often)
        // Use a cache key that includes active status to ensure deactivated users are excluded
        $employees = Cache::remember('active_employees_list', 3600, function() {
            return VmsUser::where('role', 'employee')->where('is_active', true)->get();
        });
        
        $addresses = Cache::remember('addresses_list', 3600, function() {
            return Address::all();
        });
        
        return view('frontdesk.visitor-form', compact('employees', 'addresses'));
    }

    public function checkMobile(Request $request)
    {
        $mobileNumber = $request->input('mobile_number');
        
        // Format mobile number with +91 prefix for database search
        $formattedMobile = '+91' . $mobileNumber;
        
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();
        
        if ($visitor) {
            // Get last interaction details for autofill
            $lastInteraction = $visitor->interactions()->latest()->first();
            
            return response()->json([
                'exists' => true,
                'visitor' => [
                    'name' => $visitor->name,
                    'last_location' => $lastInteraction ? $lastInteraction->address->address_name : null,
                    'last_purpose' => $lastInteraction ? $lastInteraction->purpose : null,
                    'last_meeting_with' => $lastInteraction ? $lastInteraction->meetingWith->name : null,
                    'last_meeting_with_branch' => $lastInteraction ? $lastInteraction->meetingWith->getBranchName() : null,
                    'last_interaction_date' => $lastInteraction ? DateTimeHelper::formatIndianDate($lastInteraction->created_at) : null,
                    'last_interaction_time' => $lastInteraction ? DateTimeHelper::formatIndianTime($lastInteraction->created_at) : null,
                ]
            ]);
        }
        
        return response()->json(['exists' => false]);
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        $address = Address::findOrCreate(
            $request->address_name,
            $request->address_name,
            $user->user_id
        );

        return response()->json([
            'success' => true,
            'address_id' => $address->address_id,
            'address_name' => $address->address_name
        ]);
    }

    public function storeVisitor(Request $request)
    {
        // Fallback: If address_id is not provided but address_input is, create the address FIRST
        if (!$request->address_id && $request->address_input) {
            $address = Address::findOrCreate(
                $request->address_input,
                $request->address_input,
                auth()->user()->user_id
            );
            $request->merge(['address_id' => $address->address_id]);
        }

        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
            'name' => 'required|string|max:255',
            'mode' => 'required|in:In-Campus,Out-Campus,Telephonic',
            'purpose' => 'required|in:Parent,Student,Ex-student,New Admission,Marketing,News & Media,Advertising',
            'address_id' => 'required|exists:addresses,address_id',
            'meeting_with' => 'required|exists:vms_users,user_id',
            'remarks' => 'required|string',
        ]);

        $user = auth()->user();

        // Format mobile number with +91 prefix
        $formattedMobile = '+91' . $request->mobile_number;

        // Find or create visitor
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $request->mobile_number)
            ->first();
        
        if (!$visitor) {
            $visitor = Visitor::create([
                'mobile_number' => $formattedMobile,
                'name' => $request->name,
                'last_updated_by' => $user->user_id,
            ]);
        } else {
            // Update visitor name and last_updated_by
            $visitor->update([
                'name' => $request->name,
                'last_updated_by' => $user->user_id,
            ]);
        }

        // Create interaction record
        $interaction = InteractionHistory::create([
            'visitor_id' => $visitor->visitor_id,
            'name_entered' => $request->name,
            'mode' => $request->mode,
            'purpose' => $request->purpose,
            'address_id' => $request->address_id,
            'meeting_with' => $request->meeting_with,
            'created_by' => $user->user_id,
        ]);

        // Create initial remark
        Remark::create([
            'interaction_id' => $interaction->interaction_id,
            'remark_text' => $request->remarks,
            'added_by' => $user->user_id,
            'is_editable_by' => $request->meeting_with,
        ]);

        // Clear relevant caches to ensure fresh data
        Cache::forget('frontdesk_today_interactions_' . $user->user_id);
        Cache::forget('frontdesk_all_interactions_' . $user->user_id . '_page_1');
        Cache::forget('total_visitors');
        Cache::forget('total_interactions');
        Cache::forget('today_interactions');

        // Queue heavy operations for background processing
        ProcessVisitorRegistration::dispatch([
            'visitor_id' => $visitor->visitor_id,
            'interaction_id' => $interaction->interaction_id,
            'name' => $request->name,
            'mobile' => $formattedMobile,
            'purpose' => $request->purpose
        ], $user->user_id);

        return redirect()->route('frontdesk.dashboard')
            ->with('success', 'Visitor entry created successfully!');
    }

    public function showSearchForm()
    {
        return view('frontdesk.search-visitors');
    }

    public function searchVisitors(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $mobileNumber = $request->input('mobile_number');
        
        // Format mobile number with +91 prefix for database search
        $formattedMobile = '+91' . $mobileNumber;
        
        // Get user's permitted branch IDs
        $user = auth()->user();
        $permittedBranchIds = $user->getAllowedBranchIds('can_view_remarks');
        
        \Log::info('Search - User permitted branches', [
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'permitted_branches' => $permittedBranchIds,
            'mobile_number' => $mobileNumber
        ]);
        
        // Show all visitors matching mobile number - all frontdesk users can see all data
        $interactions = InteractionHistory::whereHas('visitor', function($query) use ($formattedMobile, $mobileNumber) {
            $query->where('mobile_number', $formattedMobile)
                  ->orWhere('mobile_number', $mobileNumber);
        })
        ->with(['visitor', 'meetingWith.branch', 'address', 'remarks'])
        ->orderBy('created_at', 'desc')
        ->paginate(5);

        \Log::info('Search - Results found', [
            'interactions_count' => $interactions->count()
        ]);

        return view('frontdesk.search-results', compact('interactions', 'mobileNumber'));
    }

    public function getInteractionRemarks($interactionId)
    {
        $user = auth()->user();
        $interaction = InteractionHistory::with(['visitor', 'meetingWith', 'remarks.addedBy'])->findOrFail($interactionId);
        
        \Log::info('Getting interaction remarks', [
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'interaction_id' => $interactionId,
            'total_remarks' => $interaction->remarks->count()
        ]);
        
        // Check if user can view remarks for this interaction
        if (!$user->canViewRemarksForInteraction($interaction)) {
            \Log::info('User cannot view remarks for interaction', [
                'user_id' => $user->user_id,
                'interaction_id' => $interactionId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view remarks for this interaction'
            ], 403);
        }
        
        // Filter remarks based on permissions
        $filteredRemarks = $interaction->remarks->filter(function($remark) use ($user) {
            \Log::info('Filtering remark', [
                'remark_id' => $remark->id,
                'remark_text' => $remark->remark_text,
                'added_by' => $remark->added_by,
                'added_by_name' => $remark->addedBy->name ?? 'Unknown',
                'added_by_branch' => $remark->addedBy->branch_id ?? 'No branch',
                'current_user_id' => $user->user_id
            ]);
            
            // User can always see their own remarks
            if ($remark->added_by == $user->user_id) {
                \Log::info('User can see their own remark');
                return true;
            }
            
            // Check if user has permission to view remarks from the branch of the person who added the remark
            $branchId = $remark->addedBy->branch_id ?? null;
            if ($branchId) {
                $canView = $user->canViewRemarksForBranch($branchId);
                \Log::info('Checking branch permission', [
                    'branch_id' => $branchId,
                    'can_view' => $canView
                ]);
                return $canView;
            }
            
            \Log::info('No branch found for remark author');
            return false;
        });
        
        \Log::info('Final filtered remarks count', [
            'total_remarks' => $interaction->remarks->count(),
            'filtered_remarks' => $filteredRemarks->count()
        ]);
        
        return response()->json([
            'success' => true,
            'remarks' => $filteredRemarks->map(function($remark) {
                return [
                    'remark_text' => $remark->remark_text,
                    'added_by_name' => $remark->addedBy->name,
                    'created_at' => $remark->created_at->format('M d, Y H:i')
                ];
            }),
            'interaction' => [
                'visitor_name' => $interaction->name_entered,
                'purpose' => $interaction->purpose,
                'meeting_with' => $interaction->meetingWith->name,
                'date' => $interaction->created_at->format('M d, Y H:i')
            ]
        ]);
    }

    public function downloadTodayExcel()
    {
        $user = auth()->user();
        
        // Check if user has permission to download Excel
        $allowedBranchIds = $user->getAllowedBranchIds('can_download_excel');
        if (empty($allowedBranchIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to download Excel reports'
            ], 403);
        }
        
        // Get today's interactions where user is creator OR meeting_with, filtered by allowed branches
        $interactions = InteractionHistory::where(function($query) use ($user) {
            $query->where('created_by', $user->user_id)
                  ->orWhere('meeting_with', $user->user_id);
        })
        ->whereDate('created_at', DateTimeHelper::today())
        ->whereHas('meetingWith', function($query) use ($allowedBranchIds) {
            $query->whereIn('branch_id', $allowedBranchIds);
        })
        ->with(['visitor', 'meetingWith', 'address', 'remarks'])
        ->orderBy('created_at', 'desc')
        ->get();
        
        // Create CSV content
        $csvContent = "Date,Time,Visitor Name,Mobile,Mode,Purpose,Meeting With,Location,Status,Remarks\n";
        
        foreach ($interactions as $interaction) {
            $remarks = $interaction->remarks->map(function($remark) {
                return $remark->remark_text . ' (by ' . $remark->addedBy->name . ')';
            })->join('; ');
            
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $interaction->created_at->format('Y-m-d'),
                $interaction->created_at->format('H:i'),
                $interaction->name_entered,
                $interaction->visitor->mobile_number,
                $interaction->mode,
                $interaction->purpose,
                $interaction->meetingWith->name,
                $interaction->address->address_name ?? 'N/A',
                $interaction->hasPendingRemarks() ? 'Pending' : 'Completed',
                $remarks
            );
        }
        
        $filename = 'today_interactions_' . date('Y-m-d') . '.csv';
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
