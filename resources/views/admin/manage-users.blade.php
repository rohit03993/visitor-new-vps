@extends('layouts.app')

@section('title', 'Manage Users - VMS')
@section('page-title', 'Manage Users')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <h2 class="h4 mb-0">User Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-plus me-2"></i>Create New User
            </button>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>All Users
                </h5>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th>Mobile</th>
                                    <th>Permissions</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'frontdesk' ? 'info' : 'success') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $user->getBranchName() }}</span>
                                        </td>
                                        <td>{{ $user->mobile_number ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $branchPermissions = $user->branchPermissions()->with('branch')->get();
                                            @endphp
                                            @if($branchPermissions->count() > 0)
                                                @foreach($branchPermissions as $permission)
                                                    <div class="mb-1">
                                                        <small class="text-muted">{{ $permission->branch->branch_name }}:</small>
                                                        @if($permission->can_view_remarks)
                                                            <span class="badge bg-success me-1">View Remarks</span>
                                                        @endif
                                                        @if($permission->can_download_excel)
                                                            <span class="badge bg-info">Download Excel</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="badge bg-warning">Basic Access</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                        <td>{{ $user->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->username }}', '{{ $user->role }}', '{{ $user->branch_id }}', '{{ $user->mobile_number }}', '{{ $user->can_view_remarks }}', '{{ $user->can_download_excel }}')">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="manageBranchPermissions({{ $user->user_id }}, '{{ $user->name }}')">
                                                <i class="fas fa-shield-alt me-1"></i>Permissions
                                            </button>
                                            @if($user->user_id != auth()->id())
                                                @if($user->is_active)
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="deactivateUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                                        <i class="fas fa-user-slash me-1"></i>Deactivate
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="reactivateUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                                        <i class="fas fa-user-check me-1"></i>Reactivate
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($users as $user)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Name and Role -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $user->name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $user->username }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'frontdesk' ? 'info' : 'success') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- User Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Branch:</small><br>
                                            <span class="badge bg-secondary">{{ $user->getBranchName() }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $user->mobile_number ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <!-- Permissions -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Permissions:</small><br>
                                            @php
                                                $branchPermissions = $user->branchPermissions()->with('branch')->get();
                                            @endphp
                                            @if($branchPermissions->count() > 0)
                                                @foreach($branchPermissions as $permission)
                                                    <div class="mb-1">
                                                        <small class="text-muted">{{ $permission->branch->branch_name }}:</small>
                                                        @if($permission->can_view_remarks)
                                                            <span class="badge bg-success me-1">View Remarks</span>
                                                        @endif
                                                        @if($permission->can_download_excel)
                                                            <span class="badge bg-info">Download Excel</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="badge bg-warning">Basic Access</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Created:</small><br>
                                            <small>{{ $user->created_at->format('M d, Y') }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Updated:</small><br>
                                            <small>{{ $user->updated_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="editUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->username }}', '{{ $user->role }}', '{{ $user->branch_id }}', '{{ $user->mobile_number }}', '{{ $user->can_view_remarks }}', '{{ $user->can_download_excel }}')">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="manageBranchPermissions({{ $user->user_id }}, '{{ $user->name }}')">
                                            <i class="fas fa-shield-alt me-1"></i>Permissions
                                        </button>
                                        @if($user->user_id != auth()->id())
                                            @if($user->is_active)
                                                <button class="btn btn-outline-warning btn-sm" 
                                                        onclick="deactivateUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                                    <i class="fas fa-user-slash me-1"></i>Deactivate
                                                </button>
                                            @else
                                                <button class="btn btn-outline-success btn-sm" 
                                                        onclick="reactivateUser({{ $user->user_id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                                    <i class="fas fa-user-check me-1"></i>Reactivate
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No users found</h5>
                        <p class="text-muted">Create your first user to get started.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.create-user') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="frontdesk">Front Desk</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Branch *</label>
                        <select class="form-select" id="branch_id" name="branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="10-digit mobile number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_view_remarks" name="can_view_remarks" value="1">
                            <label class="form-check-label" for="can_view_remarks">
                                Can View Remarks
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_download_excel" name="can_download_excel" value="1">
                            <label class="form-check-label" for="can_download_excel">
                                Can Download Excel Reports
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role *</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="frontdesk">Front Desk</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_branch_id" class="form-label">Branch *</label>
                        <select class="form-select" id="edit_branch_id" name="branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mobile_number" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="edit_mobile_number" name="mobile_number" placeholder="10-digit mobile number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_can_view_remarks" name="can_view_remarks" value="1">
                            <label class="form-check-label" for="edit_can_view_remarks">
                                Can View Remarks
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_can_download_excel" name="can_download_excel" value="1">
                            <label class="form-check-label" for="edit_can_download_excel">
                                Can Download Excel Reports
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Branch Permissions Modal -->
<div class="modal fade" id="branchPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Branch Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>User: <span id="permissionUserName"></span></h6>
                    <p class="text-muted">Grant permissions for specific branches. Users can view remarks and download Excel only for branches they have permission for.</p>
                </div>
                
                <form id="branchPermissionsForm">
                    @csrf
                    <input type="hidden" id="permission_user_id" name="user_id">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-center">View Remarks</th>
                                    <th class="text-center">Download Excel</th>
                                </tr>
                            </thead>
                            <tbody id="branchPermissionsTable">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBranchPermissions()">Save Permissions</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="deleteUserModalLabel">
                    <i class="fas fa-user-slash me-2"></i>Deactivate User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-user-slash me-2"></i>
                    <strong>Warning:</strong> This will deactivate the user. They won't be able to login and won't appear in meeting dropdowns.
                </div>
                
                <p><strong>You are about to deactivate the following user:</strong></p>
                <div class="bg-light p-3 rounded mb-3">
                    <div id="userToDeleteInfo">
                        <!-- User info will be populated by JavaScript -->
                    </div>
                </div>
                
                <!-- Statistics Section -->
                <div id="deleteStatsSection" class="mb-3" style="display: none;">
                    <h6 class="text-danger">Impact Summary:</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted">Interactions Created:</small><br>
                                <strong id="interactionsCreatedCount" class="text-danger">-</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted">Remarks Added:</small><br>
                                <strong id="remarksAddedCount" class="text-danger">-</strong>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted">Interactions Assigned:</small><br>
                                <strong id="interactionsAssignedCount" class="text-warning">-</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted">Visitors Created:</small><br>
                                <strong id="visitorsCreatedCount" class="text-danger">-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role-specific Information -->
                <div id="roleSpecificInfo" class="mb-3" style="display: none;">
                    <!-- Content will be populated by JavaScript -->
                </div>

                <div class="mb-3">
                    <h6 class="text-warning">What will happen:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-user-slash text-warning me-2"></i>User account will be deactivated (cannot login)</li>
                        <li><i class="fas fa-eye-slash text-warning me-2"></i>User will not appear in meeting dropdowns</li>
                        <li><i class="fas fa-lock text-warning me-2"></i>All existing data will be preserved</li>
                        <li><i class="fas fa-shield-alt text-warning me-2"></i>User can be reactivated later if needed</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-info">Data Impact:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info-circle text-info me-2"></i>All interactions and remarks will remain intact</li>
                        <li><i class="fas fa-info-circle text-info me-2"></i>Assigned interactions will still show the user's name</li>
                        <li><i class="fas fa-info-circle text-info me-2"></i>No data loss - everything is preserved</li>
                    </ul>
                </div>

                <!-- Sample Data Preview -->
                <div id="sampleDataSection" class="mb-3" style="display: none;">
                    <h6 class="text-info">Sample Data That Will Be Affected:</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Created Interactions:</small>
                            <div id="sampleInteractionsCreated" class="small text-muted">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Assigned Interactions:</small>
                            <div id="sampleInteractionsAssigned" class="small text-muted">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="deleteConfirmationText" class="form-label">
                        <strong>Type "DEACTIVATE" to confirm:</strong>
                    </label>
                    <input type="text" class="form-control" id="deleteConfirmationText" 
                           placeholder="Type DEACTIVATE here" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmDeleteBtn" onclick="confirmDeactivateUser()">
                    <i class="fas fa-user-slash me-1"></i>Deactivate User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reactivate User Confirmation Modal -->
<div class="modal fade" id="reactivateUserModal" tabindex="-1" aria-labelledby="reactivateUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="reactivateUserModalLabel">
                    <i class="fas fa-user-check me-2"></i>Reactivate User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-user-check me-2"></i>
                    <strong>Reactivate User:</strong> This will restore the user's access to the system.
                </div>
                
                <p><strong>You are about to reactivate the following user:</strong></p>
                <div class="bg-light p-3 rounded mb-3">
                    <div id="userToReactivateInfo">
                        <!-- User info will be populated by JavaScript -->
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-success">What will happen:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-user-check text-success me-2"></i>User account will be reactivated (can login again)</li>
                        <li><i class="fas fa-eye text-success me-2"></i>User will appear in meeting dropdowns again</li>
                        <li><i class="fas fa-unlock text-success me-2"></i>All existing data remains intact</li>
                        <li><i class="fas fa-shield-alt text-success me-2"></i>User can resume normal operations</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <label for="reactivateConfirmationText" class="form-label">
                        <strong>Type "REACTIVATE" to confirm:</strong>
                    </label>
                    <input type="text" class="form-control" id="reactivateConfirmationText" 
                           placeholder="Type REACTIVATE here" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmReactivateBtn" onclick="confirmReactivateUser()">
                    <i class="fas fa-user-check me-1"></i>Reactivate User
                </button>
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
function editUser(userId, name, username, role, branchId, mobileNumber, canViewRemarks, canDownloadExcel) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_branch_id').value = branchId || '';
    document.getElementById('edit_mobile_number').value = mobileNumber || '';
    document.getElementById('edit_can_view_remarks').checked = canViewRemarks === '1' || canViewRemarks === true;
    document.getElementById('edit_can_download_excel').checked = canDownloadExcel === '1' || canDownloadExcel === true;
    document.getElementById('editUserForm').action = '/admin/users/' + userId;
    
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}

function manageBranchPermissions(userId, userName) {
    document.getElementById('permission_user_id').value = userId;
    document.getElementById('permissionUserName').textContent = userName;
    
    // Fetch current permissions and branches
    fetch(`/admin/users/${userId}/branch-permissions`)
        .then(response => response.json())
        .then(data => {
            populateBranchPermissionsTable(data.branches, data.permissions);
            new bootstrap.Modal(document.getElementById('branchPermissionsModal')).show();
        })
        .catch(error => {
            console.error('Error fetching branch permissions:', error);
            alert('Error loading branch permissions');
        });
}

function populateBranchPermissionsTable(branches, permissions) {
    const tbody = document.getElementById('branchPermissionsTable');
    tbody.innerHTML = '';
    
    branches.forEach(branch => {
        const permission = permissions.find(p => p.branch_id == branch.branch_id) || {
            branch_id: branch.branch_id,
            can_view_remarks: false,
            can_download_excel: false
        };
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <strong>${branch.branch_name}</strong>
                <input type="hidden" name="branch_ids[]" value="${branch.branch_id}">
            </td>
            <td class="text-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" 
                           name="can_view_remarks_${branch.branch_id}" 
                           id="view_remarks_${branch.branch_id}"
                           ${permission.can_view_remarks ? 'checked' : ''}>
                    <label class="form-check-label" for="view_remarks_${branch.branch_id}"></label>
                </div>
            </td>
            <td class="text-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" 
                           name="can_download_excel_${branch.branch_id}" 
                           id="download_excel_${branch.branch_id}"
                           ${permission.can_download_excel ? 'checked' : ''}>
                    <label class="form-check-label" for="download_excel_${branch.branch_id}"></label>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function saveBranchPermissions() {
    const userId = document.getElementById('permission_user_id').value;
    console.log('Saving permissions for user ID:', userId);
    
    const formData = new FormData();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('CSRF Token:', csrfToken);
    
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page and try again.');
        return;
    }
    
    formData.append('_token', csrfToken);
    
    // Collect all branch permissions
    const branchIds = document.querySelectorAll('input[name="branch_ids[]"]');
    const permissions = [];
    
    branchIds.forEach(input => {
        const branchId = input.value;
        const canViewRemarks = document.getElementById(`view_remarks_${branchId}`).checked;
        const canDownloadExcel = document.getElementById(`download_excel_${branchId}`).checked;
        
        permissions.push({
            branch_id: branchId,
            can_view_remarks: canViewRemarks,
            can_download_excel: canDownloadExcel
        });
    });
    
    console.log('Permissions to save:', permissions);
    formData.append('permissions', JSON.stringify(permissions));
    
    const url = `/admin/users/${userId}/branch-permissions`;
    console.log('Making request to:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Branch permissions updated successfully!');
            location.reload(); // Refresh the page to show updated permissions
        } else {
            alert('Error updating permissions: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving branch permissions:', error);
        alert('Error saving branch permissions: ' + error.message);
    });
}

function deactivateUser(userId, userName, userRole) {
    // Store user data for deactivation
    window.userToDeactivate = {
        id: userId,
        name: userName,
        role: userRole
    };
    
    // Show loading state
    document.getElementById('deleteStatsSection').style.display = 'none';
    document.getElementById('roleSpecificInfo').style.display = 'none';
    document.getElementById('sampleDataSection').style.display = 'none';
    
    // Show loading in modal
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
    
    // Populate basic user info
    document.getElementById('userToDeleteInfo').innerHTML = `
        <div class="row">
            <div class="col-4"><strong>Name:</strong></div>
            <div class="col-8">${userName}</div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Role:</strong></div>
            <div class="col-8">
                <span class="badge bg-${userRole === 'admin' ? 'danger' : (userRole === 'frontdesk' ? 'info' : 'success')}">
                    ${userRole.charAt(0).toUpperCase() + userRole.slice(1)}
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-4"><strong>User ID:</strong></div>
            <div class="col-8">${userId}</div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading impact statistics...
                </div>
            </div>
        </div>
    `;
    
    // Clear confirmation text
    document.getElementById('deleteConfirmationText').value = '';
    
    // Fetch user statistics
    fetch(`/admin/users/${userId}/deactivate-stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDeleteStats(data);
            } else {
                console.error('Error loading statistics:', data.message);
                showStatsError();
            }
        })
        .catch(error => {
            console.error('Error fetching statistics:', error);
            showStatsError();
        });
}

function populateDeleteStats(data) {
    const stats = data.statistics;
    const samples = data.samples;
    const user = data.user;
    
    // Update statistics counts
    document.getElementById('interactionsCreatedCount').textContent = stats.interactions_created;
    document.getElementById('remarksAddedCount').textContent = stats.remarks_added;
    document.getElementById('interactionsAssignedCount').textContent = stats.interactions_assigned;
    document.getElementById('visitorsCreatedCount').textContent = stats.visitors_created;
    
    // Show role-specific information
    let roleSpecificHtml = '';
    if (user.role === 'employee') {
        roleSpecificHtml = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Employee User:</strong> ${stats.interactions_assigned} interactions assigned to this employee will still show the user's name, but they won't be able to login or appear in new meeting dropdowns.
            </div>
        `;
    } else if (user.role === 'frontdesk') {
        roleSpecificHtml = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Frontdesk User:</strong> ${stats.visitors_created} visitors created by this user will remain intact. All data is preserved.
            </div>
        `;
    }
    
    document.getElementById('roleSpecificInfo').innerHTML = roleSpecificHtml;
    
    // Populate sample data
    let createdSamplesHtml = '';
    if (samples.interactions_created.length > 0) {
        samples.interactions_created.forEach(interaction => {
            createdSamplesHtml += `
                <div class="small mb-1">
                    <strong>${interaction.visitor_name}</strong> - ${interaction.purpose}<br>
                    <small class="text-muted">${interaction.date} | ${interaction.address}</small>
                </div>
            `;
        });
    } else {
        createdSamplesHtml = '<div class="small text-muted">No interactions created</div>';
    }
    
    let assignedSamplesHtml = '';
    if (samples.interactions_assigned.length > 0) {
        samples.interactions_assigned.forEach(interaction => {
            assignedSamplesHtml += `
                <div class="small mb-1">
                    <strong>${interaction.visitor_name}</strong> - ${interaction.purpose}<br>
                    <small class="text-muted">${interaction.date} | ${interaction.address}</small>
                </div>
            `;
        });
    } else {
        assignedSamplesHtml = '<div class="small text-muted">No interactions assigned</div>';
    }
    
    document.getElementById('sampleInteractionsCreated').innerHTML = createdSamplesHtml;
    document.getElementById('sampleInteractionsAssigned').innerHTML = assignedSamplesHtml;
    
    // Show all sections
    document.getElementById('deleteStatsSection').style.display = 'block';
    document.getElementById('roleSpecificInfo').style.display = 'block';
    document.getElementById('sampleDataSection').style.display = 'block';
    
    // Remove loading text from user info
    const userInfoDiv = document.getElementById('userToDeleteInfo');
    const loadingRow = userInfoDiv.querySelector('.fa-spinner').closest('.row');
    if (loadingRow) {
        loadingRow.remove();
    }
}

function showStatsError() {
    document.getElementById('userToDeleteInfo').innerHTML += `
        <div class="row mt-2">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Could not load impact statistics. Proceed with caution.
                </div>
            </div>
        </div>
    `;
}

function reactivateUser(userId, userName, userRole) {
    // Store user data for reactivation
    window.userToReactivate = {
        id: userId,
        name: userName,
        role: userRole
    };
    
    // Populate user info in modal
    document.getElementById('userToReactivateInfo').innerHTML = `
        <div class="row">
            <div class="col-4"><strong>Name:</strong></div>
            <div class="col-8">${userName}</div>
        </div>
        <div class="row">
            <div class="col-4"><strong>Role:</strong></div>
            <div class="col-8">
                <span class="badge bg-${userRole === 'admin' ? 'danger' : (userRole === 'frontdesk' ? 'info' : 'success')}">
                    ${userRole.charAt(0).toUpperCase() + userRole.slice(1)}
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-4"><strong>User ID:</strong></div>
            <div class="col-8">${userId}</div>
        </div>
    `;
    
    // Clear confirmation text
    document.getElementById('reactivateConfirmationText').value = '';
    
    // Show the reactivate confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('reactivateUserModal'));
    modal.show();
}

function confirmReactivateUser() {
    const confirmationText = document.getElementById('reactivateConfirmationText').value;
    const expectedText = 'REACTIVATE';
    
    if (confirmationText !== expectedText) {
        alert('Please type "REACTIVATE" exactly to confirm user reactivation.');
        return;
    }
    
    const user = window.userToReactivate;
    if (!user) {
        alert('Error: User data not found.');
        return;
    }
    
    // Show loading state
    const reactivateBtn = document.getElementById('confirmReactivateBtn');
    const originalText = reactivateBtn.innerHTML;
    reactivateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Reactivating...';
    reactivateBtn.disabled = true;
    
    // Send reactivate request
    fetch(`/admin/users/${user.id}/reactivate`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`User "${user.name}" has been reactivated successfully.`);
            location.reload(); // Refresh the page
        } else {
            alert('Error reactivating user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error reactivating user:', error);
        alert('Error reactivating user: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        reactivateBtn.innerHTML = originalText;
        reactivateBtn.disabled = false;
    });
}

function confirmDeactivateUser() {
    const confirmationText = document.getElementById('deleteConfirmationText').value;
    const expectedText = 'DEACTIVATE';
    
    if (confirmationText !== expectedText) {
        alert('Please type "DEACTIVATE" exactly to confirm user deactivation.');
        return;
    }
    
    const user = window.userToDeactivate;
    if (!user) {
        alert('Error: User data not found.');
        return;
    }
    
    // Show loading state
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deactivating...';
    deleteBtn.disabled = true;
    
    // Send deactivate request
    fetch(`/admin/users/${user.id}/deactivate`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`User "${user.name}" has been deactivated successfully.`);
            location.reload(); // Refresh the page
        } else {
            alert('Error deactivating user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deactivating user:', error);
        alert('Error deactivating user: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
        modal.hide();
        
        // Clear form
        document.getElementById('deleteConfirmationText').value = '';
    });
}
</script>
@endsection
