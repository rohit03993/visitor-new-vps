@extends('layouts.app')

@section('title', 'Advanced Search Results - Log Book')
@section('page-title', 'Advanced Search Results')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Search Summary -->
        <div class="card-paytm paytm-fade-in mb-4">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>Search Results
                </h5>
                <div class="ms-auto">
                    <span class="badge badge-paytm-enhanced badge-paytm-info">
                        {{ $visitors->total() }} {{ Str::plural('result', $visitors->total()) }} found
                    </span>
                </div>
            </div>
            <div class="card-paytm-body">
                <!-- Search Criteria Summary -->
                <div class="search-criteria-summary">
                    <h6 class="text-muted mb-2">Search Criteria:</h6>
                    <div class="row">
                        @if($studentName)
                            <div class="col-auto mb-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-user-graduate me-1"></i>Student: {{ $studentName }}
                                </span>
                            </div>
                        @endif
                        @if($fatherName)
                            <div class="col-auto mb-2">
                                <span class="badge bg-info">
                                    <i class="fas fa-user-tie me-1"></i>Father: {{ $fatherName }}
                                </span>
                            </div>
                        @endif
                        @if($contactPerson)
                            <div class="col-auto mb-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-user me-1"></i>Contact: {{ $contactPerson }}
                                </span>
                            </div>
                        @endif
                        @if($purpose)
                            @php
                                $purposeTag = $tags->find($purpose);
                            @endphp
                            @if($purposeTag)
                                <div class="col-auto mb-2">
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-bullseye me-1"></i>Purpose: {{ $purposeTag->name }}
                                    </span>
                                </div>
                            @endif
                        @endif
                        @if($courseId)
                            @php
                                $course = $courses->find($courseId);
                            @endphp
                            @if($course)
                                <div class="col-auto mb-2">
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-book me-1"></i>Course: {{ $course->course_name }}
                                    </span>
                                </div>
                            @endif
                        @endif
                        @if($dateFrom || $dateTo)
                            <div class="col-auto mb-2">
                                <span class="badge bg-dark">
                                    <i class="fas fa-calendar me-1"></i>
                                    @if($dateFrom && $dateTo)
                                        {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                                    @elseif($dateFrom)
                                        From: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                                    @else
                                        Until: {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- New Search Button -->
                    <div class="mt-3">
                        <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary btn-sm">
                            <i class="fas fa-search me-2"></i>New Search
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        @if($visitors->count() > 0)
            <div class="card-paytm paytm-fade-in">
                <div class="card-paytm-body p-0">
                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover mb-0 search-results-table">
                            <thead class="table-header-paytm">
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Contact Person</th>
                                    <th><i class="fas fa-user-graduate me-2"></i>Student</th>
                                    <th><i class="fas fa-user-tie me-2"></i>Father</th>
                                    <th><i class="fas fa-book me-2"></i>Course</th>
                                    <th><i class="fas fa-comments me-2"></i>Interactions</th>
                                    <th><i class="fas fa-clock me-2"></i>Last Visit</th>
                                    <th><i class="fas fa-cog me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visitors as $visitor)
                                    @php
                                        $interactionCount = $visitor->interactions->count();
                                        $latestInteraction = $visitor->interactions->first();
                                        
                                        // Determine interaction badge color
                                        $interactionBadgeClass = 'badge-secondary';
                                        if ($interactionCount >= 3) {
                                            $interactionBadgeClass = 'badge-success';
                                        } elseif ($interactionCount >= 1) {
                                            $interactionBadgeClass = 'badge-warning';
                                        } else {
                                            $interactionBadgeClass = 'badge-danger';
                                        }
                                    @endphp
                                    <tr class="search-result-row">
                                        <td>
                                            <div class="visitor-name">
                                                <strong>{{ $visitor->name }}</strong>
                                                <small class="d-block text-muted">
                                                    <i class="fas fa-mobile-alt me-1"></i>{{ $visitor->mobile_number }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($visitor->student_name)
                                                <span class="text-primary fw-bold">{{ $visitor->student_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($visitor->father_name)
                                                <span class="text-info fw-bold">{{ $visitor->father_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($visitor->course)
                                                <span class="course-badge">{{ $visitor->course->course_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge interaction-count-badge {{ $interactionBadgeClass }}">
                                                {{ $interactionCount }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($latestInteraction)
                                                <div class="last-visit-info">
                                                    <strong>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($latestInteraction->created_at, 'M d, Y') }}</strong>
                                                    <small class="d-block text-muted">
                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($latestInteraction->created_at, 'g:iA') }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('staff.visitor-profile', $visitor->visitor_id) }}" 
                                                   class="btn btn-paytm-primary btn-sm me-2">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="{{ route('staff.visitor-form', ['mobile' => str_replace('+91', '', $visitor->original_mobile_number)]) }}" 
                                                   class="btn btn-paytm-secondary btn-sm add-interaction-btn">
                                                    <i class="fas fa-plus me-1"></i>Add Interaction
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="d-block d-md-none mobile-results">
                        @foreach($visitors as $visitor)
                            @php
                                $interactionCount = $visitor->interactions->count();
                                $latestInteraction = $visitor->interactions->first();
                                
                                // Determine interaction badge color
                                $interactionBadgeClass = 'badge-secondary';
                                if ($interactionCount >= 3) {
                                    $interactionBadgeClass = 'badge-success';
                                } elseif ($interactionCount >= 1) {
                                    $interactionBadgeClass = 'badge-warning';
                                } else {
                                    $interactionBadgeClass = 'badge-danger';
                                }
                            @endphp
                            <div class="mobile-result-card">
                                <div class="mobile-card-header">
                                    <div class="visitor-info">
                                        <h6 class="mb-1">{{ $visitor->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-mobile-alt me-1"></i>{{ $visitor->mobile_number }}
                                        </small>
                                    </div>
                                    <div class="interaction-badge">
                                        <span class="badge interaction-count-badge {{ $interactionBadgeClass }}">
                                            {{ $interactionCount }} visits
                                        </span>
                                    </div>
                                </div>
                                <div class="mobile-card-body">
                                    <div class="row">
                                        @if($visitor->student_name)
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">Student:</small>
                                                <div class="text-primary fw-bold">{{ $visitor->student_name }}</div>
                                            </div>
                                        @endif
                                        @if($visitor->father_name)
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">Father:</small>
                                                <div class="text-info fw-bold">{{ $visitor->father_name }}</div>
                                            </div>
                                        @endif
                                        @if($visitor->course)
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">Course:</small>
                                                <div>{{ $visitor->course->course_name }}</div>
                                            </div>
                                        @endif
                                        @if($latestInteraction)
                                            <div class="col-6 mb-2">
                                                <small class="text-muted">Last Visit:</small>
                                                <div>{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($latestInteraction->created_at, 'M d, Y g:iA') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mobile-card-actions">
                                    <a href="{{ route('staff.visitor-profile', $visitor->visitor_id) }}" 
                                       class="btn btn-paytm-primary btn-sm flex-grow-1 me-2">
                                        <i class="fas fa-eye me-1"></i>View Profile
                                    </a>
                                    <a href="{{ route('staff.visitor-form', ['mobile' => str_replace('+91', '', $visitor->original_mobile_number)]) }}" 
                                       class="btn btn-paytm-secondary btn-sm add-interaction-btn">
                                        <i class="fas fa-plus me-1"></i>Add Interaction
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $visitors->appends(request()->query())->links() }}
            </div>
        @else
            <!-- No Results -->
            <div class="card-paytm paytm-fade-in">
                <div class="card-paytm-body text-center py-5">
                    <div class="no-results-icon mb-3">
                        <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted mb-2">No visitors found</h5>
                    <p class="text-muted mb-4">
                        Try adjusting your search criteria or search for different terms.
                    </p>
                    <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-primary">
                        <i class="fas fa-search me-2"></i>Try Another Search
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
/* Search Results Table Styling */
.search-results-table {
    font-size: 0.9rem;
}

.table-header-paytm {
    background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
    color: white;
}

.table-header-paytm th {
    border: none;
    padding: 1rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.search-result-row {
    transition: all 0.3s ease;
    border-bottom: 1px solid var(--paytm-border-light);
}

.search-result-row:hover {
    background: linear-gradient(135deg, rgba(var(--paytm-primary-rgb), 0.02) 0%, rgba(var(--paytm-primary-rgb), 0.05) 100%);
    transform: scale(1.01);
}

.search-result-row td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border: none;
}

.visitor-name strong {
    color: var(--paytm-dark);
    font-size: 0.95rem;
}

.course-badge {
    background: rgba(var(--paytm-success-rgb), 0.1);
    color: var(--paytm-success);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.last-visit-info strong {
    color: var(--paytm-dark);
    font-size: 0.9rem;
}

.action-buttons .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 8px;
}

/* Interaction count badges with better visibility */
.interaction-count-badge {
    font-weight: 600 !important;
    font-size: 0.85rem !important;
    padding: 0.5rem 0.75rem !important;
    border-radius: 12px !important;
    min-width: 40px !important;
    text-align: center !important;
    display: inline-block !important;
}

.interaction-count-badge.badge-success {
    background-color: #28a745 !important;
    color: #ffffff !important;
    border: 2px solid #1e7e34 !important;
}

.interaction-count-badge.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
    border: 2px solid #e0a800 !important;
    font-weight: 700 !important;
}

.interaction-count-badge.badge-danger {
    background-color: #dc3545 !important;
    color: #ffffff !important;
    border: 2px solid #c82333 !important;
}

.interaction-count-badge.badge-secondary {
    background-color: #6c757d !important;
    color: #ffffff !important;
    border: 2px solid #545b62 !important;
}

/* Add Interaction button styling */
.add-interaction-btn {
    background-color: var(--paytm-secondary) !important;
    color: #ffffff !important;
    border: 2px solid var(--paytm-secondary) !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.add-interaction-btn:hover {
    background-color: var(--paytm-secondary-dark) !important;
    color: #ffffff !important;
    border-color: var(--paytm-secondary-dark) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(var(--paytm-secondary-rgb), 0.3) !important;
}

/* Mobile Results */
.mobile-results {
    padding: 1rem;
}

.mobile-result-card {
    background: var(--paytm-white);
    border: 2px solid var(--paytm-border-light);
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.mobile-result-card:hover {
    border-color: var(--paytm-primary);
    box-shadow: 0 8px 20px rgba(var(--paytm-primary-rgb), 0.15);
    transform: translateY(-2px);
}

.mobile-card-header {
    background: linear-gradient(135deg, rgba(var(--paytm-primary-rgb), 0.05) 0%, rgba(var(--paytm-primary-rgb), 0.02) 100%);
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--paytm-border-light);
}

.mobile-card-body {
    padding: 1rem;
}

.mobile-card-actions {
    padding: 1rem;
    background: rgba(var(--paytm-primary-rgb), 0.02);
    border-top: 1px solid var(--paytm-border-light);
    display: flex;
    gap: 0.5rem;
}

.search-criteria-summary .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-weight: 500;
}

.no-results-icon {
    opacity: 0.3;
    margin-bottom: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .mobile-card-actions {
        flex-direction: column;
    }
    
    .mobile-card-actions .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .mobile-card-actions .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endsection