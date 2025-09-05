@extends('layouts.app')

@section('title', 'Add Visitor - VMS')
@section('page-title', 'Add New Visitor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Visitor Entry Form
                </h5>
            </div>
            <div class="card-body">
                <form id="visitorForm" method="POST" action="{{ route('frontdesk.store-visitor') }}">
                    @csrf
                    
                    <!-- Mobile Number -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="mobile_number" class="form-label">Mobile Number *</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" 
                                       required maxlength="10" placeholder="Enter 10-digit mobile number"
                                       inputmode="numeric" pattern="[0-9]{10}" value="{{ $prefilledMobile ?? '' }}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                            </div>
                            <div class="form-text">This will be used to identify the visitor</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Visitor Status</label>
                            <div id="visitorStatus" class="form-control-plaintext">
                                <span class="badge bg-secondary">New Visitor</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="mode" class="form-label">Visit Mode *</label>
                            <select class="form-select" id="mode" name="mode" required>
                                <option value="">Select Mode</option>
                                <option value="In-Campus">In-Campus</option>
                                <option value="Out-Campus">Out-Campus</option>
                                <option value="Telephonic">Telephonic</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="purpose" class="form-label">Purpose of Visit *</label>
                            <select class="form-select" id="purpose" name="purpose" required>
                                <option value="">Select Purpose</option>
                                <option value="Parent">Parent</option>
                                <option value="Student">Student</option>
                                <option value="Ex-student">Ex-student</option>
                                <option value="New Admission">New Admission</option>
                                <option value="Marketing">Marketing</option>
                                <option value="News & Media">News & Media</option>
                                <option value="Advertising">Advertising</option>
                            </select>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">Visitor Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   required maxlength="255" placeholder="Enter visitor name" value="{{ $prefilledName ?? '' }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="meeting_with" class="form-label">Meeting With *</label>
                            <select class="form-select" id="meeting_with" name="meeting_with" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->user_id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="address_input" class="form-label">Address *</label>
                            <input type="text" class="form-control" id="address_input" name="address_input" 
                                   placeholder="Type address or select from suggestions"
                                   autocomplete="off">
                            <input type="hidden" id="address_id" name="address_id" required>
                            <div id="addressSuggestions" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                <!-- Address suggestions will appear here -->
                            </div>
                            <div class="form-text">
                                <small class="text-muted">Start typing to see suggestions or create new address</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="remarks" class="form-label">Initial Remarks *</label>
                            <textarea class="form-control" id="remarks" name="remarks" 
                                      required rows="3" placeholder="Enter initial remarks (can be 'NA')">NA</textarea>
                        </div>
                    </div>

                    <!-- Previous Visit Info (Hidden by default) -->
                    <div id="previousVisitInfo" class="row mb-4" style="display: none;">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-gradient-primary text-white border-0">
                                    <h6 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Previous Visit Information
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div id="previousVisitDetails" class="row g-3">
                                        <!-- Content will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                <a href="{{ route('frontdesk.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Visitor Entry
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileInput = document.getElementById('mobile_number');
    const visitorStatus = document.getElementById('visitorStatus');
    const previousVisitInfo = document.getElementById('previousVisitInfo');
    const previousVisitDetails = document.getElementById('previousVisitDetails');
    const nameInput = document.getElementById('name');
    const addressInput = document.getElementById('address_input');
    const addressIdInput = document.getElementById('address_id');
    const addressSuggestions = document.getElementById('addressSuggestions');
    const purposeSelect = document.getElementById('purpose');
    const meetingWithSelect = document.getElementById('meeting_with');

    // Debounce function to avoid too many API calls
    let timeout;
    mobileInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            checkMobileNumber(this.value);
        }, 500);
    });

    function checkMobileNumber(mobileNumber) {
        if (mobileNumber.length < 10) {
            resetForm();
            hidePreviousVisitInfo();
            visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
            return;
        }

        // Show loading
        visitorStatus.innerHTML = '<span class="badge bg-warning">Checking...</span>';

        fetch('{{ route("frontdesk.check-mobile") }}', {
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
                showPreviousVisitInfo(data.visitor);
            } else {
                visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
                hidePreviousVisitInfo();
                resetForm();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resetForm();
            hidePreviousVisitInfo();
            visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
        });
    }

    function showPreviousVisitInfo(visitor) {
        let details = '';
        
        // Visitor Name - Full width on mobile, half on larger screens
        details += `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="info-card bg-light rounded p-3 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user text-primary me-2"></i>
                        <strong class="text-dark">Visitor Name</strong>
                    </div>
                    <div class="text-primary fw-bold">${visitor.name}</div>
                </div>
            </div>
        `;
        
        // Location
        if (visitor.last_location) {
            details += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="info-card bg-light rounded p-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-map-marker-alt text-success me-2"></i>
                            <strong class="text-dark">Location</strong>
                        </div>
                        <div class="text-success fw-bold">${visitor.last_location}</div>
                    </div>
                </div>
            `;
        }
        
        // Purpose
        if (visitor.last_purpose) {
            details += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="info-card bg-light rounded p-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-bullseye text-info me-2"></i>
                            <strong class="text-dark">Purpose</strong>
                        </div>
                        <div class="text-info fw-bold">${visitor.last_purpose}</div>
                    </div>
                </div>
            `;
        }

        // Meeting With Person
        if (visitor.last_meeting_with) {
            details += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="info-card bg-light rounded p-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-handshake text-warning me-2"></i>
                            <strong class="text-dark">Meeting With</strong>
                        </div>
                        <div class="text-warning fw-bold">${visitor.last_meeting_with}</div>
                    </div>
                </div>
            `;
        }

        // Employee Branch
        if (visitor.last_meeting_with_branch) {
            details += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="info-card bg-light rounded p-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-building text-secondary me-2"></i>
                            <strong class="text-dark">Employee Branch</strong>
                        </div>
                        <div class="text-secondary fw-bold">${visitor.last_meeting_with_branch}</div>
                    </div>
                </div>
            `;
        }

        // Visit Date & Time
        if (visitor.last_interaction_date && visitor.last_interaction_time) {
            details += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="info-card bg-light rounded p-3 h-100">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar-alt text-danger me-2"></i>
                            <strong class="text-dark">Visit Date</strong>
                        </div>
                        <div class="text-danger fw-bold">${visitor.last_interaction_date}</div>
                        <div class="text-muted small">${visitor.last_interaction_time}</div>
                    </div>
                </div>
            `;
        }

        previousVisitDetails.innerHTML = details;
        previousVisitInfo.style.display = 'block';

        // Auto-fill form with previous data
        nameInput.value = visitor.name;
        
        // Auto-fill address if it exists
        if (visitor.last_location) {
            addressInput.value = visitor.last_location;
            // We'll need to find the address_id for this address
            searchAddress(visitor.last_location);
        }

        // Auto-select purpose if it exists
        if (visitor.last_purpose) {
            purposeSelect.value = visitor.last_purpose;
        }
    }

    function hidePreviousVisitInfo() {
        previousVisitInfo.style.display = 'none';
    }

    // Smart address functionality
    let addressSearchTimeout;
    
    addressInput.addEventListener('input', function() {
        clearTimeout(addressSearchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideAddressSuggestions();
            addressIdInput.value = '';
            return;
        }
        
        // Debounce search
        addressSearchTimeout = setTimeout(() => {
            searchAddress(query);
        }, 300);
    });
    
    // Handle address selection
    addressInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            searchAddress(this.value.trim());
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!addressInput.contains(e.target) && !addressSuggestions.contains(e.target)) {
            hideAddressSuggestions();
        }
    });
    
    function searchAddress(query) {
        fetch(`/api/addresses/search?q=${encodeURIComponent(query)}&limit=10`)
            .then(response => response.json())
            .then(data => {
                showAddressSuggestions(data, query);
            })
            .catch(error => {
                console.error('Error searching addresses:', error);
            });
    }
    
    function showAddressSuggestions(addresses, query) {
        if (addresses.length === 0) {
            hideAddressSuggestions();
            return;
        }
        
        let suggestionsHtml = '';
        
        addresses.forEach(address => {
            suggestionsHtml += `
                <div class="dropdown-item address-suggestion" data-id="${address.id}" data-name="${address.text}">
                    <i class="fas fa-map-marker-alt text-success me-2"></i>
                    ${address.text}
                </div>
            `;
        });
        
        // Add option to create new address
        suggestionsHtml += `
            <div class="dropdown-item address-suggestion new-address" data-name="${query}">
                <i class="fas fa-plus text-primary me-3"></i>
                <em>Create new: "${query}"</em>
            </div>
        `;
        
        addressSuggestions.innerHTML = suggestionsHtml;
        addressSuggestions.style.display = 'block';
        
        // Add click handlers
        document.querySelectorAll('.address-suggestion').forEach(item => {
            item.addEventListener('click', function() {
                const addressId = this.dataset.id;
                const addressName = this.dataset.name;
                
                if (addressId) {
                    // Existing address selected
                    addressInput.value = addressName;
                    addressIdInput.value = addressId;
                } else {
                    // Create new address
                    createNewAddress(addressName);
                }
                
                hideAddressSuggestions();
            });
        });
    }
    
    function hideAddressSuggestions() {
        addressSuggestions.style.display = 'none';
    }
    
    function createNewAddress(addressName) {
        fetch('{{ route("frontdesk.add-address") }}', {
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
                // Silently save - no success message as requested
            } else {
                console.error('Error creating address:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Simple notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    function resetForm() {
        visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
        hidePreviousVisitInfo();
    }
});
</script>
@endsection
