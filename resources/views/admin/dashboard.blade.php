@extends('layouts.app')

@section('title', 'Admin Dashboard - VMS')
@section('page-title', 'Admin Dashboard')

@section('content')
<!-- Key Metrics Cards -->
<div class="row mb-4">
    <div class="col-6 col-lg-3 mb-3">
        <a href="{{ route('admin.all-visitors') }}" class="text-decoration-none">
            <div class="metric-card stats-card-paytm clickable-card">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-number">{{ $totalVisitors }}</div>
                    <div class="metric-label">Total Visitors</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <a href="{{ route('admin.all-interactions') }}" class="text-decoration-none">
            <div class="metric-card stats-card-paytm clickable-card" style="background: linear-gradient(135deg, var(--paytm-success) 0%, #1e7e34 100%);">
                <div class="metric-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-number">{{ $totalInteractions }}</div>
                    <div class="metric-label">Total Interactions</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <a href="{{ route('admin.manage-users') }}" class="text-decoration-none">
            <div class="metric-card stats-card-paytm clickable-card" style="background: linear-gradient(135deg, var(--paytm-info) 0%, #138496 100%);">
                <div class="metric-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-number">{{ $totalUsers }}</div>
                    <div class="metric-label">Total Users</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <a href="{{ route('admin.today-interactions') }}" class="text-decoration-none">
            <div class="metric-card stats-card-paytm clickable-card" style="background: linear-gradient(135deg, var(--paytm-warning) 0%, #e0a800 100%);">
                <div class="metric-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-number">{{ $todayInteractions }}</div>
                    <div class="metric-label">Today's Interactions</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Quick Actions & Today's Activity -->
<div class="row mb-4">
    <!-- Quick Actions -->
    <div class="col-12 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('admin.search-mobile') }}" class="action-btn btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Search Visitor</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.manage-users') }}" class="action-btn btn-success">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.manage-locations') }}" class="action-btn btn-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Manage Locations</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.analytics') }}" class="action-btn btn-warning">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Analytics</span>
                        </a>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('admin.manage-branches') }}" class="action-btn btn-secondary">
                            <i class="fas fa-building"></i>
                            <span>Manage Branches</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Activity Summary -->
    <div class="col-12 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i>Today's Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="activity-summary">
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-number">{{ $todayInteractions }}</div>
                            <div class="activity-label">Interactions Today</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-number">{{ $interactions->filter(function($interaction) { return !$interaction->is_completed; })->count() }}</div>
                            <div class="activity-label">Pending Interactions</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-number">{{ $interactions->filter(function($interaction) { return $interaction->is_completed; })->count() }}</div>
                            <div class="activity-label">Completed Today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Visitor</th>
                                    <th>Purpose</th>
                                    <th>Meeting With</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions->take(10) as $interaction)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $interaction->name_entered }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }} mb-1">
                                                {{ $interaction->mode }}
                                            </span>
                                            <br>
                                            <small>{{ $interaction->purpose }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
                                                <br>
                                                <span class="badge bg-info">
                                                    {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($interaction->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
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
                    <div class="d-lg-none">
                        @foreach($interactions->take(10) as $interaction)
                            <div class="card mb-3 activity-card">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor->mobile_number }}</strong>
                                        </div>
                                    </div>

                                    <!-- Purpose and Mode -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small><br>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Purpose:</small><br>
                                            <strong>{{ $interaction->purpose }}</strong>
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Meeting With:</small><br>
                                            <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
                                            <br>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-primary w-100">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No activity found</h5>
                        <p class="text-muted">No interactions have been recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Metric Cards */
.metric-card {
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 120px;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.metric-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.metric-content {
    flex: 1;
}

.metric-number {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
}

/* Action Buttons */
.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    border-radius: 10px;
    text-decoration: none;
    color: white;
    transition: all 0.3s ease;
    min-height: 80px;
    border: none;
}

.action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.action-btn span {
    font-size: 0.9rem;
    font-weight: 500;
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    color: white;
}

.action-btn.btn-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
.action-btn.btn-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
.action-btn.btn-info { background: linear-gradient(135deg, #17a2b8, #117a8b); }
.action-btn.btn-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
.action-btn.btn-secondary { background: linear-gradient(135deg, #6c757d, #545b62); }

/* Activity Summary */
.activity-summary {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.activity-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1.2rem;
}

.activity-content {
    flex: 1;
}

.activity-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #495057;
    line-height: 1;
}

.activity-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Activity Cards */
.activity-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.activity-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.activity-card .card-body {
    padding: 1rem;
}

.activity-card .card-title {
    color: #495057;
    font-weight: 600;
    font-size: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .metric-card {
        min-height: 100px;
        padding: 1rem;
    }
    
    .metric-icon {
        font-size: 2rem;
        margin-right: 0.75rem;
    }
    
    .metric-number {
        font-size: 1.5rem;
    }
    
    .activity-summary {
        gap: 0.75rem;
    }
    
    .activity-item {
        padding: 0.75rem;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .activity-number {
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .metric-card {
        flex-direction: column;
        text-align: center;
        min-height: 120px;
    }
    
    .metric-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .action-btn {
        min-height: 70px;
        padding: 0.75rem;
    }
    
    .action-btn i {
        font-size: 1.25rem;
    }
    
    .action-btn span {
        font-size: 0.8rem;
    }
}
</style>
@endsection

@section('styles')
<style>
/* Clickable Cards */
.clickable-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.clickable-card:active {
    transform: translateY(-2px);
}
</style>
@endsection