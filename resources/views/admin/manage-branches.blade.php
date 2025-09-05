@extends('layouts.app')

@section('title', 'Manage Branches - VMS')
@section('page-title', 'Manage Branches')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <h2 class="h4 mb-0">Branch Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                <i class="fas fa-plus me-2"></i>Create New Branch
            </button>
        </div>
    </div>
</div>

<!-- Branches Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>All Branches
                </h5>
            </div>
            <div class="card-body">
                @if($branches->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Branch Name</th>
                                    <th>Total Users</th>
                                    <th>Total Interactions</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branches as $branch)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $branch->branch_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $branch->users_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $branch->interactions_count }}</span>
                                        </td>
                                        <td>{{ $branch->createdBy ? $branch->createdBy->name : 'System' }}</td>
                                        <td>{{ $branch->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($branch->users_count == 0)
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteBranch({{ $branch->branch_id }}, '{{ $branch->branch_name }}')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            @else
                                                <span class="text-muted">Cannot delete (has users)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($branches as $branch)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Branch Name -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1 text-primary">{{ $branch->branch_name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-building me-1"></i>
                                                Branch
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($branch->users_count == 0)
                                                <span class="badge bg-warning">Can Delete</span>
                                            @else
                                                <span class="badge bg-info">Active</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Branch Stats -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Total Users:</small><br>
                                            <span class="badge bg-success">{{ $branch->users_count }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Total Interactions:</small><br>
                                            <span class="badge bg-info">{{ $branch->interactions_count }}</span>
                                        </div>
                                    </div>

                                    <!-- Branch Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Created By:</small><br>
                                            <strong>{{ $branch->createdBy ? $branch->createdBy->name : 'System' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Created Date:</small><br>
                                            <strong>{{ $branch->created_at->format('M d, Y') }}</strong>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        @if($branch->users_count == 0)
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteBranch({{ $branch->branch_id }}, '{{ $branch->branch_name }}')">
                                                <i class="fas fa-trash me-1"></i>Delete Branch
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-lock me-1"></i>Cannot delete (has users)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
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

<!-- Create Branch Modal -->
<div class="modal fade" id="createBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.create-branch') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Branch Name *</label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                        <div class="form-text">Enter a unique name for the new branch.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Branch Modal -->
<div class="modal fade" id="deleteBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the branch <strong id="deleteBranchName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteBranchForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Branch</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Mobile Card Styles */
.interaction-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.interaction-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.interaction-card .card-body {
    padding: 1rem;
}

.interaction-card .card-title {
    color: #495057;
    font-weight: 600;
    font-size: 1rem;
}

.interaction-card .badge {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.interaction-card .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 6px;
}
</style>
@endsection

@section('scripts')
<script>
function deleteBranch(branchId, branchName) {
    document.getElementById('deleteBranchName').textContent = branchName;
    document.getElementById('deleteBranchForm').action = '{{ route("admin.delete-branch", ":id") }}'.replace(':id', branchId);
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteBranchModal'));
    deleteModal.show();
}
</script>
@endsection
