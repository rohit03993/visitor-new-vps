@extends('layouts.app')

@section('title', 'Assigned to Me - Log Book')
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
                        <button onclick="refreshAssignedList()" class="btn btn-outline-success btn-sm" title="Refresh list">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-paytm-body">
                @if($assignedInteractions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Student Name</th>
                                    <th>Purpose</th>
                                    <th>Notes Before Meeting</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedInteractions as $interaction)
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
                                                <strong>{{ $interaction->visitor ? $interaction->visitor->student_name : $interaction->name_entered }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $interaction->visitor ? $interaction->visitor->mobile_number : 'No mobile number' }}</small>
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
                                                @if($interaction->initial_notes && $interaction->initial_notes !== 'NA')
                                                    <span class="text-muted">{{ Str::limit($interaction->initial_notes, 100) }}</span>
                                            @else
                                                    <span class="text-muted fst-italic">No notes added</span>
                                            @endif
                                            </div>
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
                                                    @php
                                                        // Check if interaction has actual work remarks (not just transfer/scheduled remarks)
                                                        $hasWorkRemark = false;
                                                        foreach($interaction->remarks as $remark) {
                                                            if (strpos($remark->remark_text, 'Transferred from') === false && 
                                                                strpos($remark->remark_text, '📅 Scheduled Assignment from') === false) {
                                                                $hasWorkRemark = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    @if($hasWorkRemark)
                                                    <span class="badge bg-info">Remark Updated</span>
                                                    @else
                                                        <span class="badge bg-warning">Remark Pending</span>
                                                    @endif
                                                @endif
                                            @else
                                                <span class="badge bg-warning">Remark Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($interaction->visitor)
                                            <a href="{{ route('staff.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Profile">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                                @else
                                            <span class="text-muted">No visitor</span>
                                                @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-lg-none">
                        @foreach($assignedInteractions as $interaction)
                            <div class="card mb-3 activity-card">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->visitor ? $interaction->visitor->student_name : $interaction->name_entered }}</h6>
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
                                            @if($interaction->remarks->count() > 0)
                                                @php
                                                    $hasOnlyTransferRemark = $interaction->remarks->count() === 1 && 
                                                        (strpos($interaction->remarks->first()->remark_text, '🔄 **Transferred from') !== false ||
                                                         strpos($interaction->remarks->first()->remark_text, 'Transferred from') !== false ||
                                                         strpos($interaction->remarks->first()->remark_text, '📅 Scheduled Assignment from') !== false);
                                                @endphp
                                                
                                                @if($hasOnlyTransferRemark)
                                                    <span class="badge bg-warning">Remark Pending</span>
                                                @elseif($interaction->is_completed)
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
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor ? $interaction->visitor->mobile_number : 'No mobile number' }}</strong>
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

                                    <!-- Notes Before Meeting -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Notes Before Meeting:</small><br>
                                            @if($interaction->initial_notes && $interaction->initial_notes !== 'NA')
                                                <span class="text-dark">{{ Str::limit($interaction->initial_notes, 150) }}</span>
                                            @else
                                                <span class="text-muted fst-italic">No notes added</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="row">
                                        <div class="col-12">
                                            @if($interaction->visitor)
                                            <a href="{{ route('staff.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-primary w-100"
                                               title="View Profile">
                                                <i class="fas fa-eye me-1"></i>View Profile
                                            </a>
                                        @else
                                            <span class="btn btn-secondary w-100 disabled">No visitor data</span>
                                        @endif
                                        </div>
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

/* Activity Cards (matching admin style) */
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

.activity-card .badge {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
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
// No JavaScript needed for this simple table view
// The View button will navigate to the visitor profile page
</script>
@endsection
