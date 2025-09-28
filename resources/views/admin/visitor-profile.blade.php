@extends('layouts.app')

@section('title', 'Visitor Profile - Task Book')
@section('page-title', 'Visitor Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div></div>
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="{{ route('staff.visitor-form', ['mobile' => $visitor->mobile_number, 'name' => $visitor->name]) }}" 
                   class="btn btn-paytm-success">
                    <i class="fas fa-plus me-2"></i>Add Interaction
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-paytm-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Profile Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-paytm-body">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Mobile Number:</strong></td>
                                <td>{{ $visitor->mobile_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact Person:</strong></td>
                                <td>{{ $visitor->name }}</td>
                            </tr>
                            @if($visitor->student_name)
                            <tr>
                                <td><strong>Student Name:</strong></td>
                                <td>{{ $visitor->student_name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <table class="table table-borderless">
                            @if($visitor->course)
                            <tr>
                                <td><strong>Course:</strong></td>
                                <td><span class="badge bg-info">{{ $visitor->course->course_name }}</span></td>
                            </tr>
                            @endif
                            @if($visitor->father_name)
                            <tr>
                                <td><strong>Father's Name:</strong></td>
                                <td>{{ $visitor->father_name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Total Interactions:</strong></td>
                                <td><span class="badge-paytm-primary">{{ $interactions->count() }}</span></td>
                            </tr>
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
                    <i class="fas fa-history me-2"></i>Complete Interaction History
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
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
                                                            <small class="text-muted">
                                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, g:i A') }}
                                                                @if($remark->meeting_duration)
                                                                    • {{ $remark->meeting_duration }} mins
                                                                @endif
                                                            </small>
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
                                                @if($interaction->is_completed)
                                                    <span class="text-muted">No remarks</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            @endif
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
                                    <!-- Header with Interaction ID and Date -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">Interaction #{{ $interaction->interaction_id }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Interaction Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Name Entered:</small><br>
                                            <strong>{{ $interaction->name_entered }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small><br>
                                            <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
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
                                    <div class="row">
                                        <div class="col-12">
                                            <small class="text-muted">Remark(s):</small><br>
                                            @if($interaction->remarks->count() > 0)
                                                <div class="remark-timeline">
                                                    @foreach($interaction->remarks as $remark)
                                                        <div class="remark-item mb-2 p-2 border rounded">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-clock me-1"></i>
                                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, g:i A') }}
                                                                @if($remark->meeting_duration)
                                                                    • <i class="fas fa-stopwatch me-1"></i>{{ $remark->meeting_duration }} mins
                                                                @endif
                                                            </small>
                                                            <div class="remark-text bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }} {{ $remark->remark_text == 'NA' ? 'text-dark' : 'text-white' }} p-2 rounded mb-1">
                                                                {{ $remark->remark_text }}
                                                            </div>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-user me-1"></i>
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
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
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
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                                 <h5 class="text-muted">No interaction history</h5>
                        <p class="text-muted">This visitor has no recorded interactions.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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

.interaction-card .remark-item {
    background: #f8f9fa;
    border-radius: 6px;
}

@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    .card-header {
        background: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>
@endsection
