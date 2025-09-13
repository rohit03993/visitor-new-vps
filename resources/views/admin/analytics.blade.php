@extends('layouts.app')

@section('title', 'Analytics - VMS')
@section('page-title', 'System Analytics')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h4">System Analytics</h2>
        <p class="text-paytm-muted">Insights and statistics about your visitor management system.</p>
    </div>
</div>

<!-- Frequent Visitors -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>Frequent Visitors
                </h5>
            </div>
            <div class="card-paytm-body">
                @if($frequentVisitors->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-paytm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Mobile Number</th>
                                    <th>Latest Name</th>
                                    <th>Total Interactions</th>
                                    <th>First Interaction</th>
                                    <th>Last Interaction</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frequentVisitors as $index => $visitor)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>{{ $visitor->mobile_number }}</td>
                                        <td>{{ $visitor->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $visitor->interactions_count }}</span>
                                        </td>
                                        <td>{{ $visitor->getFirstInteraction() ? $visitor->getFirstInteraction()->created_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $visitor->getLatestInteraction() ? $visitor->getLatestInteraction()->created_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('admin.visitor-profile', $visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View Profile
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($frequentVisitors as $index => $visitor)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Rank and Name -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $visitor->name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>
                                                {{ $visitor->mobile_number }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Visitor Stats -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Total Interactions:</small><br>
                                            <span class="badge bg-primary">{{ $visitor->interactions_count }}</span>
                                        </div>
                                    </div>

                                    <!-- Interaction Dates -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">First Interaction:</small><br>
                                            <small>{{ $visitor->getFirstInteraction() ? $visitor->getFirstInteraction()->created_at->format('M d, Y') : 'N/A' }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Last Interaction:</small><br>
                                            <small>{{ $visitor->getLatestInteraction() ? $visitor->getLatestInteraction()->created_at->format('M d, Y') : 'N/A' }}</small>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('admin.visitor-profile', $visitor->visitor_id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No frequent visitors</h5>
                        <p class="text-muted">Visitor statistics will appear here once interactions are recorded.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Top Employees -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Top Employees by Assigned Interactions
                </h5>
            </div>
            <div class="card-body">
                @if($topEmployees->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-paytm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Employee Name</th>
                                    <th>Username</th>
                                    <th>Total Assigned Interactions</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topEmployees as $index => $employee)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->username }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $employee->assigned_interactions_count }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $maxInteractions = $topEmployees->max('assigned_interactions_count');
                                                $percentage = $maxInteractions > 0 ? ($employee->assigned_interactions_count / $maxInteractions) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($topEmployees as $index => $employee)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Rank and Name -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $employee->name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $employee->username }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Employee Stats -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Total Assigned Interactions:</small><br>
                                            <span class="badge bg-primary">{{ $employee->assigned_interactions_count }}</span>
                                        </div>
                                    </div>

                                    <!-- Performance Bar -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Performance:</small><br>
                                            @php
                                                $maxInteractions = $topEmployees->max('assigned_interactions_count');
                                                $percentage = $maxInteractions > 0 ? ($employee->assigned_interactions_count / $maxInteractions) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No employee data</h5>
                        <p class="text-muted">Employee statistics will appear here once interactions are assigned.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Interactions by Purpose -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Interactions by Purpose
                </h5>
            </div>
            <div class="card-body">
                @if($interactionsByPurpose->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Purpose</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalInteractions = $interactionsByPurpose->sum('count');
                                @endphp
                                @foreach($interactionsByPurpose as $purpose)
                                    @php
                                        $percentage = $totalInteractions > 0 ? ($purpose->count / $totalInteractions) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $purpose->purpose }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $purpose->count }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 15px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @php
                            $totalInteractions = $interactionsByPurpose->sum('count');
                        @endphp
                        @foreach($interactionsByPurpose as $purpose)
                            @php
                                $percentage = $totalInteractions > 0 ? ($purpose->count / $totalInteractions) * 100 : 0;
                            @endphp
                            <div class="card mb-2 interaction-card">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ $purpose->purpose }}</h6>
                                        <span class="badge bg-primary">{{ $purpose->count }}</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $percentage }}%">
                                            {{ number_format($percentage, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No purpose data</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Interactions by Address -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Interactions by Address
                </h5>
            </div>
            <div class="card-body">
                @if($interactionsByAddress->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Address</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAddressInteractions = $interactionsByAddress->sum('interactions_count');
                                @endphp
                                @foreach($interactionsByAddress as $address)
                                    @php
                                        $percentage = $totalAddressInteractions > 0 ? ($address->interactions_count / $totalAddressInteractions) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $address->address_name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $address->interactions_count }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 15px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @php
                            $totalAddressInteractions = $interactionsByAddress->sum('interactions_count');
                        @endphp
                        @foreach($interactionsByAddress as $address)
                            @php
                                $percentage = $totalAddressInteractions > 0 ? ($address->interactions_count / $totalAddressInteractions) * 100 : 0;
                            @endphp
                            <div class="card mb-2 interaction-card">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ $address->address_name }}</h6>
                                        <span class="badge bg-info">{{ $address->interactions_count }}</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $percentage }}%">
                                            {{ number_format($percentage, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No address data</h6>
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

/* Smaller cards for analytics charts */
.interaction-card .card-body.p-3 {
    padding: 0.75rem !important;
}

.interaction-card .card-body.p-3 .card-title {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
</style>
@endsection
