@extends('layouts.app')

@section('title', 'Visitor Search - Task Book')
@section('page-title', 'Visitor Search')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <!-- Mobile Number Search -->
        <div class="card-paytm paytm-fade-in mb-4 modern-mobile-search">
            <div class="mobile-search-header">
                <div class="search-header-content">
                    <div class="search-title-section">
                        <h4 class="search-main-title">
                            <i class="fas fa-mobile-alt me-3"></i>Quick Search
                        </h4>
                        <p class="search-main-subtitle">Find logs instantly by mobile number</p>
                    </div>
                    <div class="search-stats">
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span>Fast Lookup</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mobile-search-body">
                <form method="POST" action="{{ route('staff.search-visitor') }}" class="modern-search-form">
                    @csrf
                    <div class="search-input-section">
                        <div class="modern-input-group">
                            <div class="mobile-header-section">
                                <i class="fas fa-phone me-2"></i>
                                <span class="mobile-label-text">Mobile Number</span>
                            </div>
                            <input type="tel" class="modern-mobile-input" id="mobile_number" name="mobile_number" 
                                   required maxlength="10" placeholder="+91 Enter 10-digit mobile number" 
                                   value="{{ old('mobile_number', $prefilledMobile ?? '') }}"
                                   inputmode="numeric" pattern="[0-9]{10}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                            <button type="submit" class="modern-search-btn">
                                <i class="fas fa-search"></i>
                                <span class="btn-text">Search</span>
                            </button>
                        </div>
                        @error('mobile_number')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <div class="search-info-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-search-plus"></i>
                                <span>Search existing logs</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-plus-circle"></i>
                                <span>Create new log if not found</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-history"></i>
                                <span>View complete interaction history</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Advanced Search -->
        <div class="card-paytm paytm-fade-in modern-advanced-search">
            <div class="advanced-search-toggle-section">
                <div class="toggle-content">
                    <div class="toggle-text">
                        <h5 class="toggle-title">
                            <i class="fas fa-search-plus me-2"></i>Advanced Search
                        </h5>
                        <p class="toggle-subtitle">Don't have the mobile number? Search by name, course, or visit details</p>
                    </div>
                    <button class="modern-toggle-btn" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#advancedSearchCollapse" aria-expanded="false" aria-controls="advancedSearchCollapse">
                        <span class="toggle-text-btn">Show Options</span>
                        <i class="fas fa-chevron-down toggle-icon" id="collapseIcon"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="advancedSearchCollapse">
                <div class="advanced-search-body">
                    <div class="search-info-banner">
                        <div class="info-content">
                            <i class="fas fa-lightbulb info-icon"></i>
                            <span><strong>Tip:</strong> Fill any field(s) below - we'll show results that match any of your criteria!</span>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('staff.advanced-search') }}" id="advancedSearchForm">
                        @csrf
                        
                        <!-- Row 1: Names -->
                        <div class="row mb-3">
                            <div class="col-md-4 col-12 mb-3 mb-md-0">
                                <label for="student_name" class="form-label">
                                    <i class="fas fa-user-graduate me-1"></i>Student Name
                                </label>
                                <input type="text" class="form-control-paytm" id="student_name" name="student_name" 
                                       placeholder="Enter student name" value="{{ old('student_name') }}">
                            </div>
                            <div class="col-md-4 col-12 mb-3 mb-md-0">
                                <label for="father_name" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>Father's Name
                                </label>
                                <input type="text" class="form-control-paytm" id="father_name" name="father_name" 
                                       placeholder="Enter father's name" value="{{ old('father_name') }}">
                            </div>
                            <div class="col-md-4 col-12">
                                <label for="contact_person" class="form-label">
                                    <i class="fas fa-user me-1"></i>Contact Person
                                </label>
                                <input type="text" class="form-control-paytm" id="contact_person" name="contact_person" 
                                       placeholder="Enter contact person name" value="{{ old('contact_person') }}">
                            </div>
                        </div>

                        <!-- Row 2: Purpose and Course -->
                        <div class="row mb-3">
                            <div class="col-md-6 col-12 mb-3 mb-md-0">
                                <label for="purpose" class="form-label">
                                    <i class="fas fa-bullseye me-1"></i>Purpose of Visit
                                </label>
                                <select class="form-select-paytm" id="purpose" name="purpose">
                                    <option value="">Select Purpose</option>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}" {{ old('purpose') == $tag->id ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="course_id" class="form-label">
                                    <i class="fas fa-book me-1"></i>Course Interest
                                </label>
                                <select class="form-select-paytm" id="course_id" name="course_id">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}" {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                            {{ $course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Date Range -->
                        <div class="row mb-4">
                            <div class="col-md-6 col-12 mb-3 mb-md-0">
                                <label for="date_from" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>Visit Date From
                                </label>
                                <input type="date" class="form-control-paytm" id="date_from" name="date_from" 
                                       value="{{ old('date_from') }}">
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="date_to" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Visit Date To
                                </label>
                                <input type="date" class="form-control-paytm" id="date_to" name="date_to" 
                                       value="{{ old('date_to') }}">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-paytm-secondary" id="clearAdvancedSearch">
                                <i class="fas fa-eraser me-2"></i>Clear All
                            </button>
                            <button type="submit" class="btn btn-paytm-primary">
                                <i class="fas fa-search me-2"></i>Advanced Search
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@section('styles')
<!-- Paytm styling applied via paytm-theme.css -->
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle collapse icon and text
    const collapseElement = document.getElementById('advancedSearchCollapse');
    const collapseIcon = document.getElementById('collapseIcon');
    const toggleTextBtn = document.querySelector('.toggle-text-btn');
    
    collapseElement.addEventListener('show.bs.collapse', function() {
        collapseIcon.classList.remove('fa-chevron-down');
        collapseIcon.classList.add('fa-chevron-up');
        toggleTextBtn.textContent = 'Hide Options';
    });
    
    collapseElement.addEventListener('hide.bs.collapse', function() {
        collapseIcon.classList.remove('fa-chevron-up');
        collapseIcon.classList.add('fa-chevron-down');
        toggleTextBtn.textContent = 'Show Options';
    });
    
    // Clear advanced search form
    document.getElementById('clearAdvancedSearch').addEventListener('click', function() {
        const form = document.getElementById('advancedSearchForm');
        form.reset();
        
        // Show confirmation
        const button = this;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-2"></i>Cleared!';
        button.classList.remove('btn-paytm-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-paytm-secondary');
        }, 1500);
    });
    
    // Form validation
    document.getElementById('advancedSearchForm').addEventListener('submit', function(e) {
        const formData = new FormData(this);
        let hasData = false;
        
        for (let [key, value] of formData.entries()) {
            if (key !== '_token' && value.trim() !== '') {
                hasData = true;
                break;
            }
        }
        
        if (!hasData) {
            e.preventDefault();
            alert('Please fill at least one search field.');
            return false;
        }
    });
    
    // Auto-expand if there are old values (after validation errors)
    @if(old('student_name') || old('father_name') || old('contact_person') || old('purpose') || old('course_id') || old('date_from') || old('date_to'))
        const collapse = new bootstrap.Collapse(document.getElementById('advancedSearchCollapse'), {
            show: true
        });
    @endif
});
</script>
@endsection
