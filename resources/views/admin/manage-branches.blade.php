@extends('layouts.app')

@section('title', 'Manage Branches')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Branch Management</h2>
                <button type="button" class="btn btn-paytm-primary" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                    <i class="fas fa-plus me-2"></i>Create New Branch
                </button>
            </div>

            <!-- Branch List -->
            <div class="card-paytm paytm-fade-in">
                <div class="card-paytm-header">
                    <h5 class="card-title mb-0">All Branches</h5>
                </div>
                <div class="card-paytm-body">
                    @if($branches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-paytm">
                                <thead>
                                    <tr>
                                        <th>Branch Name</th>
                                        <th>Branch Code</th>
                                        <th>Users Count</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                        <tr>
                                            <td>
                                                <strong>{{ $branch->branch_name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $branch->branch_code ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $branch->users->count() }} user(s)</span>
                                            </td>
                                            <td>
                                                {{ $branch->createdBy ? $branch->createdBy->name : 'System' }}
                                            </td>
                                            <td>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($branch->created_at) }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                                                        onclick="editBranch({{ $branch->branch_id }}, '{{ addslashes($branch->branch_name) }}', '{{ addslashes($branch->branch_code ?? '') }}', '{{ addslashes($branch->address ?? '') }}')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteBranch({{ $branch->branch_id }}, '{{ addslashes($branch->branch_name) }}', {{ $branch->users->count() }})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No branches found</h5>
                            <p class="text-muted">Create your first branch to get started.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Branch Modal -->
<div class="modal fade" id="createBranchModal" tabindex="-1" aria-labelledby="createBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBranchModalLabel">Create New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createBranchForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Branch Name *</label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                        <div class="form-text">Enter a unique branch name</div>
                    </div>
                    <div class="mb-3">
                        <label for="branch_code" class="form-label">Branch Code</label>
                        <input type="text" class="form-control" id="branch_code" name="branch_code" maxlength="50">
                        <div class="form-text">Optional: Enter a unique branch code (auto-generated if left empty)</div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                        <div class="form-text">Optional: Enter the branch address</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBranchForm">
                <input type="hidden" id="edit_branch_id" name="branch_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_branch_name" class="form-label">Branch Name *</label>
                        <input type="text" class="form-control" id="edit_branch_name" name="branch_name" required>
                        <div class="form-text">Enter a unique branch name</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_branch_code" class="form-label">Branch Code</label>
                        <input type="text" class="form-control" id="edit_branch_code" name="branch_code" maxlength="50">
                        <div class="form-text">Enter a unique branch code</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" maxlength="500"></textarea>
                        <div class="form-text">Enter the branch address</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Create Branch
document.getElementById('createBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.create-branch") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('createBranchModal')).hide();
            this.reset();
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while creating the branch.');
    });
});

// Edit Branch
function editBranch(branchId, branchName, branchCode, address) {
    document.getElementById('edit_branch_id').value = branchId;
    document.getElementById('edit_branch_name').value = branchName;
    document.getElementById('edit_branch_code').value = branchCode || '';
    document.getElementById('edit_address').value = address || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editBranchModal'));
    modal.show();
}

document.getElementById('editBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const branchId = document.getElementById('edit_branch_id').value;
    const formData = new FormData(this);
    
    fetch(`/admin/update-branch/${branchId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editBranchModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while updating the branch.');
    });
});

// Delete Branch
function deleteBranch(branchId, branchName, userCount) {
    if (userCount > 0) {
        showAlert('error', `Cannot delete branch "${branchName}". It has ${userCount} user(s) assigned to it.`);
        return;
    }
    
    if (confirm(`Are you sure you want to delete the branch "${branchName}"? This action cannot be undone.`)) {
        fetch(`/admin/branches/${branchId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while deleting the branch.');
        });
    }
}

// Alert function
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert at the top of the content
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}
</script>
@endsection