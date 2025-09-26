@extends('layouts.app')

@section('title', 'Select Student - Task Book')
@section('page-title', 'Multiple Students Found')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Select Student
                </h5>
                <small class="text-muted">
                    Phone number <strong>{{ $phoneNumber }}</strong> is associated with multiple students
                </small>
            </div>
            <div class="card-paytm-body">
                
                <!-- Students List -->
                <div class="student-selection-list">
                    @foreach($students as $index => $student)
                        <div class="student-option {{ $student['phone_type'] === 'primary' ? 'primary-student' : 'additional-student' }}" 
                             onclick="selectStudent('{{ $student['visitor_id'] }}')">
                            
                            <div class="student-info">
                                <div class="student-header">
                                    <h6 class="student-name">
                                        @if($student['student_name'])
                                            {{ $student['student_name'] }}
                                        @else
                                            {{ $student['name'] }}
                                        @endif
                                        
                                        <!-- Priority Badge -->
                                        @if($student['phone_type'] === 'primary')
                                            <span class="badge bg-success ms-2">Primary Contact</span>
                                        @else
                                            <span class="badge bg-warning ms-2">Additional Contact</span>
                                        @endif
                                    </h6>
                                    
                                    <!-- Contact Person -->
                                    <small class="text-muted">
                                        Contact: <strong>{{ $student['name'] }}</strong>
                                        @if($student['father_name'])
                                            | Father: {{ $student['father_name'] }}
                                        @endif
                                    </small>
                                </div>
                                
                                <div class="student-details">
                                    <!-- Course Info -->
                                    <div class="course-info">
                                        @if($student['course'])
                                            <span class="badge bg-primary">{{ $student['course']['course_name'] }}</span>
                                        @else
                                            <span class="badge bg-secondary">No Course</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Interaction Count -->
                                    <div class="interaction-info">
                                        <span class="interaction-count {{ $student['interaction_count'] >= 3 ? 'high' : ($student['interaction_count'] >= 1 ? 'medium' : 'low') }}">
                                            {{ $student['interaction_count'] }}
                                        </span>
                                        <small>interactions</small>
                                    </div>
                                </div>
                                
                                <!-- Latest Interaction -->
                                @if($student['latest_interaction'])
                                    <div class="latest-interaction">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Last interaction: {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($student['latest_interaction']['created_at'], 'M d, Y') }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="selection-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Add New Student Option -->
                <div class="add-new-student-section">
                    <div class="divider">
                        <span>OR</span>
                    </div>
                    
                    <div class="add-new-option" onclick="addNewStudent()">
                        <div class="add-new-content">
                            <i class="fas fa-plus-circle me-3"></i>
                            <div>
                                <h6>Add New Student</h6>
                                <small class="text-muted">Create a new student profile with this phone number</small>
                            </div>
                        </div>
                        <div class="selection-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<style>
.student-selection-list {
    margin-bottom: 2rem;
}

.student-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.student-option:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.student-option.primary-student {
    border-left: 4px solid #28a745;
}

.student-option.additional-student {
    border-left: 4px solid #ffc107;
}

.student-info {
    flex: 1;
}

.student-header {
    margin-bottom: 0.75rem;
}

.student-name {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.student-details {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.interaction-count {
    display: inline-block;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
    font-size: 0.8rem;
    font-weight: bold;
    color: white;
}

.interaction-count.high {
    background-color: #28a745;
}

.interaction-count.medium {
    background-color: #ffc107;
    color: #212529;
}

.interaction-count.low {
    background-color: #dc3545;
}

.selection-arrow {
    color: #6c757d;
    font-size: 1.2rem;
}

.student-option:hover .selection-arrow {
    color: #007bff;
}

.divider {
    text-align: center;
    margin: 2rem 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e9ecef;
}

.divider span {
    background: white;
    padding: 0 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.add-new-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border: 2px dashed #ced4da;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.add-new-option:hover {
    border-color: #28a745;
    background: #d4edda;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.add-new-content {
    display: flex;
    align-items: center;
    color: #495057;
}

.add-new-content i {
    color: #28a745;
    font-size: 1.5rem;
}

.add-new-content h6 {
    margin: 0;
    color: #2c3e50;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .student-option {
        padding: 1rem;
    }
    
    .student-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .add-new-content {
        flex-direction: column;
        text-align: center;
    }
    
    .add-new-content i {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
}
</style>

<script>
function selectStudent(visitorId) {
    // Redirect to the selected student's profile
    window.location.href = "{{ route('staff.visitor-profile', '') }}/" + visitorId;
}

function addNewStudent() {
    // Redirect to visitor form with pre-filled mobile number
    window.location.href = "{{ route('staff.visitor-form') }}?mobile={{ $phoneNumber }}";
}

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    const options = document.querySelectorAll('.student-option, .add-new-option');
    let currentIndex = -1;
    
    // Find currently focused option
    options.forEach((option, index) => {
        if (option.classList.contains('keyboard-focus')) {
            currentIndex = index;
        }
    });
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        currentIndex = (currentIndex + 1) % options.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        currentIndex = currentIndex <= 0 ? options.length - 1 : currentIndex - 1;
    } else if (e.key === 'Enter' && currentIndex >= 0) {
        e.preventDefault();
        options[currentIndex].click();
    }
    
    // Update focus
    options.forEach((option, index) => {
        if (index === currentIndex) {
            option.classList.add('keyboard-focus');
            option.focus();
        } else {
            option.classList.remove('keyboard-focus');
        }
    });
});
</script>
@endsection
