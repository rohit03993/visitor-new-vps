@extends('layouts.app')

@section('title', 'Visitor Profile - Log Book')
@section('page-title', 'Visitor Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div></div>
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="{{ route('staff.visitor-form', ['mobile' => $originalMobileNumber, 'name' => $visitor->name]) }}" 
                   class="btn btn-paytm-success">
                    <i class="fas fa-plus me-2"></i>Add Revisit
                </a>
                <button onclick="window.print()" class="btn btn-paytm-secondary">
                    <i class="fas fa-print me-2"></i>Print Profile
                </button>
                <a href="{{ route('staff.assigned-to-me') }}" class="btn btn-paytm-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Assigned
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Profile Information -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
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
                            <tr>
                                <td><strong>Total Interactions:</strong></td>
                                <td><span class="badge bg-primary">{{ $interactions->count() }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <table class="table table-borderless">
                            @if($visitor->course)
                            <tr>
                                <td><strong>Course Interest:</strong></td>
                                <td><span class="badge bg-info">{{ $visitor->course->course_name }}</span></td>
                            </tr>
                            @endif
                            @if($visitor->student_name)
                            <tr>
                                <td><strong>Student Name:</strong></td>
                                <td>{{ $visitor->student_name }}</td>
                            </tr>
                            @endif
                            @if($visitor->father_name)
                            <tr>
                                <td><strong>Father's Name:</strong></td>
                                <td>{{ $visitor->father_name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trail Timeline -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                @if($interactions->count() > 0)
                    <!-- Trail Timeline View -->
                    <div class="trail-timeline">
                        @php
                            $groupedInteractions = $interactions->groupBy(function($interaction) {
                                return $interaction->studentSession ? $interaction->studentSession->session_id : 'no-session';
                            });
                        @endphp
                        
                        @foreach($groupedInteractions as $sessionId => $sessionInteractions)
                            @if($sessionId === 'no-session')
                                <!-- Regular Interactions (No Session) -->
                                @foreach($sessionInteractions as $interaction)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-secondary"></div>
                                        <div class="timeline-content">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-user me-2"></i>{{ $interaction->name_entered }}
                                                        </h6>
                                                        <span class="badge bg-secondary">#{{ $interaction->interaction_id }}</span>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Purpose:</strong> <span class="badge bg-primary">{{ $interaction->purpose }}</span></p>
                                                            <p class="mb-1"><strong>Mode:</strong> <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">{{ $interaction->mode }}</span></p>
                                                            <p class="mb-1"><strong>Meeting With:</strong> {{ $interaction->meetingWith->name ?? 'Unknown' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Date:</strong> {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'M d, Y g:iA') }}</p>
                                                            <p class="mb-1"><strong>Address:</strong> {{ $interaction->address->address_name ?? 'N/A' }}</p>
                                                            @if($interaction->initial_notes)
                                                                <p class="mb-1"><strong>Notes:</strong> {{ $interaction->initial_notes }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        @if($interaction->remarks->count() > 0)
                                                            @foreach($interaction->remarks as $remark)
                                                                <div class="alert alert-light mb-2">
                                                                    <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, Y g:iA') }} by {{ $remark->addedBy?->name ?? 'Unknown' }}</small><br>
                                                                    {{ $remark->remark_text }}
                                                                </div>
                                                            @endforeach
                                                            
                                                            <!-- Show Add Remark button if assigned to current user and not completed -->
                                                            @if($interaction->meeting_with == auth()->user()->user_id && !$interaction->is_completed)
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                        <i class="fas fa-plus me-1"></i>Add Remark
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        @else
                                                            @if($interaction->meeting_with == auth()->user()->user_id)
                                                                <span class="badge bg-warning">Remark Pending</span>
                                                                <button class="btn btn-sm btn-outline-primary ms-2" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                                </button>
                                                            @else
                                                                <span class="badge bg-warning">
                                                                    Remark Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Session-based Interactions -->
                                @php $session = $sessionInteractions->first()->studentSession; @endphp
                                <div class="timeline-item session-item">
                                    <div class="timeline-marker bg-{{ $session->status === 'active' ? 'warning' : ($session->status === 'completed' ? 'success' : 'danger') }}"></div>
                                    <div class="timeline-content">
                                        <div class="card border-{{ $session->status === 'active' ? 'warning' : ($session->status === 'completed' ? 'success' : 'danger') }}">
                                            <div class="card-header session-header">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <h6 class="mb-0 text-white me-2">
                                                                <i class="fas fa-{{ $session->purpose === 'Admission' ? 'graduation-cap' : ($session->purpose === 'Complaint' ? 'exclamation-triangle' : 'question-circle') }} me-2"></i>
                                                                Purpose - {{ $session->purpose }}
                                                            </h6>
                                                            @if($session->status === 'active')
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-clock me-1"></i>In-Process
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <small class="text-light">
                                                                <i class="fas fa-calendar me-1"></i>Started {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($session->started_at, 'M d, Y') }}
                                                            </small>
                                                            @if($session->status === 'active')
                                                                @php
                                                                    $latestInteraction = $session->interactions()->orderBy('created_at', 'desc')->first();
                                                                    $canComplete = $latestInteraction && $latestInteraction->meeting_with == auth()->user()->user_id;
                                                                @endphp
                                                                @if(!$canComplete)
                                                                    <small class="text-light">
                                                                        <i class="fas fa-user me-1"></i>Assigned to: {{ $latestInteraction->meetingWith->name ?? 'Unknown' }}
                                                                    </small>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2 align-items-end flex-shrink-0">
                                                        @if($session->status === 'active')
                                                            @if($canComplete)
                                                                <button class="btn btn-sm btn-success modern-btn" onclick="completeSession({{ $session->session_id }})">
                                                                    <i class="fas fa-check me-1"></i>Complete
                                                                </button>
                                                            @endif
                                                        @elseif($session->status === 'completed')
                                                            @if($session->outcome === 'success')
                                                                <span class="badge bg-success px-3 py-2">
                                                                    <i class="fas fa-check-circle me-1"></i>Goal Achieved
                                                                </span>
                                                            @elseif($session->outcome === 'failed')
                                                                <span class="badge bg-danger px-3 py-2">
                                                                    <i class="fas fa-times-circle me-1"></i>Goal Not Achieved
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-danger px-3 py-2">
                                                                <i class="fas fa-times-circle me-1"></i>Cancelled
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="accordion" id="sessionAccordion{{ $session->session_id }}">
                                                    @foreach($sessionInteractions as $index => $interaction)
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="heading{{ $interaction->interaction_id }}">
                                                                <button class="accordion-button interaction-header {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $interaction->interaction_id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $interaction->interaction_id }}">
                                                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                        <div class="d-flex align-items-center flex-grow-1">
                                                                            <i class="fas fa-{{ $interaction->mode === 'In-Campus' ? 'building' : 'phone' }} me-2 text-primary"></i>
                                                                            <div class="interaction-info">
                                                                                <div class="fw-bold interaction-date" style="white-space: nowrap !important; font-family: monospace !important; display: inline-block !important;">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'Md g:iA') }}</div>
                                                                                <small class="text-muted interaction-meeting">
                                                                                    with {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex flex-column gap-1 flex-shrink-0 align-items-end">
                                                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }} px-2 py-1 mode-badge">
                                                                                <i class="fas fa-{{ $interaction->mode === 'In-Campus' ? 'building' : 'phone' }} me-1"></i>
                                                                                <span>{{ $interaction->mode }}</span>
                                                                            </span>
                                                                            @if($interaction->remarks->count() > 0)
                                                                                @if($interaction->is_completed)
                                                                                    @php
                                                                                        $latestRemark = $interaction->remarks->last();
                                                                                        $outcome = $latestRemark->outcome ?? 'in_process';
                                                                                    @endphp
                                                                                    @if($outcome === 'closed_positive')
                                                                                        <span class="badge bg-success px-2 py-1">
                                                                                            <i class="fas fa-check-circle me-1"></i>Closed (Positive)
                                                                                        </span>
                                                                                    @elseif($outcome === 'closed_negative')
                                                                                        <span class="badge bg-danger px-2 py-1">
                                                                                            <i class="fas fa-times-circle me-1"></i>Closed (Negative)
                                                                                        </span>
                                                                                    @else
                                                                                        <span class="badge bg-info px-2 py-1">
                                                                                            <i class="fas fa-comment me-1"></i>Remark Updated
                                                                                        </span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class="badge bg-info px-2 py-1">
                                                                                        <i class="fas fa-comment me-1"></i>Remark Updated
                                                                                    </span>
                                                                                @endif
                                                                            @else
                                                                                <span class="badge bg-warning px-2 py-1">
                                                                                    <i class="fas fa-clock me-1"></i>Remark Pending
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapse{{ $interaction->interaction_id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $interaction->interaction_id }}" data-bs-parent="#sessionAccordion{{ $session->session_id }}">
                                                                <div class="accordion-body">
                                                                    <div class="row">
                                                                        <!-- Visit Entered By - Full width on mobile, left column on desktop -->
                                                                        <div class="col-lg-4 col-12 mb-3">
                                                                            <div class="modern-card">
                                                                                <div class="card-header-modern">
                                                                                    <i class="fas fa-user-plus me-2"></i>
                                                                                    <strong>Visit Entered By</strong>
                                                                                </div>
                                                                                <div class="card-body-modern">
                                                                                    <div class="visit-entered-info">
                                                                                        <div class="staff-name">
                                                                                            <i class="fas fa-user-circle me-2"></i>
                                                                                            <strong>{{ $interaction->createdBy->name ?? 'Unknown' }}</strong>
                                                                                        </div>
                                                                                        <div class="visit-datetime">
                                                                                            <i class="fas fa-calendar-alt me-2"></i>
                                                                                            <span style="white-space: nowrap !important; font-family: monospace !important;">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'MdY g:iA') }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Notes and Remarks Content - Full width on mobile, right column on desktop -->
                                                                        <div class="col-lg-8 col-12">
                                                                            <div class="row">
                                                                                <!-- Notes Section - Full width on mobile, half width on desktop -->
                                                                                <div class="col-lg-6 col-12 mb-3">
                                                                                    <h6 class="section-label">
                                                                                        <i class="fas fa-sticky-note me-2"></i>Notes
                                                                                    </h6>
                                                                                    @if($interaction->initial_notes)
                                                                                        <div class="highlighted-box notes-highlight">
                                                                                            {{ $interaction->initial_notes }}
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="highlighted-box notes-highlight empty">
                                                                                            <i class="fas fa-sticky-note text-muted"></i>
                                                                                            <span class="text-muted">No notes</span>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                
                                                                                <!-- Remarks Section - Full width on mobile, half width on desktop -->
                                                                                <div class="col-lg-6 col-12 mb-3">
                                                                                    <h6 class="section-label">
                                                                                        <i class="fas fa-comments me-2"></i>Remarks
                                                                                    </h6>
                                                                                    @if($interaction->remarks->count() > 0)
                                                                                        @foreach($interaction->remarks as $remark)
                                                                                            <div class="highlighted-box remarks-highlight">
                                                                                                <div class="remark-content">
                                                                                                    {{ $remark->remark_text }}
                                                                                                </div>
                                                                                                <div class="remark-meta">
                                                                                                    <div class="remark-author">
                                                                                                        <i class="fas fa-user-circle me-1"></i>
                                                                                                        {{ $remark->addedBy?->name ?? 'Unknown' }}
                                                                                                    </div>
                                                                                                    <div class="remark-time">
                                                                                                        <i class="fas fa-clock me-1"></i>
                                                                                                        <span style="white-space: nowrap !important; font-family: monospace !important;">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'MdY g:iA') }}</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                        
                                                                                        <!-- Show Add Remark button if assigned to current user and not completed -->
                                                                                        @if($interaction->meeting_with == auth()->user()->user_id && !$interaction->is_completed)
                                                                                            <div class="mt-2">
                                                                                                <button class="btn btn-primary btn-sm" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                                                                </button>
                                                                                            </div>
                                                                                        @endif
                                                                                    @else
                                                                                        <div class="highlighted-box remarks-highlight empty">
                                                                                            <i class="fas fa-comment-slash text-muted"></i>
                                                                                            <span class="text-muted">No remarks</span>
                                                                                            @if($interaction->meeting_with == auth()->user()->user_id)
                                                                                                <button class="btn btn-primary btn-sm mt-2" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                                                                </button>
                                                                                            @else
                                                                                                <div class="alert alert-info alert-sm mt-2">
                                                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                                                    Assigned to: <strong>{{ $interaction->meetingWith->name ?? 'Unknown' }}</strong>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
                        <p class="text-muted">This visitor hasn't had any interactions yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Remark Modal -->
<div class="modal fade" id="remarkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-comment me-2"></i>Add Remark
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="remarkForm">
                <input type="hidden" id="interaction_id" name="interaction_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Interaction Details:</strong>
                        <div id="interactionDetails" class="mt-2"></div>
                    </div>
                    
                    
                    <div class="mb-3">
                        <label for="remarkText" class="form-label">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="remarkText" name="remark_text" rows="4" 
                                  placeholder="Enter your remark/note about this interaction..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-warning" onclick="showAssignModal()">
                        <i class="fas fa-exchange-alt me-1"></i>Transfer to Team Member
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Remark
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign to Team Member Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Transfer to Team Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                <input type="hidden" id="assign_interaction_id" name="interaction_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Interaction Details:</strong>
                        <div id="assignInteractionDetails" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="teamMember" class="form-label">Select Team Member <span class="text-danger">*</span></label>
                        <select class="form-select" id="teamMember" name="team_member_id" required>
                            <option value="">Choose a team member...</option>
                            @foreach(\App\Models\VmsUser::where('role', 'staff')->where('is_active', true)->where('user_id', '!=', auth()->id())->get() as $member)
                                <option value="{{ $member->user_id }}">{{ $member->name }} ({{ $member->branch->branch_name ?? 'No Branch' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select a team member to transfer this interaction to</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assignNotes" class="form-label">Transfer Notes</label>
                        <textarea class="form-control" id="assignNotes" name="assignment_notes" rows="3" 
                                  placeholder="Optional: Add notes about why you're transferring this interaction..."></textarea>
                        <div class="form-text">Optional: Add notes that will be visible to the assigned team member</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exchange-alt me-1"></i>Transfer to Team Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Session Completion Modal -->
<div class="modal fade" id="completeSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Complete Student Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeSessionForm">
                <input type="hidden" id="session_id" name="session_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Session Details:</strong>
                        <div id="sessionDetails" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="outcome" class="form-label">Session Outcome <span class="text-danger">*</span></label>
                        <select class="form-select" id="outcome" name="outcome" required>
                            <option value="">Select Outcome</option>
                            <option value="success">Success - Goal Achieved</option>
                            <option value="failed">Failed - Goal Not Achieved</option>
                        </select>
                        <div class="form-text">Select the final outcome of this student session</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="outcome_notes" class="form-label">Outcome Notes</label>
                        <textarea class="form-control" id="outcome_notes" name="outcome_notes" rows="4" 
                                  placeholder="Enter detailed notes about the session outcome..."></textarea>
                        <div class="form-text">Optional: Add detailed notes about the session result</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Complete Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* Custom styles to match the screenshot */
.card {
    border-radius: 8px;
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .text-end {
        text-align: left !important;
    }
    
    .row .col-md-4 {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Show Remark Modal
function showRemarkModal(interactionId, visitorName, purpose, studentName) {
    document.getElementById('interaction_id').value = interactionId;
    
    // Show student name if available, otherwise show contact person
    const displayName = studentName && studentName.trim() !== '' ? 
        `<strong>Student Name:</strong> ${studentName}` : 
        `<strong>Contact Person:</strong> ${visitorName}`;
    
    document.getElementById('interactionDetails').innerHTML = `
        ${displayName}<br>
        <strong>Purpose:</strong> ${purpose}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('remarkModal'));
    modal.show();
}

// Show Assign Modal
function showAssignModal() {
    // Get the current interaction ID from the remark modal
    const interactionId = document.getElementById('interaction_id').value;
    const interactionDetails = document.getElementById('interactionDetails').innerHTML;
    
    // Set the interaction ID for assignment
    document.getElementById('assign_interaction_id').value = interactionId;
    document.getElementById('assignInteractionDetails').innerHTML = interactionDetails;
    
    // Close the remark modal and open the assign modal
    bootstrap.Modal.getInstance(document.getElementById('remarkModal')).hide();
    
    // Show assign modal after a short delay
    setTimeout(() => {
        const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
        assignModal.show();
    }, 300);
}

// Handle remark form submission
document.getElementById('remarkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const interactionId = document.getElementById('interaction_id').value;
    const formData = new FormData(this);
    
    fetch(`{{ url('staff/update-remark') }}/${interactionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        // Check if response is ok (status 200-299)
        if (response.ok) {
            // Try to parse as JSON first
            return response.json().catch(() => {
                // If JSON parsing fails, assume success
                return { success: true };
            });
        } else {
            // If response is not ok, try to get error message
            return response.json().catch(() => {
                return { success: false, message: 'Server error' };
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert('Remark added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('remarkModal')).hide();
            this.reset();
            
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add remark'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Even if there's an error, check if the remark was actually saved
        // by reloading the page
        alert('Remark may have been saved. Refreshing page...');
        window.location.reload();
    });
});

// Handle assignment form submission
document.getElementById('assignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const interactionId = formData.get('interaction_id');
    
    fetch(`{{ url('staff/assign-interaction') }}/${interactionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Interaction transferred successfully! Your interaction has been completed and a new interaction has been created for the assigned team member.');
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to transfer interaction'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to assign interaction');
    });
});

// Session Completion Functions
function completeSession(sessionId) {
    // Get session details
    fetch(`{{ url('staff/session') }}/${sessionId}/modal`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate modal with session details
            document.getElementById('session_id').value = data.session.session_id;
            document.getElementById('sessionDetails').innerHTML = `
                <strong>Purpose:</strong> ${data.session.purpose}<br>
                <strong>Student:</strong> ${data.session.student_name || data.session.visitor_name}<br>
                <strong>Started:</strong> ${data.session.started_at}<br>
                <strong>Interactions:</strong> ${data.session.interaction_count}
            `;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('completeSessionModal'));
            modal.show();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to load session details');
    });
}

// Handle session completion form submission
document.getElementById('completeSessionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const sessionId = document.getElementById('session_id').value;
    const formData = new FormData(this);
    
    fetch(`{{ url('staff/complete-session') }}/${sessionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session completed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('completeSessionModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to complete session');
    });
});
</script>
@endsection
