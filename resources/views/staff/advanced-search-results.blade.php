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

        <!-- Results -->
        @if($visitors->count() > 0)
            <div class="row">
                @foreach($visitors as $visitor)
                    <div class="col-lg-6 col-12 mb-4">
                        <div class="card-paytm visitor-result-card paytm-fade-in">
                            <div class="card-paytm-header">
                                <div class="d-flex align-items-center">
                                    <div class="visitor-avatar me-3">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $visitor->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-mobile-alt me-1"></i>{{ $visitor->mobile_number }}
                                        </small>
                                    </div>
                                    <div class="visitor-status">
                                        @if($visitor->interactions->count() > 0)
                                            <span class="badge badge-paytm-enhanced badge-paytm-success">
                                                {{ $visitor->interactions->count() }} {{ Str::plural('visit', $visitor->interactions->count()) }}
                                            </span>
                                        @else
                                            <span class="badge badge-paytm-enhanced badge-paytm-warning">
                                                New Visitor
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-paytm-body">
                                <!-- Visitor Details -->
                                <div class="visitor-details mb-3">
                                    @if($visitor->student_name)
                                        <div class="detail-item">
                                            <i class="fas fa-user-graduate text-primary me-2"></i>
                                            <strong>Student:</strong> {{ $visitor->student_name }}
                                        </div>
                                    @endif
                                    @if($visitor->father_name)
                                        <div class="detail-item">
                                            <i class="fas fa-user-tie text-info me-2"></i>
                                            <strong>Father:</strong> {{ $visitor->father_name }}
                                        </div>
                                    @endif
                                    @if($visitor->course)
                                        <div class="detail-item">
                                            <i class="fas fa-book text-success me-2"></i>
                                            <strong>Course:</strong> {{ $visitor->course->course_name }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Latest Interaction -->
                                @if($visitor->interactions->count() > 0)
                                    @php
                                        $latestInteraction = $visitor->interactions->first();
                                    @endphp
                                    <div class="latest-interaction">
                                        <h6 class="text-muted mb-2">
                                            <i class="fas fa-clock me-1"></i>Latest Visit
                                        </h6>
                                        <div class="interaction-summary">
                                            <div class="interaction-meta">
                                                <small class="text-muted">
                                                    {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($latestInteraction->created_at, 'M d, Y g:iA') }}
                                                </small>
                                                @if($latestInteraction->meetingWith)
                                                    <small class="text-muted ms-2">
                                                        with {{ $latestInteraction->meetingWith->name }}
                                                        @if($latestInteraction->meetingWith->branch)
                                                            ({{ $latestInteraction->meetingWith->branch->branch_name }})
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                            @if($latestInteraction->initial_notes)
                                                <div class="interaction-notes mt-2">
                                                    <small class="text-muted">
                                                        {{ Str::limit($latestInteraction->initial_notes, 100) }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="mt-3 d-flex gap-2">
                                    <a href="{{ route('staff.visitor-profile', $visitor->visitor_id) }}" 
                                       class="btn btn-paytm-primary btn-sm flex-grow-1">
                                        <i class="fas fa-eye me-2"></i>View Profile
                                    </a>
                                    <a href="{{ route('staff.visitor-form', ['mobile' => str_replace('+91', '', $visitor->original_mobile_number)]) }}" 
                                       class="btn btn-paytm-secondary btn-sm">
                                        <i class="fas fa-plus me-2"></i>Add Visit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
.visitor-result-card {
    transition: all 0.3s ease;
}

.visitor-result-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.visitor-avatar i {
    font-size: 2.5rem;
    color: var(--paytm-primary);
}

.detail-item {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.interaction-summary {
    background: rgba(var(--paytm-primary-rgb), 0.05);
    border-left: 3px solid var(--paytm-primary);
    padding: 0.75rem;
    border-radius: 0 8px 8px 0;
}

.search-criteria-summary .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
}

.no-results-icon {
    opacity: 0.5;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .visitor-result-card {
        margin-bottom: 1rem;
    }
    
    .search-criteria-summary .row {
        gap: 0.5rem;
    }
    
    .visitor-avatar i {
        font-size: 2rem;
    }
}
</style>
@endsection
