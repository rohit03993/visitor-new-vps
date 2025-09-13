@extends('layouts.app')

@section('title', 'Assigned to Me - VMS')
@section('page-title', 'Assigned to Me')

@section('content')
<!-- My Tasks -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-tasks me-2"></i>My Tasks
                        </h5>
                        <small class="opacity-75">Visitors assigned to you for follow-up</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <div class="h4 mb-0">{{ $assignedInteractions->total() }}</div>
                            <small class="opacity-75">Pending</small>
                        </div>
                        <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary btn-sm">
                            <i class="fas fa-search me-1"></i>Search
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-paytm-body p-0">
                @if($assignedInteractions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-paytm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 200px;">Visitor & Details</th>
                                    <th style="min-width: 120px;">Purpose & Mode</th>
                                    <th style="min-width: 100px;">Notes</th>
                                    <th style="min-width: 80px;">Status</th>
                                    <th style="min-width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedInteractions as $interaction)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle-sm" style="background: var(--paytm-primary); color: white;" class="me-3">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $interaction->name_entered }}</div>
                                                    <small class="text-paytm-muted">{{ $interaction->visitor->mobile_number }}</small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'M d, Y g:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <span class="badge bg-primary">{{ $interaction->purpose }}</span>
                                            </div>
                                            <div>
                                                <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                    <i class="fas fa-{{ $interaction->mode === 'In-Campus' ? 'building' : 'phone' }} me-1"></i>
                                                    {{ $interaction->mode }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($interaction->initial_notes)
                                                <div class="text-truncate" style="max-width: 150px;" title="{{ $interaction->initial_notes }}">
                                                    {{ $interaction->initial_notes }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($interaction->remarks->count() > 0)
                                            @if($interaction->is_completed)
                                                    @php
                                                        $latestRemark = $interaction->remarks->last();
                                                        $outcome = $latestRemark->outcome ?? 'in_process';
                                                    @endphp
                                                    @if($outcome === 'closed_positive')
                                                        <span class="badge bg-success">Closed (Positive)</span>
                                                    @elseif($outcome === 'closed_negative')
                                                        <span class="badge bg-danger">Closed (Negative)</span>
                                                    @else
                                                        <span class="badge bg-info">Remark Updated</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-info">Remark Updated</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">Remark Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                @if($interaction->remarks->count() > 0)
                                                    <div class="remark-timeline">
                                                        @foreach($interaction->remarks as $remark)
                                                            <div class="remark-item mb-1">
                                                                <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, g:i A') }}</small>
                                                                <div class="remark-text bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }} {{ $remark->remark_text == 'NA' ? 'text-dark' : 'text-white' }} p-2 rounded">
                                                                    {{ $remark->remark_text }}
                                                                </div>
                                                                <small class="text-muted d-block">
                                                                    by {{ $remark->addedBy?->name ?? 'Unknown' }} <strong>({{ $remark->addedBy?->branch?->branch_name ?? 'No Branch' }})</strong>
                                                                </small>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No remarks</span>
                                                @endif
                                                
                                                <!-- Add Remark Button for Pending Interactions -->
                                                @if($interaction->remarks->count() == 0)
                                                    <button class="btn btn-primary btn-sm" 
                                                            onclick="showAddRemarkModal({{ $interaction->interaction_id }}, '{{ $interaction->name_entered }}')"
                                                            title="Add Remark">
                                                        <i class="fas fa-plus me-1"></i>Add Remark
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($assignedInteractions as $interaction)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date, Time, and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                                <i class="fas fa-clock ms-2 me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->remarks->count() > 0)
                                                <span class="badge bg-info">Remark Updated</span>
                                            @else
                                                <span class="badge bg-warning">Remark Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor->mobile_number }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small><br>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Purpose:</small><br>
                                            <strong>{{ $interaction->purpose }}</strong>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Branch:</small><br>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Address:</small><br>
                                            <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <!-- Initial Notes -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Initial Notes:</small><br>
                                            @if($interaction->initial_notes)
                                                <small class="text-muted">{{ $interaction->initial_notes }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Remarks Section -->
                                    <div class="mt-3">
                                        <h6 class="text-muted mb-2">Remarks:</h6>
                                        @if($interaction->remarks->count() > 0)
                                            <div class="remark-timeline">
                                                @foreach($interaction->remarks as $remark)
                                                    <div class="remark-item mb-2">
                                                        <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, g:i A') }}</small>
                                                        <div class="remark-text bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }} {{ $remark->remark_text == 'NA' ? 'text-dark' : 'text-white' }} p-2 rounded">
                                                            {{ $remark->remark_text }}
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            by {{ $remark->addedBy?->name ?? 'Unknown' }} <strong>({{ $remark->addedBy?->branch?->branch_name ?? 'No Branch' }})</strong>
                                                        </small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">No remarks available</span>
                                        @endif
                                        
                                        <!-- Add Remark Button for Pending Interactions -->
                                        @if($interaction->remarks->count() == 0)
                                            <div class="mt-3">
                                                <button class="btn btn-primary btn-sm w-100" 
                                                        onclick="showAddRemarkModal({{ $interaction->interaction_id }}, '{{ $interaction->name_entered }}')"
                                                        title="Add Remark">
                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        @include('components.pagination', ['paginator' => $assignedInteractions])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No assigned visitors</h5>
                        <p class="text-muted">You don't have any visitors assigned to you yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Remark Modal -->
<div class="modal fade" id="addRemarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Remark</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRemarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitorName" class="form-label">Visitor:</label>
                        <input type="text" class="form-control" id="visitorName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarkText" class="form-label">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="remarkText" name="remark_text" rows="3" required placeholder="Enter your remark/note about the meeting..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Remark</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* Avatar Styles */
.avatar-circle-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* Mobile Card Styles */
.interaction-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.interaction-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.interaction-card .card-body {
    padding: 1rem;
}

.interaction-card .card-title {
    color: #495057;
    font-weight: 600;
    font-size: 1rem;
}

.interaction-card .badge {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.interaction-card .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

/* Mobile-optimized table styles (Desktop only) */
@media (max-width: 768px) {
    .table-responsive {
        border: none;
        box-shadow: none;
    }
    
    .table {
        font-size: 0.8rem;
        margin-bottom: 0;
    }
    
    .table th,
    .table td {
        padding: 0.4rem 0.3rem;
        vertical-align: middle;
    }
    
    .badge-sm {
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
}

/* Better spacing for mobile */
@media (max-width: 576px) {
    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
    
    .btn {
        font-size: 0.8rem;
    }
    
    h2.h4 {
        font-size: 1.2rem;
    }
}

/* Remove horizontal scroll indicator for mobile cards */
@media (max-width: 767px) {
    .table-responsive::after {
        display: none !important;
    }
}

/* Remark Timeline Styles */
.remark-timeline {
    max-width: 100%;
}

.remark-item {
    margin-bottom: 0.5rem;
}

.remark-text {
    font-size: 0.875rem;
    margin: 0.25rem 0;
    word-wrap: break-word;
    max-width: 100%;
}

.remark-text.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

.remark-text.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Mobile remark styles */
@media (max-width: 768px) {
    .remark-text {
        font-size: 0.8rem;
        padding: 0.5rem !important;
    }
    
    .remark-item {
        margin-bottom: 0.75rem;
    }
}
</style>
@endsection

@section('scripts')
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('addRemarkModal')).hide();
            window.location.reload();
        } else {
            alert('Error adding remark. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding remark. Please try again.');
    });
});
</script>
@endsection
