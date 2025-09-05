@extends('layouts.app')

@section('title', 'Admin Dashboard - VMS')
@section('page-title', 'Admin Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number">{{ $totalVisitors }}</div>
            <div class="stats-label">Total Visitors</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number">{{ $totalInteractions }}</div>
            <div class="stats-label">Total Interactions</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number">{{ $totalUsers }}</div>
            <div class="stats-label">Total Users</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number">{{ $todayInteractions }}</div>
            <div class="stats-label">Today's Interactions</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="stats-card">
            <div class="stats-number">{{ $totalBranches }}</div>
            <div class="stats-label">Total Branches</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('admin.search-mobile') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search me-2"></i><span class="d-none d-md-inline">Search </span>Visitor
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('admin.manage-users') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-users me-2"></i><span class="d-none d-md-inline">Manage </span>Users
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('admin.manage-locations') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-map-marker-alt me-2"></i><span class="d-none d-md-inline">Manage </span>Locations
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('admin.analytics') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i><span class="d-none d-md-inline">View </span>Analytics
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="{{ route('admin.manage-branches') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-building me-2"></i><span class="d-none d-md-inline">Manage </span>Branches
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Interactions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Recent Interactions
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitor Name</th>
                                    <th>Mobile</th>
                                    <th>Mode</th>
                                    <th>Purpose</th>
                                    <th>Meeting With</th>
                                    <th>Branch</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at) }}</td>
                                        <td>{{ $interaction->name_entered }}</td>
                                        <td>{{ $interaction->visitor->mobile_number }}</td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->purpose }}</td>
                                        <td>{{ $interaction->meetingWith->name ?? 'No Data' }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->address->address_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($interaction->hasPendingRemarks())
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
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
                                            @if($interaction->hasPendingRemarks())
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

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        @include('components.pagination', ['paginator' => $interactions])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
                        <p class="text-muted">Interactions will appear here once they are created.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Branch Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>Branch Overview
                </h5>
            </div>
            <div class="card-body">
                @if($branchStats->count() > 0)
                    <div class="row">
                        @foreach($branchStats as $branch)
                            <div class="col-md-4 mb-3">
                                <div class="branch-card p-3 border rounded">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-building me-2"></i>{{ $branch->branch_name }}
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="text-success">
                                                <strong>{{ $branch->users_count }}</strong>
                                                <div class="small text-muted">Users</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-info">
                                                <strong>{{ $branch->interactions_count }}</strong>
                                                <div class="small text-muted">Interactions</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No branches found</h5>
                        <p class="text-muted">Branches will appear here once they are created.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- User Role Breakdown -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>User Role Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="role-card p-3 border rounded text-center">
                            <h6 class="text-danger mb-2">
                                <i class="fas fa-user-shield me-2"></i>Admins
                            </h6>
                            <div class="text-danger">
                                <strong>{{ $usersByRole['admin'] ?? 0 }}</strong>
                                <div class="small text-muted">Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="role-card p-3 border rounded text-center">
                            <h6 class="text-info mb-2">
                                <i class="fas fa-user-tie me-2"></i>Front Desk
                            </h6>
                            <div class="text-info">
                                <strong>{{ $usersByRole['frontdesk'] ?? 0 }}</strong>
                                <div class="small text-muted">Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="role-card p-3 border rounded text-center">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-user me-2"></i>Employees
                            </h6>
                            <div class="text-success">
                                <strong>{{ $usersByRole['employee'] ?? 0 }}</strong>
                                <div class="small text-muted">Users</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Remarks -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2"></i>Recent Remarks
                </h5>
            </div>
            <div class="card-body">
                @if($remarks->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitor</th>
                                    <th>Remark</th>
                                    <th>Added By</th>
                                    <th>Editable By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($remarks as $remark)
                                    <tr>
                                        <td>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at) }}</td>
                                        <td>{{ $remark->interaction->visitor->mobile_number }}</td>
                                        <td>
                                            <div class="remark-text bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }} text-dark p-2 rounded">
                                                {{ $remark->remark_text }}
                                            </div>
                                        </td>
                                        <td>{{ $remark->addedBy->name }}</td>
                                        <td>{{ $remark->isEditableBy->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($remarks as $remark)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">Remark</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }}">
                                                {{ $remark->remark_text == 'NA' ? 'Pending' : 'Completed' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Remark Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Remark:</small><br>
                                            <div class="remark-text bg-light text-dark p-2 rounded border">
                                                {{ $remark->remark_text }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Visitor:</small><br>
                                            <strong>{{ $remark->interaction->visitor->mobile_number }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Added By:</small><br>
                                            <strong>{{ $remark->addedBy->name }}</strong>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Editable By:</small><br>
                                            <strong>{{ $remark->isEditableBy->name }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        @include('components.pagination', ['paginator' => $remarks])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No remarks found</h5>
                        <p class="text-muted">Remarks will appear here once they are added.</p>
                    </div>
                @endif
            </div>
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
</style>
@endsection
