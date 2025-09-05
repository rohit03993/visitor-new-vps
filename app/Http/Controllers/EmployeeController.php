<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\Visitor;
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
}
