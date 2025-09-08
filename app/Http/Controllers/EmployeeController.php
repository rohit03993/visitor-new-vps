<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\Visitor;
use App\Models\Address;
use App\Models\VmsUser;
use App\Helpers\DateTimeHelper;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get interactions assigned to this employee with pagination
        $assignedInteractions = InteractionHistory::where('meeting_with', $user->user_id)
            ->with(['visitor', 'address', 'remarks', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Separate pending and completed interactions
        $pendingInteractions = $assignedInteractions->filter(function($interaction) {
            return $interaction->hasPendingRemarks();
        });

        $completedInteractions = $assignedInteractions->filter(function($interaction) {
            return $interaction->isCompleted();
        });

        return view('employee.dashboard', compact('pendingInteractions', 'completedInteractions', 'assignedInteractions'));
    }

    public function updateRemark(Request $request, $interactionId)
    {
        $request->validate([
            'remark_text' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $interaction = InteractionHistory::findOrFail($interactionId);

        // Check if this employee is assigned to this interaction
        if ($interaction->meeting_with !== $user->user_id) {
            return back()->withErrors(['error' => 'You are not authorized to update this remark.']);
        }

        // Create new remark (don't overwrite existing ones)
        Remark::create([
            'interaction_id' => $interaction->interaction_id,
            'remark_text' => $request->remark_text,
            'added_by' => $user->user_id,
            'is_editable_by' => $user->user_id,
        ]);

        // Update visitor's last_updated_by to reflect this remark activity
        $visitor = $interaction->visitor;
        if ($visitor) {
            $visitor->update([
                'last_updated_by' => $user->user_id,
            ]);
        }

        return redirect()->route('employee.dashboard')
            ->with('success', 'Remark updated successfully!');
    }

    public function getVisitorHistory($visitorId)
    {
        $user = auth()->user();
        
        // Get visitor
        $visitor = Visitor::findOrFail($visitorId);
        
        // Get interactions where this employee was assigned
        $interactions = InteractionHistory::where('visitor_id', $visitorId)
            ->where('meeting_with', $user->user_id)
            ->with(['address', 'remarks', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employee.visitor-history', compact('visitor', 'interactions'));
    }

    public function showVisitorSearch()
    {
        return view('employee.visitor-search');
    }

    public function searchVisitor(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $user = auth()->user();
        $mobileNumber = $request->mobile_number;
        $formattedMobile = '+91' . $mobileNumber;

        // Find visitor
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
            ->first();

        if ($visitor) {
            // Visitor exists, show their history and option to add new entry
            $interactions = InteractionHistory::where('visitor_id', $visitor->visitor_id)
                ->with(['address', 'remarks', 'createdBy', 'meetingWith'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('employee.search-results', compact('visitor', 'interactions', 'mobileNumber'));
        } else {
            // Visitor doesn't exist, redirect to form
            return redirect()->route('employee.visitor-form')
                ->with('prefilledMobile', $mobileNumber);
        }
    }

    public function showVisitorForm(Request $request)
    {
        $user = auth()->user();
        
        // Get employee's branch data
        $branch = $user->branch;
        if (!$branch) {
            return redirect()->route('employee.dashboard')
                ->withErrors(['error' => 'You are not assigned to any branch. Please contact administrator.']);
        }

        // Get all addresses (addresses are global, not branch-specific)
        $addresses = Address::orderBy('address_name')->get();

        // Get purposes (you might want to make this configurable)
        $purposes = [
            'Meeting',
            'Interview',
            'Delivery',
            'Service',
            'Consultation',
            'Training',
            'Other'
        ];

        $prefilledMobile = $request->session()->get('prefilledMobile', '');
        
        // Check if visitor exists in database
        $isExistingVisitor = false;
        if (!empty($prefilledMobile)) {
            $formattedMobile = '+91' . $prefilledMobile;
            $visitor = Visitor::where('mobile_number', $formattedMobile)
                ->orWhere('mobile_number', $prefilledMobile)
                ->first();
            $isExistingVisitor = $visitor ? true : false;
        }

        return view('employee.visitor-form', compact('addresses', 'purposes', 'prefilledMobile', 'branch', 'isExistingVisitor'));
    }

    public function checkMobile(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $mobileNumber = $request->mobile_number;
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
                    'last_visit' => $visitor->updated_at ? $visitor->updated_at->format('M d, Y') : 'Never'
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
        $branch = $user->branch;

        if (!$branch) {
            return response()->json(['error' => 'You are not assigned to any branch.'], 400);
        }

        $address = Address::create([
            'address_name' => $request->address_name,
            'created_by' => $user->user_id,
        ]);

        return response()->json([
            'success' => true,
            'address' => [
                'id' => $address->address_id,
                'name' => $address->address_name
            ]
        ]);
    }

    public function storeVisitor(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string|regex:/^[0-9]{10}$/',
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'address_id' => 'required|exists:addresses,address_id',
            'remarks' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $mobileNumber = $request->mobile_number;
        $formattedMobile = '+91' . $mobileNumber;

        // Find or create visitor
        $visitor = Visitor::where('mobile_number', $formattedMobile)
            ->orWhere('mobile_number', $mobileNumber)
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

        // Create interaction record (employee assigns to themselves)
        $interaction = InteractionHistory::create([
            'visitor_id' => $visitor->visitor_id,
            'name_entered' => $request->name,
            'mode' => 'In-Campus', // Default for employee entries
            'purpose' => $request->purpose,
            'address_id' => $request->address_id,
            'meeting_with' => $user->user_id, // Employee assigns to themselves
            'created_by' => $user->user_id,
        ]);

        // Create initial remark
        Remark::create([
            'interaction_id' => $interaction->interaction_id,
            'remark_text' => $request->remarks,
            'added_by' => $user->user_id,
            'is_editable_by' => $user->user_id,
        ]);

        return redirect()->route('employee.dashboard')
            ->with('success', 'Visitor entry created successfully! You can now update remarks as needed.');
    }
}
