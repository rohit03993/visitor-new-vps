@extends('layouts.app')

@section('title', 'Log - Task Book')
@section('page-title', 'Visitor Profile')

@section('content')
@php
    // Reusable function to determine badge state for interactions
    function getInteractionBadgeState($interaction, $currentUserId) {
        // Check if interaction is completed
        if ($interaction->is_completed) {
            return 'updated';  // Completed = no one can work
        }
        
        // Check if scheduled and time not arrived
        if ($interaction->is_scheduled && $interaction->scheduled_date && now() < $interaction->scheduled_date) {
            return 'scheduled';  // Waiting for scheduled time
        }
        
        // Check if there are actual work remarks
        $hasWorkRemarks = false;
        if ($interaction->remarks && $interaction->remarks->count() > 0) {
            foreach ($interaction->remarks as $remark) {
                // Exclude system-generated remarks
                $isSystemRemark = strpos($remark->remark_text, '📅 Scheduled Assignment from') !== false ||
                                 strpos($remark->remark_text, 'Transferred from') !== false ||
                                 strpos($remark->remark_text, 'Completed & Transferred to') !== false;
                
                if (!$isSystemRemark) {
                    $hasWorkRemarks = true;
                    break;
                }
            }
        }
        
        // SPECIAL CASE: Transfer interactions
        if ($hasWorkRemarks) {
            // Check if this is a transfer case
            $isTransferCase = false;
            foreach ($interaction->remarks as $remark) {
                if (strpos($remark->remark_text, 'Transferred from') !== false || 
                    strpos($remark->remark_text, 'Completed & Transferred to') !== false) {
                    $isTransferCase = true;
                    break;
                }
            }
            
            if ($isTransferCase) {
                // For transfer cases, check if current user is the new assignee
                if ($interaction->meeting_with == $currentUserId) {
                    // Check if the new assignee has added their own work remarks
                    $newAssigneeHasWorkRemarks = false;
                    foreach ($interaction->remarks as $remark) {
                        // Check if this remark is from the new assignee and is not a system remark
                        if ($remark->added_by == $currentUserId) {
                            $isSystemRemark = strpos($remark->remark_text, '📅 Scheduled Assignment from') !== false ||
                                             strpos($remark->remark_text, 'Transferred from') !== false ||
                                             strpos($remark->remark_text, 'Completed & Transferred to') !== false;
                            
                            if (!$isSystemRemark) {
                                $newAssigneeHasWorkRemarks = true;
                                break;
                            }
                        }
                    }
                    
                    if ($newAssigneeHasWorkRemarks) {
                        // New assignee has added their remark, show "updated"
                        return 'updated';
                    } else {
                        // New assignee hasn't added their remark yet, show "pending"
                        return 'pending';
                    }
                } else {
                    // Original assignee sees "updated" (they're done)
                    return 'updated';
                }
            } else {
                // Regular case with work remarks
                return 'updated';
            }
        }
        
        // No work remarks = pending (work needed)
        return 'pending';
    }
    
    // Function to get file URL (server or drive)
    function getFileUrl($attachment) {
        // Check if there's a corresponding file management record
        $fileManagement = \App\Models\FileManagement::where('interaction_id', $attachment->interaction_id)
            ->where('original_filename', $attachment->original_filename)
            ->first();
            
        if ($fileManagement) {
            return $fileManagement->getFileUrlAttribute();
        }
        
        // Fallback to original Google Drive URL
        return $attachment->google_drive_url;
    }
    
    // Check if file is deleted
    function isFileDeleted($attachment) {
        $fileManagement = \App\Models\FileManagement::where('interaction_id', $attachment->interaction_id)
            ->where('original_filename', $attachment->original_filename)
            ->first();
            
        return $fileManagement && $fileManagement->deleted_at;
    }
    
    // Get file deletion info
    function getFileDeletionInfo($attachment) {
        $fileManagement = \App\Models\FileManagement::with('deletedBy')
            ->where('interaction_id', $attachment->interaction_id)
            ->where('original_filename', $attachment->original_filename)
            ->first();
            
        if ($fileManagement && $fileManagement->deleted_at) {
            return [
                'deleted_at' => $fileManagement->deleted_at,
                'deleted_by' => $fileManagement->deletedBy?->name ?? 'Admin',
                'deletion_reason' => $fileManagement->deletion_reason
            ];
        }
        
        return null;
    }
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div></div>
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="{{ route('staff.visitor-form', ['mobile' => $originalMobileNumber, 'name' => $visitor->name, 'action' => 'add_interaction', 'visitor_id' => $visitor->visitor_id]) }}" 
                   class="btn btn-paytm-success">
                    <i class="fas fa-plus me-2"></i>Add Interaction
                </a>
                <a href="{{ route('staff.visitor-form', ['mobile' => !empty($searchedMobile) ? $searchedMobile : $originalMobileNumber, 'action' => 'add_student']) }}" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-user-plus me-2"></i>Add Another Student
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
                                <td><strong>Course:</strong></td>
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

<!-- Phone Numbers Management (NEW FEATURE) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-phone me-2"></i>Phone Numbers
                    <small class="phone-counter">({{ $visitor->getTotalPhoneCount() }}/4)</small>
                </h5>
            </div>
            <div class="card-body">
                <div id="phoneNumbersContainer">
                    @php
                        $allPhones = $visitor->getAllPhoneNumbersMasked();
                    @endphp
                    
                    @if($allPhones->count() > 0)
                        <div class="phone-numbers-grid">
                            @foreach($allPhones as $phone)
                                <div class="phone-number-item">
                                    <div class="phone-info">
                                        <div class="phone-number">
                                            <i class="fas fa-phone"></i>
                                            <strong>{{ $phone['phone_number'] }}</strong>
                                        </div>
                                        <div class="phone-type">
                                            @if($phone['is_primary'])
                                                <span class="badge bg-success">Primary</span>
                                            @else
                                                <span class="badge bg-secondary">Additional</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$phone['is_primary'])
                                        <button type="button" class="remove-phone-btn" 
                                                data-phone-id="{{ $phone['id'] }}" 
                                                data-phone-number="{{ $phone['original_phone_number'] ?? $phone['phone_number'] }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-phone-slash text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No phone numbers available</p>
                        </div>
                    @endif
                    
                    @if($visitor->canAddMorePhoneNumbers())
                        <div class="text-center">
                            <button type="button" class="btn add-phone-btn" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                                <i class="fas fa-plus"></i>Add Phone Number
                            </button>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="alert alert-info border-0" style="background: rgba(57, 116, 252, 0.1); color: var(--paytm-primary);">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Maximum limit reached:</strong> 4 phone numbers per visitor
                            </div>
                        </div>
                    @endif
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
                            
                            // Sort each group by created_at desc (newest first)
                            foreach($groupedInteractions as $sessionId => $sessionInteractions) {
                                $groupedInteractions[$sessionId] = $sessionInteractions->sortByDesc('created_at');
                            }
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
                                                                    <small class="text-muted">
                                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($remark->created_at, 'M d, Y g:iA') }}
                                                                        @if($remark->meeting_duration)
                                                                            • Meeting Duration: {{ $remark->meeting_duration }} mins
                                                                        @endif
                                                                        by {{ $remark->addedBy?->name ?? 'Unknown' }}
                                                                    </small><br>
                                                                    {{ $remark->remark_text }}
                                                                    <!-- DEBUG: interaction_mode = {{ $remark->interaction_mode ?? 'NULL' }} -->
                                                                    @if($remark->interaction_mode)
                                                                        <br><small class="text-success fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $remark->interaction_mode }}</small>
                                                                    @else
                                                                        <!-- DEBUG: No interaction_mode found for remark {{ $remark->remark_id }} -->
                                                                        <br><small class="text-warning fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>NO MODE (ID: {{ $remark->remark_id }})</small>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                            
                                                            <!-- Show Add Remark button if assigned to current user and not completed -->
                                                            @if($interaction->meeting_with == auth()->user()->user_id && !$interaction->is_completed)
                                                                @php
                                                                    // Check if this is a scheduled assignment and if the scheduled time has passed
                                                                    $canAddRemark = true;
                                                                    if ($interaction->is_scheduled && $interaction->scheduled_date) {
                                                                        $canAddRemark = now() >= $interaction->scheduled_date;
                                                                    }
                                                                @endphp
                                                                
                                                                @if($canAddRemark)
                                                                    <div class="mt-2 modern-action-buttons">
                                                                        <button class="btn btn-sm btn-primary" onclick="showSimpleRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                            <i class="fas fa-comment me-1"></i>Add Remark
                                                                        </button>
                                                                        <button class="btn btn-sm btn-warning" onclick="showFocusedAssignModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                            <i class="fas fa-exchange-alt me-1"></i>Assign
                                                                        </button>
                                                                        <button class="btn btn-sm btn-success" onclick="showRescheduleModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                            <i class="fas fa-calendar-alt me-1"></i>Reschedule
                                                                        </button>
                                                                        <button class="btn btn-sm btn-outline-success" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                            <i class="fas fa-paperclip me-1"></i>Upload File
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    <!-- Only show "Scheduled for" text if no remarks have been added yet -->
                                                                    @if($interaction->remarks->count() == 0)
                                                                        <div class="mt-2 text-center">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-clock me-1"></i>
                                                                                Scheduled for {{ $interaction->scheduled_date ? \Carbon\Carbon::parse($interaction->scheduled_date)->format('M d, Y h:i A') : 'Invalid Date' }}
                                                                            </small>
                                                                            <div class="mt-1">
                                                                                <button class="btn btn-sm btn-outline-success" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                                    <i class="fas fa-paperclip me-1"></i>Upload File
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <!-- If remarks exist, just show the upload file button -->
                                                                        <div class="mt-2">
                                                                            <button class="btn btn-sm btn-outline-success" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                                <i class="fas fa-paperclip me-1"></i>Upload File
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            @elseif($interaction->meeting_with == auth()->user()->user_id)
                                                                <!-- Show Upload File button even after completion if assigned to current user -->
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-outline-success" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                        <i class="fas fa-paperclip me-1"></i>Upload File
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        @else
                                                            @if($interaction->meeting_with == auth()->user()->user_id)
                                                                @php
                                                                    $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                @endphp
                                                                @if($badgeState === 'scheduled')
                                                                    <span class="badge bg-secondary">
                                                                        <i class="fas fa-calendar-clock me-1"></i>Scheduled
                                                                    </span>
                                                                @elseif($badgeState === 'pending')
                                                                    <span class="badge bg-warning">
                                                                        <i class="fas fa-clock me-1"></i>Remark Pending
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-info">
                                                                        <i class="fas fa-comment me-1"></i>Remark Updated
                                                                    </span>
                                                                @endif
                                                                <div class="d-flex ms-2 modern-action-buttons">
                                                                    <button class="btn btn-sm btn-primary" onclick="showSimpleRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                        <i class="fas fa-comment me-1"></i>Add Remark
                                                                    </button>
                                                                    <button class="btn btn-sm btn-warning" onclick="showFocusedAssignModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                        <i class="fas fa-exchange-alt me-1"></i>Assign
                                                                    </button>
                                                                    <button class="btn btn-sm btn-success" onclick="showRescheduleModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                        <i class="fas fa-calendar-alt me-1"></i>Reschedule
                                                                    </button>
                                                                    <button class="btn btn-sm btn-outline-success" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                        <i class="fas fa-paperclip me-1"></i>Upload File
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <span class="badge bg-warning">
                                                                    Remark Pending - {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                                </span>
                                                            @endif
                                                        @endif
                                                        
                                                        <!-- File Attachments Section - Independent of remarks -->
                                                        @if($interaction->attachments && $interaction->attachments->count() > 0)
                                                            <div class="mt-3">
                                                                <h6 class="text-muted mb-2"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                                                                <div class="attachments-list">
                                                                    @foreach($interaction->attachments as $attachment)
                                                                        <div class="attachment-item d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-{{ $attachment->getFileIcon() }} me-2 text-primary"></i>
                                                                                <div>
                                                                                    <div class="fw-semibold">{{ $attachment->original_filename }}</div>
                                                                                    <small class="text-muted">
                                                                                        {{ $attachment->getFormattedFileSize() }} • 
                                                                                        {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($attachment->created_at, 'M d, Y g:iA') }} • 
                                                                                        by {{ $attachment->uploadedBy?->name ?? 'Unknown' }}
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="attachment-actions">
                                                                                @php
                                                                                    $deletionInfo = getFileDeletionInfo($attachment);
                                                                                @endphp
                                                                                @if($deletionInfo)
                                                                                    <span class="badge bg-danger" title="Deleted by {{ $deletionInfo['deleted_by'] }} on {{ $deletionInfo['deleted_at']->format('M d, Y') }}">
                                                                                        <i class="fas fa-trash me-1"></i>File Deleted
                                                                                    </span>
                                                                                    @if($deletionInfo['deletion_reason'])
                                                                                        <br><small class="text-muted">{{ $deletionInfo['deletion_reason'] }}</small>
                                                                                    @endif
                                                                                @else
                                                                                    <a href="{{ getFileUrl($attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                        <i class="fas fa-eye"></i> View
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
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
                                                                        @php
                                                                            $badgeState = getInteractionBadgeState($latestInteraction, auth()->user()->user_id);
                                                                            $labelText = ($badgeState === 'pending') ? 'Assigned to' : 'Attended by';
                                                                        @endphp
                                                                        <i class="fas fa-user me-1"></i>{{ $labelText }}: {{ $latestInteraction->meetingWith->name ?? 'Unknown' }}
                                                                    </small>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2 align-items-end flex-shrink-0">
                                                        @if($session->status === 'active')
                                                            @if($canComplete)
                                                                @php
                                                                    // Check if assigned user has updated their remark
                                                                    $badgeState = getInteractionBadgeState($latestInteraction, auth()->user()->user_id);
                                                                    $canShowSessionComplete = ($badgeState === 'updated');
                                                                @endphp
                                                                @if($canShowSessionComplete)
                                                                <button class="btn btn-sm btn-success modern-btn" onclick="completeSession({{ $session->session_id }})">
                                                                    <i class="fas fa-check me-1"></i>Complete
                                                                </button>
                                                                @endif
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
                                <div class="accordion accordion-paytm" id="sessionAccordion{{ $session->session_id }}">
                                    @foreach($sessionInteractions as $index => $interaction)
                                        <div class="accordion-item accordion-paytm-item">
                                                            <h2 class="accordion-header accordion-paytm-header" id="heading{{ $interaction->interaction_id }}">
                                                                <button class="accordion-button accordion-paytm-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $interaction->interaction_id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $interaction->interaction_id }}">
                                                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                        <div class="d-flex align-items-center flex-grow-1">
                                                                            <i class="fas fa-{{ $interaction->mode === 'In-Campus' ? 'building' : 'phone' }} me-2 text-primary"></i>
                                                                            <div class="interaction-info">
                                                                                <div class="fw-bold interaction-date" style="white-space: nowrap !important; font-family: monospace !important; display: inline-block !important;">{{ \App\Helpers\DateTimeHelper::formatIndianDateTime($interaction->created_at, 'Md g:iA') }}</div>
                                                                                <small class="text-muted interaction-meeting">
                                                                                    @php
                                                                                        $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                        $labelText = ($badgeState === 'pending') ? 'Assigned to' : 'Attended by';
                                                                                    @endphp
                                                                                    {{ $labelText }} - {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                                                    @if($interaction->meetingWith && $interaction->meetingWith->branch)
                                                                                        <span class="text-muted">({{ $interaction->meetingWith->branch->branch_name }})</span>
                                                                                    @endif
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex flex-column gap-1 flex-shrink-0 align-items-end">
                                                                            
                                                                            @php
                                                                                // Check if this is the ORIGINAL transfer interaction (where transfer was initiated)
                                                                                $isOriginalTransferInteraction = false;
                                                                                $transferredToName = null;
                                                                                $transferredToBranch = null;
                                                                                
                                                                                foreach($interaction->remarks as $remark) {
                                                                                    // Only show badge on interactions that have "Completed & Transferred to" remarks
                                                                                    // This indicates the ORIGINAL interaction where transfer was initiated
                                                                                    if (strpos($remark->remark_text, 'Completed & Transferred to') !== false) {
                                                                                        $isOriginalTransferInteraction = true;
                                                                                        
                                                                                        // Extract the new assignee's name from the transfer remark
                                                                                        // Format: "Completed & Transferred to [Name] ([Branch])"
                                                                                        if (preg_match('/Completed & Transferred to ([^(]+)\s*\(([^)]+)\)/', $remark->remark_text, $matches)) {
                                                                                            $transferredToName = trim($matches[1]);
                                                                                            $transferredToBranch = trim($matches[2]);
                                                                                        }
                                                                                        break;
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            
                                            {{-- ASSIGNED TO tag removed as requested --}}
                                                                            
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
                                                                                        @php
                                                                                            $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                        @endphp
                                                                                        @if($badgeState === 'scheduled')
                                                                                            <span class="badge bg-secondary px-2 py-1">
                                                                                                <i class="fas fa-calendar-clock me-1"></i>Scheduled
                                                                                            </span>
                                                                                        @elseif($badgeState === 'pending')
                                                                                            <span class="badge bg-warning px-2 py-1">
                                                                                                <i class="fas fa-clock me-1"></i>Remark Pending
                                                                                            </span>
                                                                                        @else
                                                                                            <span class="badge bg-info px-2 py-1">
                                                                                                <i class="fas fa-comment me-1"></i>Remark Updated
                                                                                            </span>
                                                                                            @php
                                                                                                $latestRemark = $interaction->remarks->sortByDesc('created_at')->first();
                                                                                            @endphp
                                                                                            @if($latestRemark && $latestRemark->meeting_duration)
                                                                                                <div class="badge bg-secondary px-2 py-1 mt-1" style="align-self: flex-end !important; margin-left: auto !important; margin-right: 0 !important; width: fit-content !important;">Duration: {{ $latestRemark->meeting_duration }} mins</div>
                                                                                            @endif
                                                                                        @endif
                                                                                    @endif
                                                                                @else
                                                                                    @php
                                                                                        $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                    @endphp
                                                                                    @if($badgeState === 'scheduled')
                                                                                        <span class="badge bg-secondary px-2 py-1">
                                                                                            <i class="fas fa-calendar-clock me-1"></i>Scheduled
                                                                                        </span>
                                                                                    @elseif($badgeState === 'pending')
                                                                                        <span class="badge bg-warning px-2 py-1">
                                                                                            <i class="fas fa-clock me-1"></i>Remark Pending
                                                                                        </span>
                                                                                    @else
                                                                                        <span class="badge bg-info px-2 py-1">
                                                                                            <i class="fas fa-comment me-1"></i>Remark Updated
                                                                                        </span>
                                                                                        @php
                                                                                            $latestRemark = $interaction->remarks->sortByDesc('created_at')->first();
                                                                                        @endphp
                                                                                        @if($latestRemark && $latestRemark->meeting_duration)
                                                                                            <div class="badge bg-secondary px-2 py-1 mt-1" style="align-self: flex-end !important; margin-left: auto !important; margin-right: 0 !important; width: fit-content !important;">Duration: {{ $latestRemark->meeting_duration }} mins</div>
                                                                                        @endif
                                                                                    @endif
                                                                                @endif
                                                                            @else
                                                                                @php
                                                                                    $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                @endphp
                                                                                @if($badgeState === 'scheduled')
                                                                                    <span class="badge bg-secondary px-2 py-1">
                                                                                        <i class="fas fa-calendar-clock me-1"></i>Scheduled
                                                                                    </span>
                                                                                @elseif($badgeState === 'pending')
                                                                                    <span class="badge bg-warning px-2 py-1">
                                                                                        <i class="fas fa-clock me-1"></i>Remark Pending
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-info px-2 py-1">
                                                                                        <i class="fas fa-comment me-1"></i>Remark Updated
                                                                                    </span>
                                                                                    @php
                                                                                        $latestRemark = $interaction->remarks->sortByDesc('created_at')->first();
                                                                                    @endphp
                                                                                    @if($latestRemark && $latestRemark->meeting_duration)
                                                                                        <div class="badge bg-secondary px-2 py-1 mt-1" style="align-self: flex-end !important; margin-left: auto !important; margin-right: 0 !important; width: fit-content !important;">Duration: {{ $latestRemark->meeting_duration }} mins</div>
                                                                                    @endif
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapse{{ $interaction->interaction_id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $interaction->interaction_id }}" data-bs-parent="#sessionAccordion{{ $session->session_id }}">
                                                                <div class="accordion-body">
                                                                    <div class="row">
                                                                        <!-- Simple Left-Right View - Full width -->
                                                                        <div class="col-12">
                                                                            @php
                                                                                // ===== SIMPLE CONTENT DISTRIBUTION (NO TIMELINE) =====
                                                                                
                                                                                // LEFT PANEL: LAST MESSAGE from assigner/scheduler
                                                                                if ($interaction->interaction_type === 'new') {
                                                                                    // For new interactions, show initial notes as LAST MESSAGE
                                                                                    $leftPanelMessage = $interaction->initial_notes ?: 'New interaction created';
                                                                                    $leftPanelTimestamp = $interaction->created_at;
                                                                                    // Convert interaction mode to display format
                                                                                    $leftPanelInteractionMode = match($interaction->mode) {
                                                                                        'in_campus' => 'In-Campus',
                                                                                        'out_campus' => 'Out-Campus',
                                                                                        'telephonic' => 'Telephonic',
                                                                                        default => 'In-Campus'
                                                                                    };
                                                                                } else {
                                                                                    // For assigned interactions, find the assignment/schedule message
                                                                                    $assignmentRemark = $interaction->remarks->first(function($remark) {
                                                                                        return strpos($remark->remark_text, 'Transferred from') !== false || 
                                                                                               strpos($remark->remark_text, '📅 Scheduled Assignment from') !== false ||
                                                                                               strpos($remark->remark_text, 'Completed & Transferred to') !== false;
                                                                                    });
                                                                                    
                                                                                    if ($assignmentRemark) {
                                                                                        // Extract notes from assignment message
                                                                                        $remarkParts = explode("\n", $assignmentRemark->remark_text);
                                                                                        foreach ($remarkParts as $part) {
                                                                                            if (strpos($part, 'Notes:') !== false) {
                                                                                                $leftPanelMessage = trim(str_replace('Notes:', '', $part));
                                                                                                $leftPanelTimestamp = $assignmentRemark->created_at;
                                                                                                $leftPanelInteractionMode = $assignmentRemark->interaction_mode;
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        
                                                                                        // If no "Notes:" found, show assignment details
                                                                                        if (!isset($leftPanelMessage)) {
                                                                                            $leftPanelMessage = 'Assignment details';
                                                                                            $leftPanelTimestamp = $assignmentRemark->created_at;
                                                                                            $leftPanelInteractionMode = $assignmentRemark->interaction_mode;
                                                                                        }
                                                                                    } else {
                                                                                        // If no assignment remark found, try to find the latest work remark from previous interaction
                                                                                        $latestWorkRemark = $interaction->remarks->first(function($remark) {
                                                                                            // Exclude system-generated remarks
                                                                                            return strpos($remark->remark_text, 'Transferred from') === false && 
                                                                                                   strpos($remark->remark_text, '📅 Scheduled Assignment from') === false &&
                                                                                                   strpos($remark->remark_text, 'Completed & Transferred to') === false;
                                                                                        });
                                                                                        
                                                                                        if ($latestWorkRemark) {
                                                                                            $leftPanelMessage = $latestWorkRemark->remark_text;
                                                                                            $leftPanelTimestamp = $latestWorkRemark->created_at;
                                                                                            $leftPanelInteractionMode = $latestWorkRemark->interaction_mode;
                                                                                            // DEBUG: Show what we found
                                                                                            echo "<!-- DEBUG: Found work remark: " . $latestWorkRemark->remark_id . " with mode: " . ($latestWorkRemark->interaction_mode ?? 'NULL') . " -->";
                                                                                        } else {
                                                                                            $leftPanelMessage = 'Assignment details';
                                                                                            $leftPanelTimestamp = $interaction->created_at;
                                                                                        }
                                                                                    }
                                                                                }
                                                                                
                                                                                // RIGHT PANEL: LATEST MESSAGE (current status)
                                                                                // COMPLETE LOGIC: Show the LATEST action taken by the current assignee
                                                                                
                                                                                // Get ALL remarks for this interaction, sorted by creation time (latest first)
                                                                                $allRemarks = $interaction->remarks->sortByDesc('created_at');
                                                                                
                                                                                $rightPanelMessage = 'No action taken yet';
                                                                                $rightPanelTimestamp = $interaction->created_at;
                                                                                
                                                                                // Check each remark in chronological order (latest first)
                                                                                foreach ($allRemarks as $remark) {
                                                                                    // Skip assignment/transfer remarks for RIGHT panel display
                                                                                    if (strpos($remark->remark_text, 'Transferred from') !== false || 
                                                                                        strpos($remark->remark_text, 'Completed & Transferred to') !== false ||
                                                                                        strpos($remark->remark_text, '📅 Scheduled Assignment from') !== false) {
                                                                                        continue; // Skip assignment remarks
                                                                                    }
                                                                                    
                                                                                    // This is a work remark - show it
                                                                                    $rightPanelMessage = $remark->remark_text;
                                                                                    $rightPanelTimestamp = $remark->created_at;
                                                                                    $rightPanelInteractionMode = $remark->interaction_mode;
                                                                                    break; // Show the latest work remark
                                                                                }
                                                                                
                                                                                // If no work remarks found, check if this person has assigned to someone else
                                                                                if ($rightPanelMessage === 'No action taken yet') {
                                                                                    // Look for assignment remarks where this person assigned to someone
                                                                                    $assignmentRemark = $interaction->remarks->first(function($remark) {
                                                                                        return strpos($remark->remark_text, 'Completed & Transferred to') !== false;
                                                                                    });
                                                                                    
                                                                                    if ($assignmentRemark) {
                                                                                        // Extract assignment message from "Notes:" section
                                                                                        if (strpos($assignmentRemark->remark_text, 'Notes:') !== false) {
                                                                                            $remarkParts = explode("\n", $assignmentRemark->remark_text);
                                                                                            foreach ($remarkParts as $part) {
                                                                                                if (strpos($part, 'Notes:') !== false) {
                                                                                                    $rightPanelMessage = trim(str_replace('Notes:', '', $part));
                                                                                                    $rightPanelTimestamp = $assignmentRemark->created_at;
                                                                                                    $rightPanelInteractionMode = $assignmentRemark->interaction_mode;
                                                                                                    break;
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            
                                                                            <div class="row">
                                                                                <!-- LEFT PANEL: Last Message -->
                                                                                <div class="col-md-6 mb-3">
                                                                                    <div class="modern-card">
                                                                                        <div class="card-header-modern">
                                                                                            <i class="fas fa-user me-2"></i>
                                                                                            <strong>
                                                                                                @if($interaction->interaction_type === 'new')
                                                                                                    Added By - {{ $interaction->createdBy->name ?? 'Unknown' }}
                                                                                                @else
                                                                                                    Assigned By - {{ $interaction->createdBy->name ?? 'Unknown' }}
                                                                                                @endif
                                                                                                @if($interaction->createdBy && $interaction->createdBy->branch)
                                                                                                    ({{ $interaction->createdBy->branch->branch_name }})
                                                                                                @endif
                                                                                            </strong>
                                                                                        </div>
                                                                                        <div class="card-body-modern">
                                                                                            <div class="highlighted-box notes-highlight">
                                                                                                {{ $leftPanelMessage }}
                                                                                                <!-- DEBUG LEFT: interaction_mode = {{ isset($leftPanelInteractionMode) ? $leftPanelInteractionMode : 'NOT SET' }} -->
                                                                                                @if(isset($leftPanelInteractionMode) && $leftPanelInteractionMode)
                                                                                                    <br><small class="text-success fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $leftPanelInteractionMode }}</small>
                                                                                                @else
                                                                                                    <br><small class="text-warning fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>NO MODE LEFT</small>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="remark-meta">
                                                                                                <div class="remark-time">
                                                                                                    <i class="fas fa-clock"></i>
                                                                                                    {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($leftPanelTimestamp, 'M d, Y g:iA') }}
                                                                                                    @if(isset($leftPanelRemark) && $leftPanelRemark->meeting_duration)
                                                                                                        • Meeting Duration: {{ $leftPanelRemark->meeting_duration }} mins
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <!-- RIGHT PANEL: Latest Message -->
                                                                                <div class="col-md-6 mb-3">
                                                                                    <div class="modern-card">
                                                                                        <div class="card-header-modern">
                                                                                            <i class="fas fa-user-check me-2"></i>
                                                                                            @php
                                                                                                $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                                $labelText = ($badgeState === 'pending') ? 'Assigned to' : 'Attended by';
                                                                                            @endphp
                                                                                            <strong>{{ $labelText }} - {{ $interaction->meetingWith->name ?? 'Unknown' }}
                                                                                            @if($interaction->meetingWith && $interaction->meetingWith->branch)
                                                                                                ({{ $interaction->meetingWith->branch->branch_name }})
                                                                                            @endif
                                                                                            </strong>
                                                                                        </div>
                                                                                        <div class="card-body-modern">
                                                                                            @if($rightPanelMessage && $rightPanelMessage !== 'No action taken yet')
                                                                                            @else
                                                                                                <div style="margin-top: -7px; margin-bottom: 0.125rem; height: 1.2rem;">
                                                                                                    <!-- Empty spacer to align content boxes -->
                                                                                                </div>
                                                                                            @endif
                                                                                            <div class="highlighted-box remarks-highlight">
                                                                                                @if($rightPanelMessage === 'No action taken yet')
                                                                                                    <div class="d-flex align-items-center text-warning">
                                                                                                        <i class="fas fa-clock me-2"></i>
                                                                                                        <span>{{ $rightPanelMessage }}</span>
                                                                                                    </div>
                                                                                                @else
                                                                                                    {{ $rightPanelMessage }}
                                                                                                    <!-- DEBUG RIGHT: interaction_mode = {{ isset($rightPanelInteractionMode) ? $rightPanelInteractionMode : 'NOT SET' }} -->
                                                                                                    @if(isset($rightPanelInteractionMode) && $rightPanelInteractionMode)
                                                                                                        <br><small class="text-success fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $rightPanelInteractionMode }}</small>
                                                                                                    @else
                                                                                                        <br><small class="text-warning fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>NO MODE RIGHT</small>
                                                                                                    @endif
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="remark-meta">
                                                                                                <div class="remark-time">
                                                                                                    <i class="fas fa-clock"></i>
                                                                                                    {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($rightPanelTimestamp, 'M d, Y g:iA') }}
                                                                                                    @if(isset($rightPanelRemark) && $rightPanelRemark->meeting_duration)
                                                                                                        • Meeting Duration: {{ $rightPanelRemark->meeting_duration }} mins
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <!-- Action Buttons for Expanded View -->
                                                                            @if($interaction->meeting_with == auth()->user()->user_id && !$interaction->is_completed)
                                                                                @php
                                                                                    $badgeState = getInteractionBadgeState($interaction, auth()->user()->user_id);
                                                                                    $canAddRemark = ($badgeState === 'pending');
                                                                                @endphp
                                                                                
                                                                                @if($canAddRemark)
                                                                                    <div class="mt-3 text-center">
                                                                                        <div class="modern-action-buttons justify-content-center">
                                                                                            <button class="btn btn-primary btn-sm" onclick="showSimpleRemarkModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                                                <i class="fas fa-comment me-1"></i>Add Remark
                                                                                            </button>
                                                                                            <button class="btn btn-warning btn-sm" onclick="showFocusedAssignModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                                                <i class="fas fa-exchange-alt me-1"></i>Assign
                                                                                            </button>
                                                                                            <button class="btn btn-success btn-sm" onclick="showRescheduleModal({{ $interaction->interaction_id }}, '{{ addslashes($interaction->name_entered) }}', '{{ addslashes($interaction->purpose) }}', '{{ addslashes($visitor->student_name) }}')">
                                                                                                <i class="fas fa-calendar-alt me-1"></i>Reschedule
                                                                                            </button>
                                                                                            <button class="btn btn-outline-success btn-sm" onclick="showFileUploadModal({{ $interaction->interaction_id }})">
                                                                                                <i class="fas fa-paperclip me-1"></i>Upload File
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            
                                                                            <!-- File Attachments Section for Session Interactions - Independent of remarks -->
                                                                            @if($interaction->attachments && $interaction->attachments->count() > 0)
                                                                                <div class="mt-3">
                                                                                    <h6 class="text-muted mb-2"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                                                                                    <div class="attachments-list">
                                                                                        @foreach($interaction->attachments as $attachment)
                                                                                            <div class="attachment-item d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <i class="fas fa-{{ $attachment->getFileIcon() }} me-2 text-primary"></i>
                                                                                                    <div>
                                                                                                        <div class="fw-semibold">{{ $attachment->original_filename }}</div>
                                                                                                        <small class="text-muted">
                                                                                                            {{ $attachment->getFormattedFileSize() }} • 
                                                                                                            {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($attachment->created_at, 'M d, Y g:iA') }} • 
                                                                                                            by {{ $attachment->uploadedBy?->name ?? 'Unknown' }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                <div class="attachment-actions">
                                                                                    @php
                                                                                        $deletionInfo = getFileDeletionInfo($attachment);
                                                                                    @endphp
                                                                                    @if($deletionInfo)
                                                                                        <span class="badge bg-danger" title="Deleted by {{ $deletionInfo['deleted_by'] }} on {{ $deletionInfo['deleted_at']->format('M d, Y') }}">
                                                                                            <i class="fas fa-trash me-1"></i>File Deleted
                                                                                        </span>
                                                                                        @if($deletionInfo['deletion_reason'])
                                                                                            <br><small class="text-muted">{{ $deletionInfo['deletion_reason'] }}</small>
                                                                                        @endif
                                                                                    @else
                                                                                        <a href="{{ getFileUrl($attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                            <i class="fas fa-eye"></i> View
                                                                                        </a>
                                                                                    @endif
                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Final Closer Remark - Only show for completed sessions in the latest interaction (first in the list) -->
                                                                    @if($index === 0 && $session->status === 'completed' && $session->outcome_notes)
                                                                        @php
                                                                            $isGoalAchieved = $session->outcome === 'success';
                                                                            $caseClosedClass = $isGoalAchieved ? 'case-closed-section' : 'case-closed-section-not-achieved';
                                                                            $headerClass = $isGoalAchieved ? 'case-closed-header' : 'case-closed-header-not-achieved';
                                                                            $iconClass = $isGoalAchieved ? 'fas fa-check-circle' : 'fas fa-times-circle';
                                                                        @endphp
                                                                        <div class="{{ $caseClosedClass }}">
                                                                            <div class="{{ $headerClass }}">
                                                                                <i class="{{ $iconClass }} me-2"></i>Case Closed
                                                                </div>
                                                                            <div class="case-closed-body">
                                                                                <div class="row">
                                                                                    <div class="col-md-8">
                                                                                        <h6 class="text-success mb-2">Final Closer Remark:</h6>
                                                                                        <div class="case-closed-remark">{{ $session->outcome_notes }}</div>
                                                                                    </div>
                                                                                    <div class="col-md-4">
                                                                                        <div class="case-closed-info">
                                                                                            <strong>Closed by:</strong> {{ $session->completer->name ?? 'Unknown' }}<br>
                                                                                            <strong>Branch:</strong> {{ $session->completer->branch->branch_name ?? 'No Branch' }}<br>
                                                                                            <strong>Date:</strong> {{ $session->completed_at ? \App\Helpers\DateTimeHelper::formatIndianDateTime($session->completed_at, 'M d, Y g:i A') : 'Unknown' }}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
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
                        <label for="meetingDuration" class="form-label">Meeting Duration (Minutes) <span class="text-danger">*</span></label>
                        <select class="form-select" id="meetingDuration" name="meeting_duration" required>
                            <option value="">Select Duration</option>
                            @for($i = 5; $i <= 180; $i += 5)
                                <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>{{ $i }} minutes</option>
                            @endfor
                        </select>
                        <div class="form-text">Select the duration of your meeting (5-180 minutes)</div>
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
                        <i class="fas fa-exchange-alt me-1"></i>Assign To Team Member
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Remark
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Simple Add Remark Modal -->
<div class="modal fade" id="simpleRemarkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-comment me-2"></i>Add Remark
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="simpleRemarkForm">
                <input type="hidden" id="simple_interaction_id" name="interaction_id">
                <div class="modal-body" style="padding: 1rem;">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Interaction Details:</strong>
                        <div id="simpleInteractionDetails" class="mt-1"></div>
                    </div>
                    
                    <!-- Mobile-Friendly: Group Duration and Mode side-by-side -->
                    <div class="row mb-2">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label for="simpleMeetingDuration" class="form-label small">Meeting Duration (Minutes) <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="simpleMeetingDuration" name="meeting_duration" required>
                                <option value="">Select Duration</option>
                                @for($i = 5; $i <= 180; $i += 5)
                                    <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>{{ $i }} minutes</option>
                                @endfor
                            </select>
                            <div class="form-text small">Select the duration of your meeting (5-180 minutes)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="simpleInteractionMode" class="form-label small">Interaction Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="simpleInteractionMode" name="interaction_mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus">In-Campus</option>
                                <option value="Out-Campus">Out-Campus</option>
                                <option value="Telephonic">Telephonic</option>
                            </select>
                            <div class="form-text small">Select the mode of interaction</div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="simpleRemarkText" class="form-label small">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="simpleRemarkText" name="remark_text" rows="3" 
                                  placeholder="Enter your remark/note about this interaction..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <div class="d-flex flex-column flex-md-row gap-2 w-100">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Remark
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Focused Assign to Team Member Modal -->
<div class="modal fade" id="focusedAssignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Assign To Team Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="focusedAssignForm">
                <input type="hidden" id="focused_assign_interaction_id" name="interaction_id">
                <div class="modal-body py-2">
                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="focusedTeamMember" class="form-label small">Select Team Member <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="focusedTeamMember" name="team_member_id" required>
                                <option value="">Choose a team member...</option>
                                @foreach(\App\Models\VmsUser::where('role', 'staff')->where('is_active', true)->where('user_id', '!=', auth()->id())->get() as $member)
                                    <option value="{{ $member->user_id }}">{{ $member->name }} ({{ $member->branch->branch_name ?? 'No Branch' }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Select a team member to transfer this interaction to</div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="focusedAssignMeetingDuration" class="form-label small">Meeting Duration <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="focusedAssignMeetingDuration" name="meeting_duration" required>
                                <option value="">Select Duration</option>
                                @for($i = 5; $i <= 180; $i += 5)
                                    <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>{{ $i }} mins</option>
                                @endfor
                            </select>
                            <div class="form-text small">Expected duration (5-180 mins)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="focusedAssignInteractionMode" class="form-label small">Interaction Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="focusedAssignInteractionMode" name="interaction_mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus">In-Campus</option>
                                <option value="Out-Campus">Out-Campus</option>
                                <option value="Telephonic">Telephonic</option>
                            </select>
                            <div class="form-text small">Select interaction mode</div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="focusedAssignNotes" class="form-label small">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="focusedAssignNotes" name="assignment_notes" rows="3" 
                                  placeholder="Add notes about why you're transferring this interaction..." required></textarea>
                        <div class="form-text small">Notes visible to assigned team member</div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-md-row gap-2 w-100">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-exchange-alt me-1"></i>Assign To Team Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign to Team Member Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Assign To Team Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                <input type="hidden" id="assign_interaction_id" name="interaction_id">
                <div class="modal-body py-2">
                    <div class="alert alert-info py-2 mb-2">
                        <div class="small">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Interaction Details:</strong>
                            <div id="assignInteractionDetails" class="mt-1"></div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="teamMember" class="form-label small">Select Team Member <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="teamMember" name="team_member_id" required>
                                <option value="">Choose a team member...</option>
                                @foreach(\App\Models\VmsUser::where('role', 'staff')->where('is_active', true)->where('user_id', '!=', auth()->id())->get() as $member)
                                    <option value="{{ $member->user_id }}">{{ $member->name }} ({{ $member->branch->branch_name ?? 'No Branch' }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Select a team member to transfer this interaction to</div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="assignMeetingDuration" class="form-label small">Meeting Duration <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="assignMeetingDuration" name="meeting_duration" required>
                                <option value="">Select Duration</option>
                                @for($i = 5; $i <= 180; $i += 5)
                                    <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>{{ $i }} mins</option>
                                @endfor
                            </select>
                            <div class="form-text small">Expected duration (5-180 mins)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="assignInteractionMode" class="form-label small">Interaction Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="assignInteractionMode" name="interaction_mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus">In-Campus</option>
                                <option value="Out-Campus">Out-Campus</option>
                                <option value="Telephonic">Telephonic</option>
                            </select>
                            <div class="form-text small">Select interaction mode</div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="assignNotes" class="form-label small">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="assignNotes" name="assignment_notes" rows="3" 
                                  placeholder="Add notes about why you're transferring this interaction..." required></textarea>
                        <div class="form-text small">Notes visible to assigned team member</div>
                    </div>
                    
                    <!-- Scheduling Section -->
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scheduleAssignment" name="schedule_assignment">
                            <label class="form-check-label small" for="scheduleAssignment">
                                <i class="fas fa-calendar-alt me-1"></i>Schedule for later date
                            </label>
                        </div>
                        <div class="form-text small">Check to schedule assignment for future date</div>
                    </div>
                    
                    <div class="mb-2" id="scheduleDateSection" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="scheduledDate" class="form-label small">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="scheduledDate" name="scheduled_date" 
                                       min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="scheduledHour" class="form-label small">Hour <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="scheduledHour" name="scheduled_hour">
                                    <option value="09">09 AM</option>
                                    <option value="10">10 AM</option>
                                    <option value="11">11 AM</option>
                                    <option value="12">12 PM</option>
                                    <option value="13">01 PM</option>
                                    <option value="14">02 PM</option>
                                    <option value="15" selected>03 PM</option>
                                    <option value="16">04 PM</option>
                                    <option value="17">05 PM</option>
                                    <option value="18">06 PM</option>
                                    <option value="19">07 PM</option>
                                    <option value="20">08 PM</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="scheduledMinute" class="form-label small">Minute <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="scheduledMinute" name="scheduled_minute">
                                    @for($i = 0; $i < 60; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-text small">Interaction will appear in assignee's tab on this date/time</div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-md-row gap-2 w-100">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-exchange-alt me-1"></i>Assign To Team Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt me-2"></i>Reschedule My Interaction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rescheduleForm">
                <input type="hidden" id="reschedule_interaction_id" name="interaction_id">
                <div class="modal-body py-2">
                    <div class="mb-2">
                        <label for="rescheduleTeamMember" class="form-label small">Assign To</label>
                        <input type="hidden" id="rescheduleTeamMember" name="team_member_id" value="{{ auth()->user()->user_id }}">
                        <div class="form-control-plaintext bg-light border rounded p-2 small">
                            <i class="fas fa-user me-2 text-primary"></i>
                            <strong>{{ auth()->user()->name }} ({{ auth()->user()->branch->branch_name ?? 'No Branch' }})</strong>
                            <small class="text-muted ms-2">- Assign to Myself</small>
                        </div>
                        <div class="form-text small">Reschedule your own interaction for a later date</div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="rescheduleDate" class="form-label small">Assignment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="rescheduleDate" name="scheduled_date" 
                                   min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="rescheduleInteractionMode" class="form-label small">Interaction Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="rescheduleInteractionMode" name="interaction_mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus">In-Campus</option>
                                <option value="Out-Campus">Out-Campus</option>
                                <option value="Telephonic">Telephonic</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="rescheduleHour" class="form-label small">Hour <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="rescheduleHour" name="scheduled_hour">
                                <option value="09">09 AM</option>
                                <option value="10">10 AM</option>
                                <option value="11">11 AM</option>
                                <option value="12">12 PM</option>
                                <option value="13">01 PM</option>
                                <option value="14">02 PM</option>
                                <option value="15" selected>03 PM</option>
                                <option value="16">04 PM</option>
                                <option value="17">05 PM</option>
                                <option value="18">06 PM</option>
                                <option value="19">07 PM</option>
                                <option value="20">08 PM</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="rescheduleMinute" class="form-label small">Minute <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="rescheduleMinute" name="scheduled_minute">
                                @for($i = 0; $i < 60; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="rescheduleNotes" class="form-label small">Remark/Note <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="rescheduleNotes" name="assignment_notes" rows="3" 
                                  placeholder="Required: Explain why you're rescheduling this interaction..." required></textarea>
                        <div class="form-text small">Required: Explain why you're rescheduling this interaction</div>
                    </div>
                    
                    <div class="alert alert-warning py-2 mb-2">
                        <div class="small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> This interaction will appear in your "Assigned to Me" tab on the scheduled date and time.
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-md-row gap-2 w-100">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-calendar-check me-1"></i>Reschedule
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
    
    /* Ensure badges don't overlap on mobile */
    .d-flex.flex-column.gap-1 .badge {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
        white-space: nowrap !important;
    }
    
    {{-- Transfer tag CSS removed as the tag was removed --}}
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

/* Simple Button Styling - Clean Bootstrap Look */
.modern-action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.modern-action-buttons .btn {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    font-weight: 400;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

/* Add Remark Button - Clean Blue */
.modern-action-buttons .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.modern-action-buttons .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    color: #fff;
}

/* Assign Button - Clean Orange */
.modern-action-buttons .btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.modern-action-buttons .btn-warning:hover {
    background-color: #ffca2c;
    border-color: #ffc720;
    color: #000;
}

/* Reschedule Button - Clean Green */
.modern-action-buttons .btn-success {
    background-color: #198754;
    border-color: #198754;
    color: #fff;
}

.modern-action-buttons .btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
    color: #fff;
}

/* Upload File Button - Clean Outline Green */
.modern-action-buttons .btn-outline-success {
    background-color: transparent;
    border-color: #198754;
    color: #198754;
}

.modern-action-buttons .btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
    color: #fff;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modern-action-buttons {
        gap: 0.375rem;
    }
    
    .modern-action-buttons .btn {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
    }
}

@media (max-width: 480px) {
    .modern-action-buttons {
        gap: 0.25rem;
    }
    
    .modern-action-buttons .btn {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        flex: 1;
        max-width: calc(50% - 0.125rem);
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

// Show Simple Remark Modal (New)
function showSimpleRemarkModal(interactionId, visitorName, purpose, studentName) {
    try {
        document.getElementById('simple_interaction_id').value = interactionId;
        
        // Show student name if available, otherwise show contact person
        const displayName = studentName && studentName.trim() !== '' ? 
            `<strong>Student Name:</strong> ${studentName}` : 
            `<strong>Contact Person:</strong> ${visitorName}`;
        
        document.getElementById('simpleInteractionDetails').innerHTML = `
            ${displayName}<br>
            <strong>Purpose:</strong> ${purpose}
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('simpleRemarkModal'));
        modal.show();
    } catch (error) {
        console.error('Error opening Simple Remark Modal:', error);
        alert('Error opening modal. Please try again.');
    }
}

// Show Focused Assign Modal (New)
function showFocusedAssignModal(interactionId, visitorName, purpose, studentName) {
    console.log('showFocusedAssignModal called with:', {interactionId, visitorName, purpose, studentName});
    
    try {
        document.getElementById('focused_assign_interaction_id').value = interactionId;
        console.log('Set interaction ID:', interactionId);
        
        const modal = new bootstrap.Modal(document.getElementById('focusedAssignModal'));
        console.log('Created modal instance:', modal);
        modal.show();
        console.log('Modal show() called');
    } catch (error) {
        console.error('Error opening Focused Assign Modal:', error);
        alert('Error opening modal. Please try again. Error: ' + error.message);
    }
}

// Fetch default interaction mode for modal dropdowns
function fetchDefaultInteractionMode(interactionId, selectElementId) {
    fetch(`/staff/interaction/${interactionId}/default-mode`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.default_mode) {
                const selectElement = document.getElementById(selectElementId);
                if (selectElement) {
                    selectElement.value = data.default_mode;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching default interaction mode:', error);
            // Don't prevent modal from opening if this fails
        });
}

// Show Reschedule Modal (Self-Reschedule Only)
function showRescheduleModal(interactionId, visitorName, purpose, studentName) {
    try {
        document.getElementById('reschedule_interaction_id').value = interactionId;
        
        // Fetch default interaction mode (non-blocking)
        fetchDefaultInteractionMode(interactionId, 'rescheduleInteractionMode');
        
        const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
        modal.show();
    } catch (error) {
        console.error('Error opening Reschedule Modal:', error);
        alert('Error opening modal. Please try again.');
    }
}

// Show Assign Modal
function showAssignModal() {
    // Get the current interaction ID from the remark modal
    const interactionId = document.getElementById('interaction_id').value;
    const interactionDetails = document.getElementById('interactionDetails').innerHTML;
    
    // Set the interaction ID for assignment
    document.getElementById('assign_interaction_id').value = interactionId;
    document.getElementById('assignInteractionDetails').innerHTML = interactionDetails;
    
    // NEW: Copy entire remark text to transfer notes field (time saver)
    const remarkText = document.getElementById('remarkText').value.trim();
    if (remarkText) {
        document.getElementById('assignNotes').value = remarkText;
    }
    
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

// Handle Simple Remark Form Submission (New)
document.getElementById('simpleRemarkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const interactionId = document.getElementById('simple_interaction_id').value;
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
        if (response.ok) {
            return response.json().catch(() => {
                return { success: true };
            });
        } else {
            return response.json().catch(() => {
                return { success: false, message: 'Server error' };
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert('Remark added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('simpleRemarkModal')).hide();
            this.reset();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add remark'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Remark may have been saved. Refreshing page...');
        window.location.reload();
    });
});

// Handle Focused Assign Form Submission (New)
document.getElementById('focusedAssignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const interactionId = formData.get('interaction_id');
    
    console.log('Submitting assign form with interaction ID:', interactionId);
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch(`{{ url('staff/assign-interaction') }}/${interactionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Interaction transferred successfully! Your interaction has been completed and a new interaction has been created for the assigned team member.');
            bootstrap.Modal.getInstance(document.getElementById('focusedAssignModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to transfer interaction'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to assign interaction - ' + error.message);
    });
});

// Handle Reschedule Form Submission (New)
document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const interactionId = formData.get('interaction_id');
    
    // Add scheduling flag for reschedule
    formData.append('schedule_assignment', '1');
    
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
            alert('Interaction rescheduled successfully! The interaction will appear in assignee\'s tab on the scheduled date and time.');
            bootstrap.Modal.getInstance(document.getElementById('rescheduleModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to reschedule interaction'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to reschedule interaction');
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
            
            // Pre-fill outcome notes with latest remark
            if (data.session.latest_remark) {
                document.getElementById('outcome_notes').value = data.session.latest_remark;
            }
            
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

// ========== PHONE NUMBER MANAGEMENT (NEW FEATURE) ==========

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add Phone Number Modal Handler
    const addPhoneForm = document.getElementById('addPhoneForm');
    
    if (addPhoneForm) {
        addPhoneForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phoneNumber = document.getElementById('newPhoneNumber').value;
    const visitorId = {{ $visitor->visitor_id }};
    const url = `/staff/visitor/${visitorId}/add-phone`;
    
    
    const formData = new FormData();
    formData.append('phone_number', phoneNumber);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Phone number added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('addPhoneModal')).hide();
            location.reload();
        } else {
            // Show the actual error message from server
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        alert('Error: Failed to add phone number - Please try again');
    });
        });
    }

    // Remove Phone Number Handler
    const removeButtons = document.querySelectorAll('.remove-phone-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const phoneId = this.getAttribute('data-phone-id');
            const phoneNumber = this.getAttribute('data-phone-number');
            const visitorId = {{ $visitor->visitor_id }};
            
            
            if (confirm(`Are you sure you want to remove phone number ${phoneNumber}?`)) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'DELETE');
                
                fetch(`/staff/visitor/${visitorId}/remove-phone/${phoneId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Phone number removed successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: Failed to remove phone number');
                });
            }
        });
    });

    // Handle schedule assignment checkbox
    const scheduleCheckbox = document.getElementById('scheduleAssignment');
    const scheduleDateSection = document.getElementById('scheduleDateSection');
    const scheduledDateInput = document.getElementById('scheduledDate');
    
    if (scheduleCheckbox && scheduleDateSection) {
        scheduleCheckbox.addEventListener('change', function() {
            if (this.checked) {
                scheduleDateSection.style.display = 'block';
                scheduledDateInput.required = true;
            } else {
                scheduleDateSection.style.display = 'none';
                scheduledDateInput.required = false;
                scheduledDateInput.value = '{{ date("Y-m-d") }}';
            }
        });
    }
});

// File Upload Modal Functions
function showFileUploadModal(interactionId) {
    try {
        document.getElementById('upload_interaction_id').value = interactionId;
        // Only clear file info display, not the input itself
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('uploadBtn').disabled = true;
        const modal = new bootstrap.Modal(document.getElementById('fileUploadModal'));
        modal.show();
    } catch (error) {
        console.error('Error opening File Upload Modal:', error);
        alert('Error opening modal. Please try again.');
    }
}

function handleFileSelect(input) {
    const file = input.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileInfo.style.display = 'block';
        uploadBtn.disabled = false;
    } else {
        fileInfo.style.display = 'none';
        uploadBtn.disabled = true;
    }
}

function submitFileUpload() {
    const fileInput = document.getElementById('fileInput');
    const interactionId = document.getElementById('upload_interaction_id').value;
    
    if (!fileInput.files[0]) {
        alert('Please select a file to upload.');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('interaction_id', interactionId);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Disable upload button and show loading
    const uploadBtn = document.getElementById('uploadBtn');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';
    
    // Upload file
    fetch('/staff/upload-attachment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('fileUploadModal')).hide();
            alert('File uploaded successfully!');
            window.location.reload(true);
        } else {
            alert('Upload failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed: Network error');
    })
    .finally(() => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
    });
}
</script>

<!-- File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileUploadModalLabel">
                    <i class="fas fa-paperclip me-2"></i>Upload File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="fileUploadForm" enctype="multipart/form-data">
                <input type="hidden" id="upload_interaction_id" name="interaction_id">
                <div class="modal-body py-2">
                    <div class="mb-3">
                        <div class="upload-area border border-dashed rounded p-4 text-center" id="uploadArea" style="min-height: 150px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h6>Drag & Drop Files Here</h6>
                            <p class="text-muted mb-3">or click to browse</p>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-folder-open me-1"></i>Browse Files
                            </button>
                        </div>
                        <input type="file" id="fileInput" name="file" style="display: none;" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp3,.wav" onchange="handleFileSelect(this)">
                        <div id="fileInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-file me-2"></i>
                                <strong id="fileName"></strong>
                                <small class="text-muted ms-2" id="fileSize"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="small">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>File Limits:</strong>
                            <ul class="mb-1 mt-1">
                                <li><strong>PDF:</strong> 5MB max</li>
                                <li><strong>Images:</strong> 2MB max</li>
                                <li><strong>Audio:</strong> 10MB max</li>
                            </ul>
                            <strong>Supported:</strong> PDF, JPG, PNG, WebP, MP3, WAV
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-md-row gap-2 w-100">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="uploadBtn" disabled>
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Phone Number Modal (NEW FEATURE) -->
<div class="modal fade" id="addPhoneModal" tabindex="-1" aria-labelledby="addPhoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPhoneModalLabel">
                    <i class="fas fa-phone me-2"></i>Add Phone Number
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPhoneForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newPhoneNumber" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">+91</span>
                            <input type="tel" class="form-control" id="newPhoneNumber" name="phone_number" 
                                   placeholder="Enter 10-digit mobile number" required maxlength="10" 
                                   pattern="[0-9]{10}" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            This number will be searchable and linked to the same visitor profile.
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Note:</strong> Maximum 4 phone numbers per visitor. 
                        Current count: <span class="badge bg-primary">{{ $visitor->getTotalPhoneCount() }}/4</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-paytm-primary">
                        <i class="fas fa-plus me-1"></i>Add Phone Number
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('staff.modals.file-upload')

<script>
// Force modal centering on show
document.addEventListener('DOMContentLoaded', function() {
    // Get all modals
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Force center positioning with mobile responsiveness
            const dialog = modal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.position = 'fixed';
                dialog.style.top = '50%';
                dialog.style.left = '50%';
                dialog.style.transform = 'translate(-50%, -50%)';
                dialog.style.margin = '0';
                
                // Mobile responsive sizing
                if (window.innerWidth <= 375) {
                    dialog.style.maxWidth = '95vw';
                    dialog.style.width = '95vw';
                    dialog.style.maxHeight = '95vh';
                } else if (window.innerWidth <= 768) {
                    dialog.style.maxWidth = '90vw';
                    dialog.style.width = '90vw';
                    dialog.style.maxHeight = '90vh';
                } else {
                    dialog.style.maxWidth = '500px';
                    dialog.style.width = '90%';
                }
            }
        });
        
        modal.addEventListener('shown.bs.modal', function() {
            // Additional centering after modal is shown
            const dialog = modal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.position = 'fixed';
                dialog.style.top = '50%';
                dialog.style.left = '50%';
                dialog.style.transform = 'translate(-50%, -50%)';
                dialog.style.margin = '0';
                
                // Mobile responsive sizing
                if (window.innerWidth <= 375) {
                    dialog.style.maxWidth = '95vw';
                    dialog.style.width = '95vw';
                    dialog.style.maxHeight = '95vh';
                } else if (window.innerWidth <= 768) {
                    dialog.style.maxWidth = '90vw';
                    dialog.style.width = '90vw';
                    dialog.style.maxHeight = '90vh';
                } else {
                    dialog.style.maxWidth = '500px';
                    dialog.style.width = '90%';
                }
            }
        });
    });
});

// Handle window resize for mobile orientation changes
window.addEventListener('resize', function() {
    const openModals = document.querySelectorAll('.modal.show');
    openModals.forEach(modal => {
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.position = 'fixed';
            dialog.style.top = '50%';
            dialog.style.left = '50%';
            dialog.style.transform = 'translate(-50%, -50%)';
            dialog.style.margin = '0';
            
            // Mobile responsive sizing
            if (window.innerWidth <= 768) {
                dialog.style.maxWidth = '95vw';
                dialog.style.width = '95vw';
                dialog.style.maxHeight = '90vh';
            } else if (window.innerWidth <= 375) {
                dialog.style.maxWidth = '98vw';
                dialog.style.width = '98vw';
                dialog.style.maxHeight = '95vh';
            } else {
                dialog.style.maxWidth = '500px';
                dialog.style.width = '90%';
            }
        }
    });
});
</script>

@endsection
