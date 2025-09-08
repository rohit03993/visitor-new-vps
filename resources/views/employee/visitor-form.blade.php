@extends('layouts.app')

@section('title', 'Add Visitor - VMS')
@section('page-title', 'Add New Visitor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Visitor Details
                </h5>
            </div>
            <div class="card-body">
                <form id="visitorForm" method="POST" action="{{ route('employee.store-visitor') }}">
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
                            @error('mobile_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Visitor Status</label>
                            <div id="visitorStatus" class="form-control-plaintext">
                                @if($isExistingVisitor)
                                    <span class="badge bg-success">Existing Visitor</span>
                                @else
                                    <span class="badge bg-secondary">New Visitor</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Information -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Visitor Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   required maxlength="255" placeholder="Enter visitor's full name">
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="purpose" class="form-label">Purpose of Visit *</label>
                            <select class="form-select" id="purpose" name="purpose" required>
                                <option value="">Select purpose</option>
                                @foreach($purposes as $purpose)
                                    <option value="{{ $purpose }}">{{ $purpose }}</option>
                                @endforeach
                            </select>
                            @error('purpose')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Branch</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-info">{{ $branch->branch_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-8">
                            <label for="address_id" class="form-label">Location/Address *</label>
                            <select class="form-select" id="address_id" name="address_id" required>
                                <option value="">Select location</option>
                                @foreach($addresses as $address)
                                    <option value="{{ $address->address_id }}">{{ $address->address_name }}</option>
                                @endforeach
                            </select>
                            @error('address_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-primary d-block w-100" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-1"></i>Add New Location
                            </button>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="remarks" class="form-label">Initial Remarks *</label>
                            <textarea class="form-control" id="remarks" name="remarks" 
                                      rows="4" required maxlength="1000" 
                                      placeholder="Enter initial remarks about the visit..."></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            @error('remarks')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                <a href="{{ route('employee.visitor-search') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Search
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Create Visitor Entry
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAddressForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_address_name" class="form-label">Location Name *</label>
                        <input type="text" class="form-control" id="new_address_name" 
                               required maxlength="255" placeholder="Enter location name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileInput = document.getElementById('mobile_number');
    const visitorStatus = document.getElementById('visitorStatus');
    const nameInput = document.getElementById('name');
    const addAddressForm = document.getElementById('addAddressForm');
    const addressSelect = document.getElementById('address_id');

    // Mobile number validation and visitor check
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
            return;
        }

        fetch('{{ route("employee.check-mobile") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ mobile_number: mobileNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                visitorStatus.innerHTML = `
                    <span class="badge bg-warning">Existing Visitor</span>
                    <small class="text-muted d-block">Last visit: ${data.visitor.last_visit}</small>
                `;
                nameInput.value = data.visitor.name;
            } else {
                visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
                nameInput.value = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
        });
    }

    function resetForm() {
        visitorStatus.innerHTML = '<span class="badge bg-secondary">New Visitor</span>';
        nameInput.value = '';
    }

    // Add new address
    addAddressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const addressName = document.getElementById('new_address_name').value;
        
        fetch('{{ route("employee.add-address") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ address_name: addressName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to select
                const newOption = document.createElement('option');
                newOption.value = data.address.id;
                newOption.textContent = data.address.name;
                newOption.selected = true;
                addressSelect.appendChild(newOption);
                
                // Close modal and reset form
                bootstrap.Modal.getInstance(document.getElementById('addAddressModal')).hide();
                document.getElementById('new_address_name').value = '';
                
                // Show success message
                showAlert('Location added successfully!', 'success');
            } else {
                showAlert('Error adding location: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error adding location. Please try again.', 'danger');
        });
    });

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const form = document.getElementById('visitorForm');
        form.insertBefore(alertDiv, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endsection
