@extends('layouts.app')

@section('title', 'Visitor Profile - VMS')
@section('page-title', 'Visitor Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div></div>
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="{{ route('staff.visitor-form', ['mobile' => $originalMobileNumber, 'name' => $visitor->name]) }}" 
                   class="btn btn-paytm-success">
                    <i class="fas fa-plus me-2"></i>Add Revisit
                </a>
                <button onclick="window.print()" class="btn btn-paytm-secondary">
                    <i class="fas fa-print me-2"></i>Print Profile
                </button>
                <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Search
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Profile Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Mobile Number:</strong></td>
                                <td>{{ $visitor->mobile_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Latest Name:</strong></td>
                                <td>{{ $visitor->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Interactions:</strong></td>
                                <td><span class="badge bg-primary">{{ $interactions->count() }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <table class="table table-borderless">
                            @if($visitor->course)
                            <tr>
                                <td><strong>Course Interest:</strong></td>
                                <td><span class="badge bg-info">{{ $visitor->course->course_name }}</span></td>
                            </tr>
                            @endif
                            @if($visitor->father_name)
                            <tr>
                                <td><strong>Father's Name:</strong></td>
                                <td>{{ $visitor->father_name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Interaction History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-route me-2"></i>Student Journey Timeline
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Interaction ID</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Meeting With</th>
                                    <th>Purpose</th>
                                    <th>Address</th>
                                    <th>Name Entered</th>
                                    <th>Initial Notes</th>
                                    <th>Remark(s)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>{{ $interaction->interaction_id }}</td>
                                        <td>{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->meetingWith->name ?? 'No Data' }}</td>
                                        <td>{{ $interaction->purpose }}</td>
                                        <td>{{ $interaction->address->address_name ?? 'N/A' }}</td>
                                        <td>{{ $interaction->name_entered }}</td>
                                        <td>
                                            @if($interaction->initial_notes)
                                                <small class="text-muted">{{ $interaction->initial_notes }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($interaction->remarks->count() > 0)
                                                <div class="remark-timeline">
                                                    @foreach($interaction->remarks as $remark)
                                                        <div class="remark-item mb-1">
                                                            <div class="remark-text">{{ $remark->remark_text }}</div>
                                                            <small class="text-muted">
                                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at) }} 
                                                                by {{ $remark->addedBy?->name ?? 'Unknown' }} <strong>({{ $remark->addedBy?->branch?->branch_name ?? 'No Branch' }})</strong>
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                @if($interaction->is_completed)
                                                    <span class="text-muted">No remarks</span>
                                                @else
                                                    <!-- Pending Status with Assignment Info -->
                                                    @if($interaction->meeting_with == auth()->user()->user_id)
                                                        <!-- Assigned to current staff - show button only if no remarks -->
                                                        @if($interaction->remarks->count() == 0)
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge bg-warning">Pending</span>
                                                                <button class="btn btn-primary btn-sm"
                                                                        onclick="showAddRemarkModal({{ $interaction->interaction_id }}, '{{ $interaction->name_entered }}')"
                                                                        title="Add Remark">
                                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="badge bg-warning">Pending</span>
                                                        @endif
                                                    @else
                                                        <!-- Assigned to someone else - show who -->
                                                        <span class="badge bg-warning">
                                                            Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }} 
                                                            ({{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }})
                                                        </span>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-lg-none">
                        @foreach($interactions as $interaction)
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1">
                                                <strong>{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</strong>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                @if($interaction->meeting_with == auth()->user()->user_id)
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }} 
                                                        ({{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }})
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Interaction Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">ID:</small>
                                            <div><strong>{{ $interaction->interaction_id }}</strong></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small>
                                            <div>
                                                <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                    {{ $interaction->mode }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Visitor:</small>
                                            <div><strong>{{ $interaction->name_entered }}</strong></div>
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small>
                                            <div>{{ $interaction->meetingWith->name ?? 'No Data' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Purpose:</small>
                                            <div>{{ $interaction->purpose }}</div>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Address:</small>
                                            <div>{{ $interaction->address->address_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <!-- Initial Notes -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Initial Notes:</small>
                                            @if($interaction->initial_notes)
                                                <div><small class="text-muted">{{ $interaction->initial_notes }}</small></div>
                                            @else
                                                <div><span class="text-muted">-</span></div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Remarks Section -->
                                    <div class="border-top pt-2">
                                        <small class="text-muted">Remark(s):</small>
                                        <div class="mt-1">
                                            @if($interaction->remarks->count() > 0)
                                                <div class="remark-timeline">
                                                    @foreach($interaction->remarks as $remark)
                                                        <div class="remark-item mb-2">
                                                            <div class="remark-text">{{ $remark->remark_text }}</div>
                                                            <small class="text-muted">
                                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at) }} 
                                                                by {{ $remark->addedBy?->name ?? 'Unknown' }} <strong>({{ $remark->addedBy?->branch?->branch_name ?? 'No Branch' }})</strong>
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                @if($interaction->is_completed)
                                                    <span class="text-muted">
                                                        <i class="fas fa-comment-slash me-1"></i>No remarks
                                                    </span>
                                                @else
                                                    @if($interaction->meeting_with == auth()->user()->user_id)
                                                        <!-- Assigned to current staff - show button only if no remarks -->
                                                        @if($interaction->remarks->count() == 0)
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-clock me-1"></i>Pending
                                                                </span>
                                                                <button class="btn btn-primary btn-sm"
                                                                        onclick="showAddRemarkModal({{ $interaction->interaction_id }}, '{{ $interaction->name_entered }}')"
                                                                        title="Add Remark">
                                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock me-1"></i>Pending
                                                            </span>
                                                        @endif
                                                    @else
                                                        <!-- Assigned to someone else - show who -->
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }} 
                                                            ({{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }})
                                                        </span>
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
                        <p class="text-muted">This visitor hasn't had any interactions yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.remark-timeline {
    max-height: 200px;
    overflow-y: auto;
}

.remark-item {
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    padding: 8px 12px;
    border-radius: 4px;
}

.remark-text {
    font-size: 0.9rem;
    margin-bottom: 4px;
    color: #333;
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}
</style>

<!-- Add Remark Modal -->
<div class="modal fade" id="addRemarkModal" tabindex="-1" aria-labelledby="addRemarkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRemarkModalLabel">
                    <i class="fas fa-comment-plus me-2"></i>Add Remark
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRemarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitorName" class="form-label">Visitor Name</label>
                        <input type="text" class="form-control" id="visitorName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="remarkText" class="form-label">Remark <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="remarkText" name="remark_text" rows="4" 
                                  placeholder="Enter your remark about this interaction..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Remark
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show Add Remark Modal
function showAddRemarkModal(interactionId, visitorName) {
    document.getElementById('visitorName').value = visitorName;
    document.getElementById('addRemarkForm').action = `/staff/update-remark/${interactionId}`;
    new bootstrap.Modal(document.getElementById('addRemarkModal')).show();
}

// Handle form submission
document.getElementById('addRemarkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = this.action;
    
    fetch(action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addRemarkModal')).hide();
            
            // Show success message
            alert('Remark added and interaction completed successfully!');
            
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add remark'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to add remark');
    });
});

// Session Completion Functions
function completeSession(sessionId) {
    // Get session details
    fetch(`{{ url('staff/session') }}/${sessionId}/modal`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate modal with session details
            document.getElementById('session_id').value = data.session.session_id;
            document.getElementById('sessionDetails').innerHTML = `
                <strong>Purpose:</strong> ${data.session.purpose}<br>
                <strong>Student:</strong> ${data.session.visitor_name}<br>
                <strong>Started:</strong> ${data.session.started_at}<br>
                <strong>Started by:</strong> ${data.session.started_by}<br>
                <strong>Interactions:</strong> ${data.session.interaction_count}
            `;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('completeSessionModal'));
            modal.show();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to load session details');
    });
}

// Handle session completion form submission
document.getElementById('completeSessionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const sessionId = document.getElementById('session_id').value;
    const formData = new FormData(this);
    
    fetch(`{{ url('staff/complete-session') }}/${sessionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session completed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('completeSessionModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to complete session');
    });
});
</script>

<!-- Session Completion Modal -->
<div class="modal fade" id="completeSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Complete Student Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeSessionForm">
                <input type="hidden" id="session_id" name="session_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Session Details:</strong>
                        <div id="sessionDetails" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="outcome" class="form-label">Session Outcome <span class="text-danger">*</span></label>
                        <select class="form-select" id="outcome" name="outcome" required>
                            <option value="">Select Outcome</option>
                            <option value="success">Success - Goal Achieved</option>
                            <option value="failed">Failed - Goal Not Achieved</option>
                            <option value="pending">Pending - Follow-up Required</option>
                        </select>
                        <div class="form-text">Select the final outcome of this student session</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="outcome_notes" class="form-label">Outcome Notes</label>
                        <textarea class="form-control" id="outcome_notes" name="outcome_notes" rows="4" 
                                  placeholder="Enter detailed notes about the session outcome..."></textarea>
                        <div class="form-text">Optional: Add detailed notes about the session result</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Complete Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection