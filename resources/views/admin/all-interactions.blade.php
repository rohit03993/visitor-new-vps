@extends('layouts.app')

@section('title', 'All Interactions - VMS')
@section('page-title', 'All Interactions')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <div>
                <small class="text-paytm-muted">Total {{ $interactions->total() }} interactions found</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-paytm-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-handshake me-2"></i>All Interactions
                </h5>
            </div>
            <div class="card-paytm-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-paytm">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Visitor</th>
                                    <th>Mode</th>
                                    <th>Purpose</th>
                                    <th>Meeting With</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
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
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->purpose }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <small>{{ $interaction->meetingWith->name ?? 'No Data' }}</small>
                                                <br>
                                                <span class="badge bg-info">
                                                    {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
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
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Visitor:</small>
                                            <div><strong>{{ $interaction->name_entered }}</strong></div>
                                            <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small>
                                            <div>
                                                <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                    {{ $interaction->mode }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Purpose:</small>
                                            <div>{{ $interaction->purpose }}</div>
                                        </div>
                                    </div>

                                    <!-- Meeting With and Address -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small>
                                            <div>{{ $interaction->meetingWith->name ?? 'No Data' }}</div>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Address:</small>
                                            <div>{{ $interaction->address->address_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-eye me-1"></i>View Profile
                                            </a>
                                        </div>
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
                        <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
                        <p class="text-muted">No interactions have been recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
</style>
@endsection
