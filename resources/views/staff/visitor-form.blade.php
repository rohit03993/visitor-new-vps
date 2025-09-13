@extends('layouts.app')

@section('title', 'Add Visitor - VMS')
@section('page-title', 'Add New Visitor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Visitor Details
                </h5>
            </div>
            <div class="card-paytm-body">
                <form id="visitorForm" method="POST" action="{{ route('staff.store-visitor') }}">
                    @csrf
                    
                    <!-- Mobile Number -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="mobile_number" class="form-label-paytm">Mobile Number *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--paytm-primary); color: white; border-color: var(--paytm-primary);">+91</span>
                                <input type="tel" class="form-control-paytm" id="mobile_number" name="mobile_number" 
                                       required maxlength="10" placeholder="Enter 10-digit mobile number"
                                       inputmode="numeric" pattern="[0-9]{10}" value="{{ $prefilledMobile ?? '' }}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                                <!-- Hidden field to store original mobile number for form submission -->
                                @if(isset($originalMobileNumber) && !empty($originalMobileNumber))
                                    <input type="hidden" id="original_mobile_number" name="original_mobile_number" value="{{ $originalMobileNumber }}">
                                @endif
                            </div>
                            <div class="form-text">This will be used to identify the visitor</div>
                            @error('mobile_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-paytm">Visitor Status</label>
                            <div id="visitorStatus" class="form-control-plaintext">
                                @if($isExistingVisitor)
                                    <span class="badge-paytm-success">Existing Visitor</span>
                                @else
                                    <span class="badge-paytm-primary">New Visitor</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Information -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="name" class="form-label-paytm">Visitor Name *</label>
                            <input type="text" class="form-control-paytm" id="name" name="name" 
                                   required maxlength="255" placeholder="Enter visitor's full name"
                                   value="{{ $prefilledName ?? '' }}">
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Course Selection -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="course_id" class="form-label-paytm">Course Interest *</label>
                            <select class="form-control-paytm" id="course_id" name="course_id" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->course_id }}" 
                                            @if($course->course_name === 'None')
                                                {{ (!isset($lastInteractionDetails)) ? 'selected' : (isset($lastInteractionDetails) && $lastInteractionDetails['course_id'] == $course->course_id ? 'selected' : '') }}
                                            @else
                                                {{ (isset($lastInteractionDetails) && $lastInteractionDetails['course_id'] == $course->course_id) ? 'selected' : '' }}
                                            @endif>
                                        {{ $course->course_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">"None" is selected by default for general visitors</div>
                            <!-- Debug: {{ isset($lastInteractionDetails) ? 'Has previous data' : 'New visitor' }} -->
                            @error('course_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6" id="father_name_container" style="display: none;">
                            <label for="father_name" class="form-label-paytm">Father's Name *</label>
                            <input type="text" class="form-control-paytm" id="father_name" name="father_name" 
                                   maxlength="255" placeholder="Enter father's name"
                                   value="{{ isset($lastInteractionDetails) ? $lastInteractionDetails['father_name'] : '' }}">
                            <div class="form-text">Required when selecting a course</div>
                            @error('father_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="mode" class="form-label-paytm">Visit Mode *</label>
                            <select class="form-control-paytm" id="mode" name="mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'In-Campus') ? 'selected' : '' }}>In-Campus</option>
                                <option value="Out-Campus" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'Out-Campus') ? 'selected' : '' }}>Out-Campus</option>
                                <option value="Telephonic" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'Telephonic') ? 'selected' : '' }}>Telephonic</option>
                            </select>
                            @error('mode')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tags" class="form-label">Purpose (Tags) *</label>
                            <div class="tag-selection-container">
                                @foreach($tags as $tag)
                                    <div class="form-check form-check-inline mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="tags[]" value="{{ $tag->id }}" 
                                               id="tag_{{ $tag->id }}"
                                               {{ (isset($lastInteractionDetails) && in_array($tag->id, $lastInteractionDetails['tags'])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tag_{{ $tag->id }}">
                                            <span class="badge" style="background-color: {{ $tag->color }}; color: white;">
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select one or more tags that describe the purpose of this visit</div>
                            @error('tags')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Meeting Assignment -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="meeting_with" class="form-label-paytm">Assign To *</label>
                            <select class="form-control-paytm" id="meeting_with" name="meeting_with" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->user_id }}" 
                                            {{ (isset($lastInteractionDetails) && $lastInteractionDetails['meeting_with'] == $employee->user_id) ? 'selected' : ($employee->user_id == auth()->user()->user_id ? 'selected' : '') }}>
                                        {{ $employee->name }} ({{ $employee->branch->branch_name ?? 'No Branch' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">You can assign this visitor to yourself or any other employee</div>
                            @error('meeting_with')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Selection -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Address *</label>
                            <input type="text" class="form-control" id="address" name="address_input"
                                   placeholder="Type to search or add new address" autocomplete="off" required
                                   value="{{ isset($lastInteractionDetails) ? $lastInteractionDetails['address_name'] : '' }}">
                            <input type="hidden" id="address_id" name="address_id" value="{{ isset($lastInteractionDetails) ? $lastInteractionDetails['address_id'] : '' }}">
                            <div id="addressSuggestions" class="list-group mt-2" style="display: none;"></div>
                            @error('address_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Initial Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="initial_notes" class="form-label">Initial Notes (Optional)</label>
                            <textarea class="form-control" id="initial_notes" name="initial_notes" rows="3" 
                                      maxlength="500" placeholder="Enter any initial notes about this visit (optional)..."></textarea>
                            <div class="form-text">Maximum 500 characters - These are just initial notes, detailed remarks will be added after the meeting</div>
                            @error('initial_notes')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('staff.visitor-search') }}" class="btn btn-paytm-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-paytm-primary">
                            <i class="fas fa-save me-1"></i>Save Visitor Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Address suggestions styling */
#addressSuggestions {
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    position: absolute;
    width: 100%;
}

#addressSuggestions .list-group-item {
    cursor: pointer;
    border: 1px solid #dee2e6;
}

#addressSuggestions .list-group-item:hover {
    background-color: #f8f9fa;
}

/* Form styling */
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Tag Selection Styles */
.tag-selection-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
    max-height: 200px;
    overflow-y: auto;
}

.tag-selection-container .form-check {
    margin-bottom: 0.5rem;
}

.tag-selection-container .form-check-input {
    margin-top: 0.25rem;
}

.tag-selection-container .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tag-selection-container .badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tag-selection-container .form-check-input:checked + .form-check-label .badge {
    opacity: 0.7;
    transform: scale(0.95);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        font-size: 0.9rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
let timeout;
const addressInput = document.getElementById('address');
const addressIdInput = document.getElementById('address_id');
const addressSuggestions = document.getElementById('addressSuggestions');

// Address search functionality
addressInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    if (query.length < 2) {
        hideAddressSuggestions();
        return;
    }
    
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        searchAddress(query);
    }, 300);
});

// Address selection
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('address-suggestion')) {
        e.preventDefault();
        const addressId = e.target.dataset.id;
        const addressName = e.target.dataset.name;
        
        if (addressId) {
            // Existing address selected
            addressInput.value = addressName;
            addressIdInput.value = addressId;
        } else {
            // Create new address
            createNewAddress(addressName);
        }
        
        hideAddressSuggestions();
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!addressInput.contains(e.target) && !addressSuggestions.contains(e.target)) {
        hideAddressSuggestions();
    }
});

function searchAddress(query) {
    // Get addresses from the passed data
    const addresses = @json($addresses);
    
    // Filter addresses that match the query
    const filteredAddresses = addresses.filter(address => 
        address.address_name.toLowerCase().includes(query.toLowerCase())
    );
    
    showAddressSuggestions(filteredAddresses, query);
}

function showAddressSuggestions(addresses, query) {
    let html = '';
    
    // Show existing addresses
    addresses.forEach(address => {
        html += `
            <a href="#" class="list-group-item list-group-item-action address-suggestion" 
               data-id="${address.address_id}" data-name="${address.address_name}">
                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                ${address.address_name}
            </a>
        `;
    });
    
    // Add option to create new address if query doesn't match exactly
    const exactMatch = addresses.some(addr => 
        addr.address_name.toLowerCase() === query.toLowerCase()
    );
    
    if (!exactMatch && query.length > 2) {
        html += `
            <a href="#" class="list-group-item list-group-item-action address-suggestion" 
               data-id="" data-name="${query}">
                <i class="fas fa-plus me-2 text-success"></i>
                Create new: "${query}"
            </a>
        `;
    }
    
    addressSuggestions.innerHTML = html;
    addressSuggestions.style.display = html ? 'block' : 'none';
}

function hideAddressSuggestions() {
    addressSuggestions.style.display = 'none';
}

function createNewAddress(addressName) {
    fetch('{{ route("staff.add-address") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                           document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({ address_name: addressName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addressInput.value = data.address_name;
            addressIdInput.value = data.address_id;
        } else {
            console.error('Error creating address:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Mobile number validation and visitor status check
document.getElementById('mobile_number').addEventListener('input', function() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        checkMobileNumber(this.value);
    }, 500);
});

// Mask mobile number display for staff privacy
document.addEventListener('DOMContentLoaded', function() {
    const mobileInput = document.getElementById('mobile_number');
    const originalMobileField = document.getElementById('original_mobile_number');
    
    // If we have an original mobile number (from "Add Revisit"), mask the display
    if (originalMobileField && originalMobileField.value) {
        const originalMobile = originalMobileField.value;
        const maskedMobile = maskMobileNumber(originalMobile);
        
        // Show masked number in the input field
        mobileInput.value = maskedMobile.replace('+91', '');
        
        // Make the input field read-only to prevent editing
        mobileInput.readOnly = true;
        mobileInput.style.backgroundColor = '#f8f9fa';
        mobileInput.style.cursor = 'not-allowed';
        
        // Add a tooltip to explain why it's masked
        mobileInput.title = 'Mobile number is masked for privacy. Original number is preserved for form submission.';
    }
});

// Helper function to mask mobile number (same logic as PHP)
function maskMobileNumber(mobileNumber) {
    if (!mobileNumber) return mobileNumber;
    
    // Remove any spaces or special characters except +
    const cleaned = mobileNumber.replace(/[^\d+]/g, '');
    
    // If number is too short, return as is
    if (cleaned.length < 8) return mobileNumber;
    
    // Extract country code (+91) and mask the middle
    let countryCode = '';
    let number = cleaned;
    
    if (cleaned.startsWith('+91')) {
        countryCode = '+91';
        number = cleaned.substring(3); // Remove +91
    }
    
    // Show first 2 digits and last 2 digits, mask the rest
    if (number.length >= 6) {
        const firstTwo = number.substring(0, 2);
        const lastTwo = number.substring(number.length - 2);
        const masked = firstTwo + 'X'.repeat(number.length - 4) + lastTwo;
        return countryCode + masked;
    }
    
    return countryCode + number;
}

function checkMobileNumber(mobileNumber) {
    if (mobileNumber.length < 10) {
        resetForm();
        hidePreviousVisitInfo();
        visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
        return;
    }

    // Show loading
    visitorStatus.innerHTML = '<span class="badge bg-warning">Checking...</span>';

    fetch('{{ route("staff.check-mobile") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                           document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({ mobile_number: mobileNumber })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            visitorStatus.innerHTML = '<span class="badge bg-success">Existing Visitor</span>';
            if (data.visitor) {
                document.getElementById('name').value = data.visitor.name;
                showPreviousVisitInfo(data.visitor);
            }
        } else {
            visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
            hidePreviousVisitInfo();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        visitorStatus.innerHTML = '<span class="badge bg-danger">Error</span>';
    });
}

function resetForm() {
    document.getElementById('name').value = '';
    hidePreviousVisitInfo();
}

function showPreviousVisitInfo(visitor) {
    // You can add previous visit info display here if needed
}

function hidePreviousVisitInfo() {
    // Hide any previous visit info
}

// Course selection logic
function handleCourseSelection() {
    const courseId = document.getElementById('course_id').value;
    const fatherNameContainer = document.getElementById('father_name_container');
    const fatherNameInput = document.getElementById('father_name');
    
    // Get the selected course option text
    const selectedOption = document.getElementById('course_id').options[document.getElementById('course_id').selectedIndex];
    const courseName = selectedOption.text;
    
    if (courseName === 'None') {
        // Hide father's name field and clear it
        fatherNameContainer.style.display = 'none';
        fatherNameInput.value = '';
        fatherNameInput.required = false;
    } else {
        // Show father's name field and make it required
        fatherNameContainer.style.display = 'block';
        fatherNameInput.required = true;
    }
}

// Add event listener for course selection
document.getElementById('course_id').addEventListener('change', handleCourseSelection);

// Handle auto-fill on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if course is pre-selected and handle father's name field
    handleCourseSelection();
});

// Form validation
document.getElementById('visitorForm').addEventListener('submit', function(e) {
    const mobileNumber = document.getElementById('mobile_number').value.trim();
    const name = document.getElementById('name').value.trim();
    const courseId = document.getElementById('course_id').value;
    const fatherName = document.getElementById('father_name').value.trim();
    const mode = document.getElementById('mode').value;
    const selectedTags = document.querySelectorAll('input[name="tags[]"]:checked');
    const meetingWith = document.getElementById('meeting_with').value;
    const addressId = document.getElementById('address_id').value;
    
    // Use original mobile number if available (for "Add Revisit" functionality)
    const originalMobileField = document.getElementById('original_mobile_number');
    const finalMobileNumber = originalMobileField && originalMobileField.value ? 
        originalMobileField.value.replace('+91', '') : mobileNumber;

    if (finalMobileNumber.length !== 10) {
        e.preventDefault();
        alert('Please enter a valid 10-digit mobile number.');
        document.getElementById('mobile_number').focus();
        return false;
    }
    
    // Update the mobile number field with the original number before submission
    if (originalMobileField && originalMobileField.value) {
        document.getElementById('mobile_number').value = finalMobileNumber;
    }

    if (!name || !courseId || !mode || selectedTags.length === 0 || !meetingWith) {
        e.preventDefault();
        alert('Please fill in all required fields, including course selection and at least one purpose tag.');
        return false;
    }

    // Check if father's name is required but not provided
    const selectedCourseOption = document.getElementById('course_id').options[document.getElementById('course_id').selectedIndex];
    const selectedCourseName = selectedCourseOption.text;
    
    if (selectedCourseName !== 'None' && !fatherName) {
        e.preventDefault();
        alert('Father\'s name is required when selecting a course.');
        document.getElementById('father_name').focus();
        return false;
    }

    // Address is required - either select existing or enter new one
    if (!addressInput.value.trim()) {
        e.preventDefault();
        alert('Please enter an address.');
        document.getElementById('address').focus();
        return false;
    }
});
</script>
@endsection
