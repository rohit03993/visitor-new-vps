@extends('layouts.app')

@section('title', 'Employee Dashboard - VMS')
@section('page-title', 'Employee Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h4">Your Assigned Visitors</h2>
        <p class="text-muted">Manage visitors assigned to you and update their remarks.</p>
    </div>
</div>

<!-- Pending Interactions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Pending Interactions ({{ $pendingInteractions->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($pendingInteractions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitor Name</th>
                                    <th>Mobile</th>
                                    <th>Purpose</th>
                                    <th>Branch</th>
                                    <th>Address</th>
                                    <th>Current Remark</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingInteractions as $interaction)
                                    <tr>
                                        <td>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at) }}</td>
                                        <td>{{ $interaction->name_entered }}</td>
                                        <td>{{ $interaction->visitor->mobile_number }}</td>
                                        <td>{{ $interaction->purpose }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->address->address_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-warning">{{ $interaction->getLatestRemark()->remark_text }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#updateRemarkModal" 
                                                    data-interaction-id="{{ $interaction->interaction_id }}"
                                                    data-visitor-name="{{ $interaction->name_entered }}">
                                                <i class="fas fa-edit me-1"></i>Update Remark
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($pendingInteractions as $interaction)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-warning">Pending</span>
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor->mobile_number }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Branch:</small><br>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Purpose -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Purpose:</small><br>
                                            <strong>{{ $interaction->purpose }}</strong>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Address:</small><br>
                                            <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <!-- Current Remark -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Current Remark:</small><br>
                                            <div class="remark-text bg-warning text-dark p-2 rounded">{{ $interaction->getLatestRemark()->remark_text }}</div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#updateRemarkModal" 
                                                data-interaction-id="{{ $interaction->interaction_id }}"
                                                data-visitor-name="{{ $interaction->name_entered }}">
                                            <i class="fas fa-edit me-1"></i>Update Remark
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">All caught up!</h5>
                        <p class="text-muted">No pending interactions to update.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Completed Interactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>Completed Interactions ({{ $completedInteractions->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($completedInteractions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitor Name</th>
                                    <th>Mobile</th>
                                    <th>Purpose</th>
                                    <th>Branch</th>
                                    <th>Address</th>
                                    <th>Final Remark</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($completedInteractions as $interaction)
                                    <tr>
                                        <td>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at) }}</td>
                                        <td>{{ $interaction->name_entered }}</td>
                                        <td>{{ $interaction->visitor->mobile_number }}</td>
                                        <td>{{ $interaction->purpose }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->address->address_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $interaction->getLatestRemark()->remark_text }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('employee.visitor-history', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-history me-1"></i>View History
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($completedInteractions as $interaction)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Completed</span>
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor->mobile_number }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Branch:</small><br>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Branch' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Purpose -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Purpose:</small><br>
                                            <strong>{{ $interaction->purpose }}</strong>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Address:</small><br>
                                            <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <!-- Final Remark -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Final Remark:</small><br>
                                            <div class="remark-text bg-success text-white p-2 rounded">{{ $interaction->getLatestRemark()->remark_text }}</div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('employee.visitor-history', $interaction->visitor->visitor_id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-history me-1"></i>View History
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No completed interactions</h5>
                        <p class="text-muted">Complete some interactions to see them here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($assignedInteractions->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                @include('components.pagination', ['paginator' => $assignedInteractions])
            </div>
        </div>
    </div>
@endif

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

/* Remark text styling is now handled globally in layouts/app.blade.php */
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
