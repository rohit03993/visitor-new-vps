@extends('layouts.app')

@section('title', 'Staff Dashboard - Log Book')
@section('page-title', 'Staff Dashboard')

@section('content')
<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <p class="text-paytm-muted mb-0">Manage your assigned visitors and view all visitor data.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary btn-sm">
                    <i class="fas fa-search me-1"></i><span class="d-none d-sm-inline">Search Visitor</span>
                </a>
                <a href="{{ route('staff.visitor-form') }}" class="btn btn-paytm-primary btn-sm">
                    <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Visitor</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills nav-paytm" id="staffTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-paytm-link active" id="all-visitors-tab" data-bs-toggle="tab" data-bs-target="#all-visitors" type="button" role="tab" aria-controls="all-visitors" aria-selected="true">
                    <i class="fas fa-users me-2"></i>All Visitors
                    <span class="badge-paytm-primary ms-2">{{ $allInteractions->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-paytm-link" id="assigned-to-me-tab" data-bs-toggle="tab" data-bs-target="#assigned-to-me" type="button" role="tab" aria-controls="assigned-to-me" aria-selected="false">
                    <i class="fas fa-user-check me-2"></i>Assigned to Me
                    <span class="badge-paytm-success ms-2">{{ $assignedInteractions->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-paytm-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="false">
                    <i class="fas fa-chart-bar me-2"></i>Analytics
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="staffTabsContent">
    <!-- All Visitors Tab -->
    <div class="tab-pane fade show active" id="all-visitors" role="tabpanel" aria-labelledby="all-visitors-tab">
        <div class="row">
            <div class="col-12">
                <div class="card-paytm paytm-fade-in">
                    <div class="card-paytm-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>All Visitors
                            <span class="badge-paytm-primary ms-2">{{ $allInteractions->total() }}</span>
                        </h5>
                    </div>
                    <div class="card-paytm-body p-0">
                        @if($allInteractions->count() > 0)
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-paytm table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 80px;">Date</th>
                                            <th style="min-width: 70px;">Time</th>
                                            <th style="min-width: 120px;">Visitor</th>
                                            <th style="min-width: 100px;">Mobile</th>
                                            <th style="min-width: 80px;">Mode</th>
                                            <th style="min-width: 100px;">Purpose</th>
                                            <th style="min-width: 100px;">Meeting With</th>
                                            <th style="min-width: 100px;">Branch</th>
                                            <th style="min-width: 100px;">Address</th>
                                            <th style="min-width: 80px;">Status</th>
                                            <th style="min-width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allInteractions as $interaction)
                                            <tr>
                                                <td>
                                                    <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-medium">{{ $interaction->name_entered }}</div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge-paytm-{{ $interaction->getModeBadgeColor() }}">
                                                        {{ $interaction->mode }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $interaction->purpose }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $interaction->meetingWith->name ?? 'No Data' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge-paytm-info">
                                                        {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    @if($interaction->is_completed)
                                                        <span class="badge-paytm-warning">Pending</span>
                                                    @else
                                                        <span class="badge-paytm-success">Done</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                                            <button class="btn btn-sm btn-paytm-secondary" 
                                                                    onclick="viewRemarks({{ $interaction->interaction_id }})"
                                                                    title="View Remarks">
                                                                <i class="fas fa-comments me-1"></i>View
                                                            </button>
                                                        @else
                                                            <span class="text-paytm-muted small">No Access</span>
                                                        @endif
                                                        <a href="{{ route('staff.visitor-form', ['mobile' => $interaction->visitor->original_mobile_number, 'name' => $interaction->name_entered]) }}" 
                                                           class="btn btn-sm btn-paytm-success" title="Add Interaction">
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
                                @foreach($allInteractions as $interaction)
                                    <div class="card-paytm mb-3 interaction-card paytm-slide-up">
                                        <div class="card-paytm-body">
                                            <!-- Header with Date, Time, and Status -->
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                                    <small class="text-paytm-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                                        <i class="fas fa-clock ms-2 me-1"></i>
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    @if($interaction->is_completed)
                                                        <span class="badge bg-warning">Pending</span>
                                                    @else
                                                        <span class="badge bg-success">Completed</span>
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
                                                    <small class="text-muted">Meeting With:</small><br>
                                                    <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Branch:</small><br>
                                                    <span class="badge bg-info">
                                                        {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <small class="text-muted">Address:</small><br>
                                                    <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-end gap-2">
                                                @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="viewRemarks({{ $interaction->interaction_id }})">
                                                        <i class="fas fa-comments me-1"></i>View Remarks
                                                    </button>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="fas fa-lock me-1"></i>No Access to Remarks
                                                    </span>
                                                @endif
                                                <a href="{{ route('staff.visitor-form', ['mobile' => $interaction->visitor->original_mobile_number, 'name' => $interaction->name_entered]) }}" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-plus me-1"></i>Add Interaction
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            <div class="mt-3">
                                @include('components.pagination', ['paginator' => $allInteractions])
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No visitors found</h5>
                                <p class="text-muted">No visitors found in your permitted branches.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned to Me Tab -->
    <div class="tab-pane fade" id="assigned-to-me" role="tabpanel" aria-labelledby="assigned-to-me-tab">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check me-2"></i>Assigned to Me
                            <span class="badge bg-success ms-2">{{ $assignedInteractions->total() }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($assignedInteractions->count() > 0)
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 80px;">Date</th>
                                            <th style="min-width: 70px;">Time</th>
                                            <th style="min-width: 120px;">Visitor</th>
                                            <th style="min-width: 100px;">Mobile</th>
                                            <th style="min-width: 80px;">Mode</th>
                                            <th style="min-width: 100px;">Purpose</th>
                                            <th style="min-width: 100px;">Branch</th>
                                            <th style="min-width: 100px;">Address</th>
                                            <th style="min-width: 80px;">Status</th>
                                            <th style="min-width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignedInteractions as $interaction)
                                            <tr>
                                                <td>
                                                    <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-medium">{{ $interaction->name_entered }}</div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge-paytm-{{ $interaction->getModeBadgeColor() }}">
                                                        {{ $interaction->mode }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $interaction->purpose }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge-paytm-info">
                                                        {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    @if($interaction->is_completed)
                                                        <span class="badge-paytm-warning">Pending</span>
                                                    @else
                                                        <span class="badge-paytm-success">Done</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateRemarkModal" 
                                                                data-interaction-id="{{ $interaction->interaction_id }}"
                                                                data-visitor-name="{{ $interaction->name_entered }}">
                                                            <i class="fas fa-edit me-1"></i>Update
                                                        </button>
                                                        @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                                            <button class="btn btn-sm btn-outline-info" 
                                                                    onclick="viewRemarks({{ $interaction->interaction_id }})"
                                                                    title="View Remarks">
                                                                <i class="fas fa-comments"></i>
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
                                                    <small class="text-paytm-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                                        <i class="fas fa-clock ms-2 me-1"></i>
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    @if($interaction->is_completed)
                                                        <span class="badge bg-warning">Pending</span>
                                                    @else
                                                        <span class="badge bg-success">Completed</span>
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

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateRemarkModal" 
                                                        data-interaction-id="{{ $interaction->interaction_id }}"
                                                        data-visitor-name="{{ $interaction->name_entered }}">
                                                    <i class="fas fa-edit me-1"></i>Update Remark
                                                </button>
                                                @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="viewRemarks({{ $interaction->interaction_id }})">
                                                        <i class="fas fa-comments me-1"></i>View Remarks
                                                    </button>
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
    </div>

    <!-- Analytics Tab -->
    <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Analytics & Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Quick Stats -->
                            <div class="col-md-3 mb-4">
                                <div class="stats-card-paytm paytm-bounce">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                            <div class="stats-number">{{ $allInteractions->total() }}</div>
                                            <div class="stats-label">Total Visitors</div>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="stats-card-paytm paytm-bounce" style="background: linear-gradient(135deg, var(--paytm-success) 0%, #1e7e34 100%);">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                            <div class="stats-number">{{ $assignedInteractions->total() }}</div>
                                            <div class="stats-label">My Assigned</div>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-user-check fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="stats-card-paytm paytm-bounce" style="background: linear-gradient(135deg, var(--paytm-warning) 0%, #e0a800 100%);">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                            <div class="stats-number">{{ $assignedInteractions->where('created_at', '>=', now()->startOfDay())->count() }}</div>
                                            <div class="stats-label">Today's Visits</div>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-calendar-day fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="stats-card-paytm paytm-bounce" style="background: linear-gradient(135deg, var(--paytm-info) 0%, #138496 100%);">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                            <div class="stats-number">{{ $assignedInteractions->where('mode', 'In-Campus')->count() }}</div>
                                            <div class="stats-label">In-Campus</div>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-building fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Recent Activity</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            @foreach($assignedInteractions->take(5) as $interaction)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $interaction->name_entered }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $interaction->purpose }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</small>
                                                        <br>
                                                        <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">{{ $interaction->mode }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Remark Modal -->
<div class="modal fade modal-paytm" id="updateRemarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Remark</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateRemarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitorName" class="form-label-paytm">Visitor:</label>
                        <input type="text" class="form-control-paytm" id="visitorName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="remark_text" class="form-label-paytm">Remark *</label>
                        <textarea class="form-control-paytm" id="remark_text" name="remark_text" rows="4" required maxlength="1000" placeholder="Enter your remark..."></textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-paytm-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-paytm-primary">Update Remark</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remarks Modal -->
<div class="modal fade modal-paytm" id="remarksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Interaction Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="remarksContent">
                <!-- Remarks will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-paytm-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Mobile Card Styles - Enhanced with Paytm Theme */
.interaction-card {
    border: 1px solid var(--paytm-border-light);
    border-radius: 12px;
    box-shadow: 0 2px 8px var(--paytm-shadow-light);
    transition: all 0.3s ease;
    background: var(--paytm-white);
}

.interaction-card:hover {
    box-shadow: 0 4px 16px var(--paytm-shadow-medium);
    transform: translateY(-2px);
    border-color: var(--paytm-primary);
}

.interaction-card .card-paytm-body {
    padding: 1rem;
}

.interaction-card .card-title {
    color: var(--paytm-text-primary);
    font-weight: 600;
    font-size: 1rem;
}

.interaction-card .badge-paytm-primary,
.interaction-card .badge-paytm-success,
.interaction-card .badge-paytm-warning,
.interaction-card .badge-paytm-info {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.interaction-card .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 8px;
}

/* Enhanced Tab Styles with Paytm Theme */
.nav-paytm {
    background: var(--paytm-white);
    border-radius: 12px;
    padding: 0.5rem;
    box-shadow: 0 2px 8px var(--paytm-shadow-light);
}

.nav-paytm-link {
    border: none;
    border-radius: 8px;
    color: var(--paytm-text-secondary);
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-paytm-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.nav-paytm-link:hover::before {
    left: 100%;
}

.nav-paytm-link.active {
    color: var(--paytm-white);
    background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
    box-shadow: 0 2px 8px var(--paytm-primary-shadow);
}

.nav-paytm-link:hover:not(.active) {
    color: var(--paytm-primary);
    background: rgba(57, 116, 252, 0.1);
    transform: translateY(-1px);
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
// Update Remark Modal
document.getElementById('updateRemarkModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const interactionId = button.getAttribute('data-interaction-id');
    const visitorName = button.getAttribute('data-visitor-name');
    
    const modal = this;
    modal.querySelector('#visitorName').value = visitorName;
    modal.querySelector('#updateRemarkForm').action = `/staff/update-remark/${interactionId}`;
});

// View Remarks
function viewRemarks(interactionId) {
    fetch(`/staff/interactions/${interactionId}/remarks`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRemarks(data.remarks, data.interaction);
            } else {
                alert('Error loading remarks: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading remarks');
        });
}

function displayRemarks(remarks, interaction) {
    const content = document.getElementById('remarksContent');
    
    let html = `
        <div class="mb-3">
            <h6>Interaction Details:</h6>
            <p><strong>Visitor:</strong> ${interaction.visitor_name}</p>
            <p><strong>Purpose:</strong> ${interaction.purpose}</p>
            <p><strong>Meeting With:</strong> ${interaction.meeting_with}</p>
            <p><strong>Date:</strong> ${interaction.date}</p>
        </div>
        <hr>
        <h6>Remarks:</h6>
    `;
    
    if (remarks.length > 0) {
        remarks.forEach(remark => {
            html += `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1">${remark.remark_text}</p>
                                <small class="text-muted">Added by: ${remark.added_by_name}</small>
                            </div>
                            <small class="text-muted">${remark.created_at}</small>
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        html += '<p class="text-muted">No remarks available.</p>';
    }
    
    content.innerHTML = html;
    new bootstrap.Modal(document.getElementById('remarksModal')).show();
}
</script>
@endsection