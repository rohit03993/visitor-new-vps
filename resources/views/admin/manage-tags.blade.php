@extends('layouts.app')

@section('title', 'Edit Purpose - VMS')
@section('page-title', 'Edit Purpose')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <div>
                <small class="text-muted">Total {{ $tags->total() }} tags found</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
                    <i class="fas fa-plus me-1"></i>Create New Tag
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tags me-2"></i>Purpose Management
                </h5>
            </div>
            <div class="card-body">
                @if($tags->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tag Name</th>
                                    <th>Color</th>
                                    <th>Description</th>
                                    <th>Usage Count</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tags as $tag)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: {{ $tag->display_color }}; color: white;">
                                                {{ $tag->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: {{ $tag->display_color }}; border-radius: 3px;"></div>
                                                <small class="text-muted">{{ $tag->display_color }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $tag->description ?? 'No description' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $tag->visitors_count }} visitors</span>
                                        </td>
                                        <td>
                                            @if($tag->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ \App\Helpers\DateTimeHelper::formatIndianDate($tag->created_at) }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" onclick="editTag({{ $tag->id }}, '{{ addslashes($tag->name) }}', '{{ $tag->display_color }}', '{{ addslashes($tag->description) }}')" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-{{ $tag->is_active ? 'warning' : 'success' }}" onclick="toggleTagStatus({{ $tag->id }})" title="{{ $tag->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $tag->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                @if($tag->visitors_count == 0)
                                                    <button class="btn btn-outline-danger" onclick="deleteTag({{ $tag->id }}, '{{ $tag->name }}')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-secondary" disabled title="Cannot delete - in use">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-lg-none">
                        @foreach($tags as $tag)
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <!-- Header with Tag Name and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <span class="badge" style="background-color: {{ $tag->display_color }}; color: white; font-size: 0.9rem;">
                                                {{ $tag->name }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            @if($tag->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Color and Usage -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Color:</small>
                                            <div class="d-flex align-items-center">
                                                <div class="color-preview me-2" style="width: 16px; height: 16px; background-color: {{ $tag->display_color }}; border-radius: 3px;"></div>
                                                <small>{{ $tag->display_color }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Usage:</small>
                                            <div><span class="badge bg-info">{{ $tag->visitors_count }} visitors</span></div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Description:</small>
                                            <div><small>{{ $tag->description ?? 'No description' }}</small></div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-outline-primary btn-sm" onclick="editTag({{ $tag->id }}, '{{ addslashes($tag->name) }}', '{{ $tag->display_color }}', '{{ addslashes($tag->description) }}')">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                                <button class="btn btn-outline-{{ $tag->is_active ? 'warning' : 'success' }} btn-sm" onclick="toggleTagStatus({{ $tag->id }})">
                                                    <i class="fas fa-{{ $tag->is_active ? 'pause' : 'play' }} me-1"></i>{{ $tag->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                @if($tag->visitors_count == 0)
                                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteTag({{ $tag->id }}, '{{ $tag->name }}')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                                        <i class="fas fa-lock me-1"></i>In Use
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        @include('components.pagination', ['paginator' => $tags])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No tags found</h5>
                        <p class="text-muted">Create your first tag to get started.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
                            <i class="fas fa-plus me-1"></i>Create First Tag
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Tag Modal -->
<div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTagModalLabel">Create New Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTagForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tagName" class="form-label">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tagName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="tagColor" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="tagColor" name="color" value="#007bff">
                            <input type="text" class="form-control" id="tagColorText" value="#007bff" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tagDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="tagDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTagForm">
                <input type="hidden" id="editTagId" name="tag_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTagName" class="form-label">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editTagName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTagColor" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="editTagColor" name="color">
                            <input type="text" class="form-control" id="editTagColorText" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editTagDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTagDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.color-preview {
    border: 1px solid #ddd;
}
</style>

<script>
// Color picker synchronization
document.getElementById('tagColor').addEventListener('input', function() {
    document.getElementById('tagColorText').value = this.value;
});

document.getElementById('editTagColor').addEventListener('input', function() {
    document.getElementById('editTagColorText').value = this.value;
});

// Create Tag Form
document.getElementById('createTagForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.create-tag") }}', {
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
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'An error occurred while creating the tag.');
    });
});

// Edit Tag Function
function editTag(id, name, color, description) {
    console.log('editTag called with:', {id, name, color, description});
    
    // Set form values
    document.getElementById('editTagId').value = id;
    document.getElementById('editTagName').value = name;
    document.getElementById('editTagColor').value = color;
    document.getElementById('editTagColorText').value = color;
    document.getElementById('editTagDescription').value = description || '';
    
    // Verify values were set
    console.log('Form values set:', {
        id: document.getElementById('editTagId').value,
        name: document.getElementById('editTagName').value,
        color: document.getElementById('editTagColor').value,
        description: document.getElementById('editTagDescription').value
    });
    
    new bootstrap.Modal(document.getElementById('editTagModal')).show();
}

// Edit Tag Form
document.getElementById('editTagForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tagId = document.getElementById('editTagId').value;
    const name = document.getElementById('editTagName').value;
    const color = document.getElementById('editTagColor').value;
    const description = document.getElementById('editTagDescription').value;
    
    // Debug: Log form data
    console.log('Form submission data:', {
        tagId: tagId,
        name: name,
        color: color,
        description: description
    });
    
    // Use POST method with _method=PUT for Laravel
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', name);
    formData.append('color', color);
    formData.append('description', description);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/update-tag/${tagId}?t=${Date.now()}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showAlert('error', 'An error occurred while updating the tag.');
    });
});

// Toggle Tag Status
function toggleTagStatus(tagId) {
    if (confirm('Are you sure you want to toggle this tag\'s status?')) {
        fetch(`/admin/toggle-tag-status/${tagId}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
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
            showAlert('error', 'An error occurred while updating the tag status.');
        });
    }
}

// Delete Tag
function deleteTag(tagId, tagName) {
    if (confirm(`Are you sure you want to delete the tag "${tagName}"? This action cannot be undone.`)) {
        fetch(`/admin/delete-tag/${tagId}`, {
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
            showAlert('error', 'An error occurred while deleting the tag.');
        });
    }
}

// Alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endsection
