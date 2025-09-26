@extends('layouts.app')

@section('title', 'Add Visitor - Task Book')
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
                    
                    <!-- Hidden fields to preserve parameters -->
                    @if(request('action'))
                        <input type="hidden" name="action" value="{{ request('action') }}">
                    @endif
                    @if(request('visitor_id'))
                        <input type="hidden" name="visitor_id" value="{{ request('visitor_id') }}">
                    @endif
                    
                    <!-- Mobile Number -->
                    <div class="form-field mb-4">
                        <div class="field-header">
                            <div class="field-title">
                                <h6 class="mb-1">Mobile Number</h6>
                                <small class="text-muted">This will be used to identify the visitor</small>
                            </div>
                            <div class="visitor-status">
                                <div id="visitorStatus">
                                    @if(isset($lastInteractionDetails) && $lastInteractionDetails)
                                        <span class="badge bg-success">Existing Visitor</span>
                                    @elseif(isset($isExistingContact) && $isExistingContact)
                                        <span class="badge bg-info">Existing Contact</span>
                                        <small class="text-muted d-block mt-1">{{ $existingStudentsCount ?? 0 }} student(s) found</small>
                                    @else
                                        <span class="badge bg-primary">New Contact</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="field-content">
                            <div class="input-group modern-input">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" 
                                       required maxlength="10" placeholder="Enter 10-digit mobile number"
                                       inputmode="numeric" pattern="[0-9]{10}" value="{{ $prefilledMobile ?? '' }}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                                <!-- Hidden field to store original mobile number for form submission -->
                                @if(isset($originalMobileNumber) && !empty($originalMobileNumber))
                                    <input type="hidden" id="original_mobile_number" name="original_mobile_number" value="{{ $originalMobileNumber }}">
                                @endif
                            </div>
                            @error('mobile_number')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="form-field mb-4">
                        <div class="field-header">
                            <div class="field-title">
                                <h6 class="mb-1">Contact Person</h6>
                                <small class="text-muted">Enter the contact person's full name</small>
                            </div>
                        </div>
                        <div class="field-content">
                            <input type="text" class="form-control modern-input" id="name" name="name" 
                                   required maxlength="255" placeholder="Enter contact person's full name"
                                   value="{{ $lastInteractionDetails['contact_name'] ?? $prefilledName ?? '' }}"
                                   {{ request('action') === 'add_interaction' ? 'readonly' : '' }}>
                            @error('name')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Course -->
                    <div class="form-field mb-4">
                        <div class="field-header">
                            <div class="field-title">
                                <h6 class="mb-1">Course</h6>
                                <small class="text-muted">Select the course interest (None by default)</small>
                            </div>
                        </div>
                        <div class="field-content">
                            <select class="form-select modern-input" id="course_id" name="course_id" required>
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
                            @error('course_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Two Column Layout -->
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-6 col-12">
                            <!-- Purpose -->
                            <div class="form-field mb-4">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Purpose</h6>
                                        <small class="text-muted">Select the purpose of this visit</small>
                                    </div>
                                </div>
                                <div class="field-content">
                                    <select class="form-select modern-input" id="purpose" name="purpose" required>
                                        <option value="">Select Purpose</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}" 
                                                    {{ (isset($lastInteractionDetails) && in_array($tag->id, $lastInteractionDetails['tags'])) ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('purpose')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Student Name (Conditional) -->
                            <div class="form-field mb-4" id="student_name_container" style="display: none;">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Student Name</h6>
                                    </div>
                                </div>
                                <div class="field-content">
                                    <input type="text" class="form-control modern-input" id="student_name" name="student_name" 
                                           maxlength="255" placeholder="Enter student's name"
                                           value="{{ $lastInteractionDetails['student_name'] ?? '' }}"
                                           {{ request('action') === 'add_interaction' ? 'readonly' : '' }}>
                                    @error('student_name')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Father's Name (Conditional) -->
                            <div class="form-field mb-4" id="father_name_container" style="display: none;">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Father's Name</h6>
                                    </div>
                                </div>
                                <div class="field-content">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="text" class="form-control modern-input flex-grow-1" id="father_name" name="father_name" 
                                   maxlength="255" placeholder="Enter father's name"
                                   value="{{ $lastInteractionDetails['father_name'] ?? '' }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="copy_visitor_name" 
                                                   onchange="toggleFatherNameCopy()">
                                            <label class="form-check-label" for="copy_visitor_name">
                                                <small>Same as Visitor</small>
                                            </label>
                                        </div>
                                    </div>
                            @error('father_name')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-6 col-12">
                            <!-- Visit Mode -->
                            <div class="form-field mb-4">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Visit Mode</h6>
                                        <small class="text-muted">How is the visitor meeting with you?</small>
                        </div>
                    </div>
                                <div class="field-content">
                                    <select class="form-select modern-input" id="mode" name="mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'In-Campus') ? 'selected' : '' }}>In-Campus</option>
                                <option value="Out-Campus" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'Out-Campus') ? 'selected' : '' }}>Out-Campus</option>
                                <option value="Telephonic" {{ (isset($lastInteractionDetails) && $lastInteractionDetails['mode'] == 'Telephonic') ? 'selected' : '' }}>Telephonic</option>
                            </select>
                            @error('mode')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                            </div>

                            <!-- Assign To -->
                            <div class="form-field mb-4">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Assign To</h6>
                                        <small class="text-muted">You can assign this visitor to yourself or any other employee</small>
                        </div>
                    </div>
                                <div class="field-content">
                                    <select class="form-select modern-input" id="meeting_with" name="meeting_with" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->user_id }}" 
                                            {{ (isset($lastInteractionDetails) && $lastInteractionDetails['meeting_with'] == $employee->user_id) ? 'selected' : ($employee->user_id == auth()->user()->user_id ? 'selected' : '') }}>
                                        {{ $employee->name }} ({{ $employee->branch->branch_name ?? 'No Branch' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('meeting_with')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                            <!-- Address -->
                            <div class="form-field mb-4">
                                <div class="field-header">
                                    <div class="field-title">
                                        <h6 class="mb-1">Address</h6>
                                        <small class="text-muted">Type to search or add new address</small>
                                    </div>
                                </div>
                                <div class="field-content">
                                    <input type="text" class="form-control modern-input" id="address" name="address_input"
                                   placeholder="Type to search or add new address" autocomplete="off" required
                                   value="{{ isset($lastInteractionDetails) ? $lastInteractionDetails['address_name'] : '' }}">
                            <input type="hidden" id="address_id" name="address_id" value="{{ isset($lastInteractionDetails) ? $lastInteractionDetails['address_id'] : '' }}">
                            <div id="addressSuggestions" class="list-group mt-2" style="display: none;"></div>
                            @error('address_id')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload (Optional) -->
                    <div class="form-field mb-4">
                        <div class="field-header">
                            <div class="field-title">
                                <h6 class="mb-1">Attach Files (Optional)</h6>
                                <small class="text-muted">Upload documents, images, or other files related to this visit</small>
                            </div>
                        </div>
                        <div class="field-content">
                            <div class="file-upload-section">
                                <button type="button" class="btn btn-outline-success" onclick="showVisitorFileUploadModal()">
                                    <i class="fas fa-paperclip me-1"></i>Upload Files
                                </button>
                                <div id="visitorFileInfo" class="mt-2" style="display: none;">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <span id="visitorFileCount">0</span> file(s) selected
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Initial Notes (Optional) -->
                    <div class="form-field mb-4">
                        <div class="field-header">
                            <div class="field-title">
                                <h6 class="mb-1">Initial Notes (Optional)</h6>
                                <small class="text-muted">Maximum 500 characters - These are just initial notes, detailed remarks will be added after the meeting</small>
                            </div>
                        </div>
                        <div class="field-content">
                            <textarea class="form-control modern-input" id="initial_notes" name="initial_notes" rows="4" 
                                      maxlength="500" placeholder="Enter any initial notes about this visit (optional)..."></textarea>
                            @error('initial_notes')
                                <div class="text-danger mt-2">{{ $message }}</div>
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
/* Modern Form Fields */
.form-field {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    margin-bottom: 1.5rem;
    position: relative;
}

.form-field::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.form-field:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: #e0e0e0;
}

.form-field:hover::before {
    opacity: 1;
}

.field-header {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
}

.field-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 1.5rem;
    right: 1.5rem;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.form-field:hover .field-header::after {
    transform: scaleX(1);
}

.field-title {
    flex-grow: 1;
}

.field-title h6 {
    color: #2c3e50;
    margin: 0;
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
}

.field-title small {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
}

.visitor-status {
    display: flex;
    align-items: center;
}

.field-content {
    padding: 1.5rem;
    background: #fff;
}

/* Modern Input Styles */
.modern-input {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    font-size: 1rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: #fff;
    font-weight: 500;
    color: #2c3e50;
}

.modern-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.1);
    outline: none;
    transform: translateY(-2px);
    background: #f8f9ff;
}

.modern-input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

.input-group .modern-input {
    border-left: none;
    border-radius: 0 12px 12px 0;
}

.input-group-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid #667eea;
    border-right: none;
    border-radius: 12px 0 0 12px;
    font-weight: 700;
    font-size: 1rem;
    padding: 1rem 1.25rem;
}

/* Form Select Styling */
.form-select.modern-input {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23667eea' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1.25rem center;
    background-size: 16px 12px;
    padding-right: 3rem;
}

.form-select.modern-input:focus {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23667eea' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
}

/* Address suggestions styling */
#addressSuggestions {
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    position: absolute;
    width: 100%;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    margin-top: 0.5rem;
}

#addressSuggestions .list-group-item {
    cursor: pointer;
    border: none;
    padding: 1rem 1.25rem;
    transition: all 0.3s ease;
    border-bottom: 1px solid #f8f9fa;
}

#addressSuggestions .list-group-item:hover {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    transform: translateX(8px);
    border-left: 4px solid #667eea;
}

#addressSuggestions .list-group-item:last-child {
    border-bottom: none;
}

/* Readonly fields styling */
.form-control[readonly] {
    background-color: #f8f9fa !important;
    border-color: #e9ecef !important;
    color: #6c757d !important;
    cursor: not-allowed;
}

.form-control[readonly]:focus {
    box-shadow: none !important;
    border-color: #e9ecef !important;
}

/* Form validation */
.text-danger {
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.75rem;
    color: #dc3545;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.text-danger::before {
    content: '⚠';
    font-size: 1rem;
}

/* Submit buttons */
.btn {
    border-radius: 12px;
    padding: 1rem 2.5rem;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-paytm-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-paytm-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-paytm-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-paytm-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .form-field {
        margin-bottom: 1rem;
        border-radius: 12px;
    }
    
    .field-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .field-content {
        padding: 1rem;
    }
    
    .modern-input {
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    
    .input-group-text {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .field-title h6 {
        font-size: 1rem;
    }
    
    .field-title small {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .field-header {
        padding: 0.75rem;
    }
    
    .field-content {
        padding: 0.75rem;
    }
    
    .field-title h6 {
        font-size: 0.95rem;
    }
    
    .field-title small {
        font-size: 0.75rem;
    }
    
    .modern-input {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 0.6rem 1.25rem;
        font-size: 0.85rem;
    }
}

/* Animation for form fields */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-field {
    animation: slideInUp 0.6s ease-out;
}

.form-field:nth-child(1) { animation-delay: 0.1s; }
.form-field:nth-child(2) { animation-delay: 0.2s; }
.form-field:nth-child(3) { animation-delay: 0.3s; }
.form-field:nth-child(4) { animation-delay: 0.4s; }
.form-field:nth-child(5) { animation-delay: 0.5s; }
.form-field:nth-child(6) { animation-delay: 0.6s; }
.form-field:nth-child(7) { animation-delay: 0.7s; }
.form-field:nth-child(8) { animation-delay: 0.8s; }
.form-field:nth-child(9) { animation-delay: 0.9s; }
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
    
    // If we have an original mobile number (from "Add Interaction"), mask the display
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
    const studentNameContainer = document.getElementById('student_name_container');
    const studentNameInput = document.getElementById('student_name');
    const fatherNameContainer = document.getElementById('father_name_container');
    const fatherNameInput = document.getElementById('father_name');
    
    // Get the selected course option text
    const selectedOption = document.getElementById('course_id').options[document.getElementById('course_id').selectedIndex];
    const courseName = selectedOption.text;
    
    if (courseName === 'None') {
        // Hide both student name and father's name fields with smooth animation
        [studentNameContainer, fatherNameContainer].forEach(container => {
            container.style.transition = 'all 0.3s ease';
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
        });
        
        setTimeout(() => {
            studentNameContainer.style.display = 'none';
        fatherNameContainer.style.display = 'none';
            studentNameInput.value = '';
        fatherNameInput.value = '';
            studentNameInput.required = false;
        fatherNameInput.required = false;
        }, 300);
    } else {
        // Show both student name and father's name fields with smooth animation
        [studentNameContainer, fatherNameContainer].forEach(container => {
            container.style.display = 'block';
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
        });
        
        studentNameInput.required = true;
        fatherNameInput.required = true;
        
        setTimeout(() => {
            [studentNameContainer, fatherNameContainer].forEach(container => {
                container.style.transition = 'all 0.3s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            });
        }, 10);
    }
}

// Add event listener for course selection
document.getElementById('course_id').addEventListener('change', handleCourseSelection);

// Function to handle "Same as Visitor" checkbox
function toggleFatherNameCopy() {
    const copyCheckbox = document.getElementById('copy_visitor_name');
    const fatherNameInput = document.getElementById('father_name');
    const visitorNameInput = document.getElementById('name');
    
    if (copyCheckbox.checked) {
        // Copy visitor name to father's name
        fatherNameInput.value = visitorNameInput.value;
        fatherNameInput.readOnly = true;
        fatherNameInput.style.backgroundColor = '#f8f9fa';
        fatherNameInput.style.cursor = 'not-allowed';
    } else {
        // Enable father's name input
        fatherNameInput.readOnly = false;
        fatherNameInput.style.backgroundColor = '#fff';
        fatherNameInput.style.cursor = 'text';
    }
}

// Also update father's name when visitor name changes (if checkbox is checked)
document.getElementById('name').addEventListener('input', function() {
    const copyCheckbox = document.getElementById('copy_visitor_name');
    const fatherNameInput = document.getElementById('father_name');
    
    if (copyCheckbox && copyCheckbox.checked) {
        fatherNameInput.value = this.value;
    }
});

// Handle auto-fill on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if course is pre-selected and handle father's name field
    handleCourseSelection();
    
    // Add smooth focus effects to all inputs
    const inputs = document.querySelectorAll('.modern-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Add smooth focus effects to all form fields
    const formFields = document.querySelectorAll('.form-field');
    formFields.forEach(field => {
        field.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        field.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Form validation
document.getElementById('visitorForm').addEventListener('submit', function(e) {
    const mobileNumber = document.getElementById('mobile_number').value.trim();
    const name = document.getElementById('name').value.trim();
    const courseId = document.getElementById('course_id').value;
    const studentName = document.getElementById('student_name').value.trim();
    const fatherName = document.getElementById('father_name').value.trim();
    const mode = document.getElementById('mode').value;
    const purpose = document.getElementById('purpose').value;
    const meetingWith = document.getElementById('meeting_with').value;
    const addressId = document.getElementById('address_id').value;
    
    // Use original mobile number if available (for "Add Interaction" functionality)
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

    if (!name || !courseId || !mode || !purpose || !meetingWith) {
        e.preventDefault();
        alert('Please fill in all required fields, including purpose selection.');
        return false;
    }

    // Check if student name and father's name are required but not provided
    const selectedCourseOption = document.getElementById('course_id').options[document.getElementById('course_id').selectedIndex];
    const selectedCourseName = selectedCourseOption.text;
    
    if (selectedCourseName !== 'None') {
        if (!studentName) {
            e.preventDefault();
            alert('Student name is required when selecting a course.');
            document.getElementById('student_name').focus();
            return false;
        }
        if (!fatherName) {
        e.preventDefault();
        alert('Father\'s name is required when selecting a course.');
        document.getElementById('father_name').focus();
        return false;
        }
    }

    // Address is required - either select existing or enter new one
    if (!addressInput.value.trim()) {
        e.preventDefault();
        alert('Please enter an address.');
        document.getElementById('address').focus();
        return false;
    }
});

// Visitor File Upload Functions - Reusing existing file upload system
let visitorFiles = [];

function showVisitorFileUploadModal() {
    // Create a temporary interaction ID for visitor files
    const tempInteractionId = 'visitor_' + Date.now();
    document.getElementById('upload_interaction_id').value = tempInteractionId;
    
    // Clear previous file info
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
    
    // Show the existing file upload modal
    const modal = new bootstrap.Modal(document.getElementById('fileUploadModal'));
    modal.show();
}

// Override the existing submitFileUpload function for visitor files
function submitFileUpload() {
    const fileInput = document.getElementById('fileInput');
    
    if (!fileInput.files[0]) {
        alert('Please select a file to upload.');
        return;
    }
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('interaction_id', 'visitor_temp_' + Date.now()); // Temporary ID for validation
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Show uploading state
    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';
    uploadBtn.disabled = true;
    
    // Try to upload to Google Drive using existing endpoint
    fetch('/staff/upload-attachment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store the uploaded file info
            visitorFiles.push({
                name: file.name,
                size: file.size,
                type: file.type,
                google_drive_file_id: data.attachment.id,
                google_drive_url: data.attachment.url
            });
            
            // Update file info display
            document.getElementById('visitorFileCount').textContent = visitorFiles.length;
            document.getElementById('visitorFileInfo').style.display = 'block';
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('fileUploadModal'));
            modal.hide();
            
            // Clear file input
            fileInput.value = '';
            document.getElementById('fileInfo').style.display = 'none';
            uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
            uploadBtn.disabled = true;
        } else {
            alert('Upload failed: ' + data.message);
            uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
            uploadBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed: ' + error.message);
        uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
        uploadBtn.disabled = false;
    });
}

// Handle file uploads after visitor creation
document.getElementById('visitorForm').addEventListener('submit', function(e) {
    // Add visitor files to form data with Google Drive info
    visitorFiles.forEach((fileData, index) => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'visitor_files[]';
        hiddenInput.value = JSON.stringify({
            name: fileData.name,
            size: fileData.size,
            type: fileData.type,
            google_drive_file_id: fileData.google_drive_file_id,
            google_drive_url: fileData.google_drive_url
        });
        this.appendChild(hiddenInput);
    });
});

</script>

@include('staff.modals.file-upload')

@endsection
