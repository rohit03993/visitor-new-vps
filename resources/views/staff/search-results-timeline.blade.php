@extends('layouts.app')

@section('title', 'Visitor Profile - VMS')
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
                <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Search
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
                                <td><strong>Latest Name:</strong></td>
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
                                                        @else
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
                                                <span class="badge bg-info">Remark Updated</span>
                                            @endif
                                        @else
                                            @if($interaction->meeting_with == auth()->user()->user_id)
                                                <span class="badge bg-warning">Remark Pending</span>
                                                <button class="btn btn-sm btn-outline-primary ms-2" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}')">
                                                    <i class="fas fa-plus me-1"></i>Add Remark
                                                </button>
                                            @else
                                                <span class="badge bg-warning">
                                                    Remark Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                </span>
                                            @endif
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
                                                                                    @else
                                                                                        <div class="highlighted-box remarks-highlight empty">
                                                                                            <i class="fas fa-comment-slash text-muted"></i>
                                                                                            <span class="text-muted">No remarks</span>
                                                                                            @if($interaction->meeting_with == auth()->user()->user_id)
                                                                                                <button class="btn btn-primary btn-sm mt-2" onclick="showRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}')">
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
    <div class="modal-dialog">
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Remark
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Session Completion Modal -->
<div class="modal fade" id="completeSessionModal" tabindex="-1">
    <div class="modal-dialog">
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
                            <option value="pending">Pending - Follow-up Required</option>
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
        /* Cache bust: {{ time() }} - Force date to stay on one line and fix accordion arrows - CHANGES ARE HERE! */
        
        /* Badge layout fixes for mobile */
        .interaction-header .badge {
            font-size: 0.7rem !important;
            white-space: nowrap !important;
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        
        .interaction-header .d-flex.flex-column {
            min-width: 0 !important;
            max-width: 120px !important;
        }
        
        /* Fix Profile Information alignment on mobile */
        @media (max-width: 768px) {
            .table td {
                vertical-align: middle !important;
                padding: 0.25rem 0.5rem !important;
                border: none !important;
            }
            
            .table td strong {
                display: inline-block !important;
                margin-bottom: 0 !important;
                width: 45% !important;
                font-weight: 600 !important;
            }
            
            .table td:last-child {
                width: 55% !important;
                text-align: right !important;
            }
            
            .badge {
                display: inline-block !important;
                vertical-align: middle !important;
                margin: 0 !important;
                font-size: 0.75rem !important;
            }
            
            /* Remove extra spacing from table rows */
            .table tr {
                border: none !important;
            }
            
            .table tr td {
                padding-top: 0.4rem !important;
                padding-bottom: 0.4rem !important;
            }
            
            /* MOBILE ARROW POSITIONING - BOTTOM RIGHT CORNER FOR COLLAPSED ONLY */
            .accordion-button::after {
                display: none !important;
            }
            
            .accordion-button {
                padding-right: 0.5rem !important;
                position: relative !important;
            }
            
            /* Only target collapsed accordion buttons */
            .accordion-item .accordion-button.collapsed::before {
                content: "▶" !important;
                position: absolute !important;
                right: 0.5rem !important;
                bottom: 0.5rem !important;
                font-size: 0.7rem !important;
                color: white !important;
                background: rgba(0,0,0,0.3) !important;
                padding: 0.2rem !important;
                border-radius: 0.2rem !important;
                z-index: 10 !important;
            }
            
            /* For expanded accordion buttons */
            .accordion-item .accordion-button:not(.collapsed)::before {
                content: "▼" !important;
                position: absolute !important;
                right: 0.5rem !important;
                bottom: 0.5rem !important;
                font-size: 0.7rem !important;
                color: white !important;
                background: rgba(0,0,0,0.3) !important;
                padding: 0.2rem !important;
                border-radius: 0.2rem !important;
                z-index: 10 !important;
            }
            
            /* Modern Complete Session Button */
            .modern-btn {
                font-size: 0.65rem !important;
                padding: 0.4rem 0.8rem !important;
                border-radius: 0.5rem !important;
                font-weight: 600 !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
                border: none !important;
                transition: all 0.3s ease !important;
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
                color: white !important;
                min-width: 80px !important;
            }
            
            .modern-btn:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
                background: linear-gradient(135deg, #218838 0%, #1ea085 100%) !important;
            }
            
            .modern-btn:active {
                transform: translateY(0) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            }
        }
/* Profile Summary Styles */
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stats-grid {
    display: flex;
    gap: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Timeline Styles */
.timeline-container {
    position: relative;
    padding-left: 30px;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #667eea, #764ba2, #f093fb);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 20px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #667eea;
    z-index: 2;
}

.timeline-content {
    margin-left: 20px;
}

.session-item .timeline-marker {
    width: 18px;
    height: 18px;
    left: -26px;
    top: 16px;
    box-shadow: 0 0 0 4px #667eea;
}

/* Session Header Styles */
.session-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.session-header h6 {
    color: white;
}

.session-header .badge {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.session-header .btn-success {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.session-header .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.session-meta {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}

.session-meta small {
    color: rgba(255, 255, 255, 0.9);
}

/* Accordion Styles */
.accordion-button {
    background-color: #f8f9fa;
    border: none;
    box-shadow: none;
    padding: 15px 20px;
    border-radius: 8px !important;
}

.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #1976d2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.accordion-button::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.accordion-item {
    border: 1px solid #dee2e6;
    margin-bottom: 15px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.accordion-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

/* Interaction Header Styles */
.interaction-header {
    font-weight: 500;
}

.interaction-header .fw-bold {
    font-size: 1.1rem;
}

/* Info Item Styles */
.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 5px 0;
}

.info-item i {
    width: 20px;
    text-align: center;
}

/* Remarks Section */
.remarks-section {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
}

.remark-card {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    position: relative;
}

.remark-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #007bff;
    border-radius: 2px 0 0 2px;
}

.remark-content {
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 8px;
    color: #333;
}

.remark-meta {
    font-size: 0.85rem;
    color: #6c757d;
    display: flex;
    align-items: center;
}

.no-remarks {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
}

/* Card Styles */
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    padding: 15px 20px;
}

/* Badge Styles */
.badge {
    font-size: 0.8em;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 20px;
}

/* Button Styles */
.btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .timeline-container {
        padding-left: 20px;
    }
    
    .timeline-container::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -17px;
    }
    
    .session-item .timeline-marker {
        left: -19px;
    }
    
    .timeline-content {
        margin-left: 15px;
    }
    
    .accordion-button {
        padding: 12px 15px;
    }
    
    /* Mobile Profile Information */
    .card-body .table td {
        padding: 0.5rem 0.25rem;
        font-size: 0.9rem;
    }
    
    .card-body .table td:first-child {
        width: 40%;
        font-size: 0.85rem;
    }
    
    /* Mobile Accordion */
    .accordion-body {
        padding: 1rem;
    }
    
    .modern-card {
        margin-bottom: 1rem;
    }
    
    .card-header-modern {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .card-body-modern {
        padding: 0.75rem;
    }
    
    .visit-entered-info {
        font-size: 0.85rem;
    }
    
    .staff-name, .visit-datetime {
        margin-bottom: 0.5rem;
    }
    
    /* Mobile Highlighted Boxes */
    .highlighted-box {
        padding: 15px !important;
        margin-bottom: 10px !important;
    }
    
    .section-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .remark-content {
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .remark-meta {
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    
    /* Mobile Buttons */
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
    
    /* Mobile Badges */
    .badge {
        font-size: 0.75rem;
    }
    
    .session-meta .row {
        flex-direction: column;
    }
    
    /* Mobile Accordion Header - Modern Touch Design */
    .accordion-button {
        padding: 0.875rem 0.75rem;
        font-size: 0.9rem;
        border: none;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .accordion-button:not(.collapsed) {
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    
    .interaction-info {
        flex-grow: 1;
        min-width: 0;
    }
    
    .interaction-date {
        font-size: 0.85rem;
        line-height: 1.1;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    }
    
    /* Force ALL dates to stay on one line - GLOBAL FIX */
    .visit-datetime, .remark-time, .interaction-date, 
    .interaction-info, .accordion-button .fw-bold,
    .accordion-button .interaction-date,
    .accordion-button .interaction-info .fw-bold {
        white-space: nowrap !important;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace !important;
        display: inline-block !important;
        max-width: 100% !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    
    /* Force accordion button content to not wrap */
    .accordion-button .d-flex {
        flex-wrap: nowrap !important;
    }
    
    .accordion-button .interaction-info {
        min-width: 0 !important;
        flex-shrink: 1 !important;
    }
    
    .interaction-meeting {
        font-size: 0.75rem;
        line-height: 1.1;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* SUPER AGGRESSIVE - Force all text in accordion headers to not wrap */
    .accordion-button * {
        white-space: nowrap !important;
    }
    
    .accordion-button .fw-bold {
        white-space: nowrap !important;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace !important;
        display: inline !important;
    }
    
    .mode-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-weight: 500;
    }
    
    /* Modern Touch-Friendly Spacing */
    .accordion-item {
        margin-bottom: 0.5rem;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    .accordion-item:last-child {
        margin-bottom: 0;
    }
    
    /* Mobile Accordion Body - Original Layout */
    .accordion-body {
        padding: 1rem;
    }
    
    .visit-entered-section {
        margin-bottom: 1rem;
    }
    
    .content-section {
        margin-bottom: 1rem;
    }
    
    .section-label {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #495057;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.5rem;
    }
    
    /* Mobile Cards - Modern Design */
    .modern-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        background: #fff;
        overflow: hidden;
    }
    
    .card-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .card-header-modern i {
        color: rgba(255,255,255,0.9);
    }
    
    .card-body-modern {
        padding: 1rem;
    }
    
    .visit-entered-info {
        font-size: 0.85rem;
    }
    
    .staff-name, .visit-datetime {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
    }
    
    .staff-name i, .visit-datetime i {
        color: #667eea;
        width: 18px;
        font-size: 0.9rem;
    }
    
    /* Mobile Highlighted Boxes */
    .highlighted-box {
        padding: 1rem !important;
        margin-bottom: 0.75rem !important;
        border-radius: 8px !important;
        border-left: 4px solid !important;
    }
    
    .notes-highlight {
        border-left-color: #28a745 !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%) !important;
    }
    
    .remarks-highlight {
        border-left-color: #007bff !important;
        background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%) !important;
    }
    
    .highlighted-box.empty {
        text-align: center;
        padding: 1.5rem 1rem !important;
        color: #6c757d;
    }
    
    .highlighted-box.empty i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    /* Mobile Remark Content */
    .remark-content {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 0.75rem;
    }
    
    .remark-meta {
        font-size: 0.75rem;
        color: #6c757d;
        border-top: 1px solid #e9ecef;
        padding-top: 0.5rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .remark-author, .remark-time {
        display: flex;
        align-items: center;
    }
    
    /* Mobile Buttons */
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
    }
    
    /* Mobile Alerts */
    .alert-sm {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
    }
}
    
    .session-meta .col-md-6 {
        margin-bottom: 5px;
    }
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.timeline-item {
    animation: fadeIn 0.5s ease-out;
}

.accordion-collapse {
    transition: all 0.3s ease;
}

/* Modern Card Styles */
.modern-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.card-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.card-body-modern {
    padding: 20px;
}

/* Detail Grid */
.detail-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.detail-label {
    font-weight: 600;
    color: #495057;
    min-width: 100px;
    font-size: 14px;
}

.detail-value {
    color: #6c757d;
    font-size: 14px;
}

/* Modern Remarks */
.remarks-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.remark-item-modern {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 12px;
    padding: 20px;
    border-left: 5px solid #007bff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    position: relative;
}

.remark-item-modern:hover {
    background: linear-gradient(135deg, #bbdefb 0%, #e1bee7 100%);
    transform: translateX(6px);
    box-shadow: 0 4px 16px rgba(0,123,255,0.2);
}

.remark-item-modern::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 20px 20px 0;
    border-color: transparent #007bff transparent transparent;
}

.remark-content-modern {
    font-size: 16px;
    line-height: 1.6;
    color: #2c3e50;
    margin-bottom: 12px;
    font-weight: 500;
    background: rgba(255,255,255,0.7);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid rgba(0,123,255,0.2);
}

.remark-meta-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #6c757d;
}

.remark-author {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.remark-time {
    display: flex;
    align-items: center;
}

/* Modern Buttons */
.btn-modern {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,123,255,0.4);
}

/* Modern Alerts */
.alert-modern {
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    font-weight: 500;
}

/* No Remarks Modern */
.no-remarks-modern {
    padding: 20px;
}

.no-remarks-modern i {
    opacity: 0.5;
}

/* Visit Entered By Styles */
.visit-entered-info {
    text-align: center;
    padding: 10px 0;
}

.staff-name {
    font-size: 18px;
    color: #2c3e50;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.staff-name i {
    color: #007bff;
    font-size: 20px;
}

.visit-datetime {
    font-size: 14px;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
}

.visit-datetime i {
    color: #28a745;
}

/* Section Titles */
.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
}

.section-title i {
    color: #007bff;
}

/* Notes Section */
.notes-section {
    padding-right: 15px;
}

.notes-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #28a745;
    font-size: 14px;
    line-height: 1.5;
    color: #495057;
}

.no-notes {
    text-align: center;
    padding: 20px;
    color: #6c757d;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.no-notes i {
    font-size: 24px;
    opacity: 0.5;
}

/* Remarks Section */
.remarks-section {
    padding-left: 15px;
}

/* Smaller remark items for side-by-side layout */
.remarks-section .remark-item-modern {
    padding: 12px;
    margin-bottom: 8px;
}

.remarks-section .remark-content-modern {
    font-size: 14px;
    padding: 8px;
    margin-bottom: 8px;
}

.remarks-section .remark-meta-modern {
    font-size: 11px;
}

/* Section Labels */
.section-label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 12px;
    font-size: 16px;
    display: flex;
    align-items: center;
}

.section-label i {
    color: #007bff;
}

/* Highlighted Boxes */
.highlighted-box {
    background: white !important;
    border-radius: 12px !important;
    padding: 20px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
    border: 2px solid #e9ecef !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    overflow: hidden !important;
    display: block !important;
    margin-bottom: 15px !important;
}

.highlighted-box:hover {
    box-shadow: 0 6px 30px rgba(0,0,0,0.2);
    transform: translateY(-3px);
}

.highlighted-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007bff, #28a745);
}

.notes-highlight {
    border-left: 5px solid #28a745 !important;
    background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%) !important;
}

.notes-highlight::before {
    background: linear-gradient(90deg, #28a745, #20c997);
}

.remarks-highlight {
    border-left: 5px solid #007bff !important;
    background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%) !important;
}

.remarks-highlight::before {
    background: linear-gradient(90deg, #007bff, #6f42c1);
}

.highlighted-box.empty {
    text-align: center;
    color: #6c757d;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    min-height: 80px;
    justify-content: center;
}

.remark-content {
    font-size: 16px;
    line-height: 1.6;
    color: #2c3e50;
    margin-bottom: 12px;
    font-weight: 500;
}

.remark-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #6c757d;
    margin-top: auto;
}

.remark-author {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.remark-time {
    display: flex;
    align-items: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .visit-entered-info {
        text-align: left;
    }
    
    .staff-name {
        justify-content: flex-start;
    }
    
    .visit-datetime {
        justify-content: flex-start;
    }
    
    .content-box {
        margin-bottom: 16px;
        min-height: 100px;
    }
    
    .remark-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Show Remark Modal
function showRemarkModal(interactionId, visitorName, purpose) {
    document.getElementById('interaction_id').value = interactionId;
    document.getElementById('interactionDetails').innerHTML = `
        <strong>Visitor:</strong> ${visitorName}<br>
        <strong>Purpose:</strong> ${purpose}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('remarkModal'));
    modal.show();
}

// Handle remark form submission
document.getElementById('remarkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const interactionId = document.getElementById('interaction_id').value;
    const formData = new FormData(this);
    
    fetch(`{{ url('staff/update-remark') }}/${interactionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
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
        alert('Error: Failed to add remark');
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
                <strong>Student:</strong> ${data.session.visitor_name}<br>
                <strong>Started:</strong> ${data.session.started_at}<br>
                <strong>Started by:</strong> ${data.session.started_by}<br>
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
