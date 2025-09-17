@extends('layouts.app')

@section('title', 'Visitor Search - Log Book')
@section('page-title', 'Visitor Search')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <!-- Mobile Number Search -->
        <div class="card-paytm paytm-fade-in mb-4">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-mobile-alt me-2"></i>Search Log by Mobile Number
                </h5>
            </div>
            <div class="card-paytm-body">
                <form method="POST" action="{{ route('staff.search-visitor') }}">
                    @csrf
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--paytm-primary); color: white; border-color: var(--paytm-primary);">+91</span>
                            <input type="tel" class="form-control-paytm" id="mobile_number" name="mobile_number" 
                                   required maxlength="10" placeholder="Enter the mobile number to search for Log or Enter New Log" 
                                   value="{{ old('mobile_number', $prefilledMobile ?? '') }}"
                                   inputmode="numeric" pattern="[0-9]{10}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                        </div>
                        @error('mobile_number')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-paytm-primary">
                            <i class="fas fa-search me-2"></i>Search Log
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Advanced Search -->
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-search-plus me-2"></i>Advanced Search Options
                </h5>
                <button class="btn btn-sm btn-outline-light ms-auto" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#advancedSearchCollapse" aria-expanded="false" aria-controls="advancedSearchCollapse">
                    <i class="fas fa-chevron-down" id="collapseIcon"></i>
                </button>
            </div>
            <div class="collapse" id="advancedSearchCollapse">
                <div class="card-paytm-body">
                    <div class="advanced-search-intro mb-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Don't know the mobile number?</strong> Use these fields to find visitors by their details.
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
    // Toggle collapse icon
    const collapseElement = document.getElementById('advancedSearchCollapse');
    const collapseIcon = document.getElementById('collapseIcon');
    
    collapseElement.addEventListener('show.bs.collapse', function() {
        collapseIcon.classList.remove('fa-chevron-down');
        collapseIcon.classList.add('fa-chevron-up');
    });
    
    collapseElement.addEventListener('hide.bs.collapse', function() {
        collapseIcon.classList.remove('fa-chevron-up');
        collapseIcon.classList.add('fa-chevron-down');
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
