@extends('layouts.app')

@section('title', 'Search Results - VMS')
@section('page-title', 'Search Results')

@section('content')
<!-- Mobile-Optimized Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1">Visitor History</h2>
                <small class="text-muted">Searching for: +91{{ $mobileNumber }} ({{ $interactions->count() }} interactions found)</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('employee.visitor-search') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i><span class="d-none d-sm-inline">Search Again</span>
                </a>
                <a href="{{ route('employee.visitor-form') }}?mobile={{ $mobileNumber }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add New Entry</span>
                </a>
                <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Back</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Visitor Information Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Visitor Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p><strong>Name:</strong> {{ $visitor->name }}</p>
                        <p><strong>Mobile:</strong> {{ $visitor->mobile_number }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <p><strong>Total Visits:</strong> {{ $interactions->count() }}</p>
                        <p><strong>Last Updated:</strong> {{ $visitor->updated_at ? $visitor->updated_at->format('M d, Y H:i') : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactions History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Interaction History ({{ $interactions->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 80px;">Date</th>
                                    <th style="min-width: 70px;">Time</th>
                                    <th style="min-width: 100px;">Purpose</th>
                                    <th style="min-width: 100px;">Location</th>
                                    <th style="min-width: 100px;">Created By</th>
                                    <th style="min-width: 100px;">Meeting With</th>
                                    <th style="min-width: 80px;">Status</th>
                                    <th style="min-width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->purpose }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->createdBy->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->meetingWith->name ?? 'No Data' }}</small>
                                        </td>
                                        <td>
                                            @if($interaction->hasPendingRemarks())
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if($interaction->meeting_with == auth()->user()->user_id)
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateRemarkModal" 
                                                            data-interaction-id="{{ $interaction->interaction_id }}"
                                                            data-visitor-name="{{ $interaction->name_entered }}">
                                                        <i class="fas fa-edit me-1"></i>Update
                                                    </button>
                                                @else
                                                    <span class="text-muted small">No Access</span>
                                                @endif
                                                <a href="{{ route('employee.visitor-form', ['mobile' => $visitor->mobile_number, 'name' => $visitor->name]) }}" 
                                                   class="btn btn-sm btn-success" title="Add Revisit">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($interactions as $interaction)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date, Time, and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->purpose }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                                <i class="fas fa-clock ms-2 me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->hasPendingRemarks())
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Interaction Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Location:</small><br>
                                            <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Created By:</small><br>
                                            <strong>{{ $interaction->createdBy->name ?? 'Unknown' }}</strong>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small><br>
                                            <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small><br>
                                            <span class="badge bg-info">{{ $interaction->mode }}</span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($interaction->meeting_with == auth()->user()->user_id)
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updateRemarkModal" 
                                                    data-interaction-id="{{ $interaction->interaction_id }}"
                                                    data-visitor-name="{{ $interaction->name_entered }}">
                                                <i class="fas fa-edit me-1"></i>Update Remark
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-lock me-1"></i>No Access to Update
                                            </span>
                                        @endif
                                        <a href="{{ route('employee.visitor-form', ['mobile' => $visitor->mobile_number, 'name' => $visitor->name]) }}" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-plus me-1"></i>Add Revisit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
                        <p class="text-muted">This visitor has no previous interactions.</p>
                        <a href="{{ route('employee.visitor-form') }}?mobile={{ $mobileNumber }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add First Entry
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Update Remark Modal -->
<div class="modal fade" id="updateRemarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Remark</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateRemarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitor_name" class="form-label">Visitor</label>
                        <input type="text" class="form-control" id="visitor_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="remark_text" class="form-label">New Remark *</label>
                        <textarea class="form-control" id="remark_text" name="remark_text" 
                                  rows="4" required placeholder="Enter detailed remark about the visit..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Remark</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
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
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateRemarkModal = document.getElementById('updateRemarkModal');
    const updateRemarkForm = document.getElementById('updateRemarkForm');
    
    updateRemarkModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const interactionId = button.getAttribute('data-interaction-id');
        const visitorName = button.getAttribute('data-visitor-name');
        
        document.getElementById('visitor_name').value = visitorName;
        updateRemarkForm.action = '{{ route("employee.update-remark", ":interactionId") }}'.replace(':interactionId', interactionId);
    });
});
</script>
@endsection
