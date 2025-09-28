<!-- Interaction Details Content -->
<div class="interaction-details">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">{{ $interaction->name_entered }}</h4>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i>
                        {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'M d, Y g:i A') }}
                    </p>
                </div>
                <div class="text-end">
                    @if($interaction->remarks->count() > 0)
                        @if($interaction->is_completed)
                            @php
                                $latestRemark = $interaction->remarks->last();
                                $outcome = $latestRemark->outcome ?? 'in_process';
                            @endphp
                            @if($outcome === 'closed_positive')
                                <span class="badge bg-success fs-6">Closed (Positive)</span>
                            @elseif($outcome === 'closed_negative')
                                <span class="badge bg-danger fs-6">Closed (Negative)</span>
                            @else
                                <span class="badge bg-info fs-6">Remark Updated</span>
                            @endif
                        @else
                            <span class="badge bg-info fs-6">Remark Updated</span>
                        @endif
                    @else
                        <span class="badge bg-warning fs-6">Remark Pending</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-user me-2"></i>Visitor Information
                </h6>
                <div class="info-content">
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $interaction->name_entered }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mobile:</span>
                        <span class="info-value">{{ $interaction->visitor->mobile_number }}</span>
                    </div>
                    @if($interaction->visitor->email)
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $interaction->visitor->email }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-handshake me-2"></i>Meeting Details
                </h6>
                <div class="info-content">
                    <div class="info-item">
                        <span class="info-label">Meeting With:</span>
                        <span class="info-value">{{ $interaction->meetingWith->name ?? 'No Data' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Branch:</span>
                        <span class="badge bg-info">{{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Location:</span>
                        <span class="info-value">{{ $interaction->address->address_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purpose and Mode -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-bullseye me-2"></i>Purpose & Mode
                </h6>
                <div class="info-content">
                    <div class="info-item">
                        <span class="info-label">Purpose:</span>
                        <span class="badge bg-primary">{{ $interaction->purpose }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mode:</span>
                        <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                            <i class="fas fa-{{ $interaction->mode === 'In-Campus' ? 'building' : 'phone' }} me-1"></i>
                            {{ $interaction->mode }}
                        </span>
                    </div>
                    @if($interaction->course)
                        <div class="info-item">
                            <span class="info-label">Course:</span>
                            <span class="info-value">{{ $interaction->course->course_name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-tags me-2"></i>Tags
                </h6>
                <div class="info-content">
                    @if($interaction->tags->count() > 0)
                        @foreach($interaction->tags as $tag)
                            <span class="badge bg-secondary me-1 mb-1">{{ $tag->name }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">No tags assigned</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Initial Notes -->
    @if($interaction->initial_notes)
        <div class="row mb-4">
            <div class="col-12">
                <div class="info-card">
                    <h6 class="info-title">
                        <i class="fas fa-sticky-note me-2"></i>Initial Notes
                    </h6>
                    <div class="info-content">
                        <p class="mb-0">{{ $interaction->initial_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Remarks Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-comments me-2"></i>Remarks History
                </h6>
                <div class="info-content">
                    @if($remarks->count() > 0)
                        <div class="remarks-timeline">
                            @foreach($remarks as $remark)
                                <div class="remark-item">
                                    <div class="remark-header">
                                        <div class="remark-meta">
                                            <span class="remark-date">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, Y g:i A') }}
                                                @if($remark->meeting_duration)
                                                    • <i class="fas fa-stopwatch me-1"></i>{{ $remark->meeting_duration }} mins
                                                @endif
                                            </span>
                                            <span class="remark-author">
                                                by <strong>{{ $remark->addedBy?->name ?? 'Unknown' }}</strong>
                                                <span class="text-muted">({{ $remark->addedBy?->branch?->branch_name ?? 'No Branch' }})</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="remark-content">
                                        <div class="remark-text bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }} {{ $remark->remark_text == 'NA' ? 'text-dark' : 'text-white' }} p-3 rounded">
                                            {{ $remark->remark_text }}
                                        </div>
                                        @if($remark->outcome)
                                            <div class="remark-outcome mt-2">
                                                <span class="badge bg-{{ $remark->outcome === 'closed_positive' ? 'success' : ($remark->outcome === 'closed_negative' ? 'danger' : 'info') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $remark->outcome)) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-comment-slash fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No remarks available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Remark Form (only if no remarks or not completed) -->
    @if($interaction->remarks->count() == 0 || !$interaction->is_completed)
        <div class="row">
            <div class="col-12">
                <div class="info-card">
                    <h6 class="info-title">
                        <i class="fas fa-plus me-2"></i>Add Remark
                    </h6>
                    <div class="info-content">
                        <form id="addRemarkForm" onsubmit="return submitRemarkForm(this);" action="{{ route('staff.update-remark', $interaction->interaction_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="remarkText" class="form-label">Remark/Note <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="remarkText" name="remark_text" rows="4" required placeholder="Enter your remark/note about the meeting..."></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Add Remark
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Interaction Details Styles */
.interaction-details {
    max-height: 70vh;
    overflow-y: auto;
}

.info-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    height: 100%;
}

.info-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    min-width: 100px;
}

.info-value {
    color: #495057;
    font-weight: 500;
}

/* Remarks Timeline */
.remarks-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.remark-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
}

.remark-header {
    margin-bottom: 0.75rem;
}

.remark-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.remark-date {
    color: #6c757d;
    font-size: 0.875rem;
}

.remark-author {
    color: #495057;
    font-size: 0.875rem;
}

.remark-content {
    margin-top: 0.5rem;
}

.remark-text {
    font-size: 0.95rem;
    line-height: 1.5;
    word-wrap: break-word;
}

.remark-outcome {
    margin-top: 0.5rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .interaction-details {
        max-height: 60vh;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .info-label {
        min-width: auto;
        font-size: 0.875rem;
    }
    
    .remark-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .remark-date,
    .remark-author {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .info-card {
        padding: 0.75rem;
    }
    
    .info-title {
        font-size: 0.95rem;
    }
    
    .remark-item {
        padding: 0.75rem;
    }
    
    .remark-text {
        font-size: 0.875rem;
        padding: 0.75rem !important;
    }
}
</style>
