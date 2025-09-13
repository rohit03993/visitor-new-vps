@extends('layouts.app')

@section('title', 'Manage Courses - VMS')
@section('page-title', 'Course Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Course Management
                </h5>
                <button type="button" class="btn btn-paytm-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                    <i class="fas fa-plus me-1"></i>Add New Course
                </button>
            </div>
            <div class="card-paytm-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Code</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Students Count</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr>
                                    <td>
                                        <strong>{{ $course->course_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $course->course_code }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($course->description, 50) }}</span>
                                    </td>
                                    <td>
                                        @if($course->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $course->visitors->count() }} student(s)</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $course->creator->name ?? 'System' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $course->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCourse({{ $course->course_id }}, '{{ addslashes($course->course_name) }}', '{{ addslashes($course->course_code) }}', '{{ addslashes($course->description) }}', {{ $course->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            @if($course->visitors->count() == 0)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteCourse({{ $course->course_id }}, '{{ addslashes($course->course_name) }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled 
                                                        title="Cannot delete - course is being used by {{ $course->visitors->count() }} student(s)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                        <p>No courses found. Create your first course to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($courses->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $courses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Add New Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCourseForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name *</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                        <div class="form-text">Enter a unique course name</div>
                    </div>
                    <div class="mb-3">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="course_code" name="course_code" maxlength="50">
                        <div class="form-text">Leave empty to auto-generate from course name</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"></textarea>
                        <div class="form-text">Optional description of the course</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCourseForm">
                <input type="hidden" id="edit_course_id" name="course_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_course_name" class="form-label">Course Name *</label>
                        <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                        <div class="form-text">Enter a unique course name</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="edit_course_code" name="course_code" maxlength="50">
                        <div class="form-text">Leave empty to auto-generate from course name</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" maxlength="500"></textarea>
                        <div class="form-text">Optional description of the course</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Active Course
                            </label>
                        </div>
                        <div class="form-text">Inactive courses won't appear in the course selection dropdown</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Create Course
document.getElementById('createCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.create-course") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('createCourseModal')).hide();
            this.reset();
            location.reload();
        } else {
            showAlert('error', data.message || 'An error occurred while creating the course.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while creating the course.');
    });
});

// Edit Course
function editCourse(courseId, courseName, courseCode, description, isActive) {
    document.getElementById('edit_course_id').value = courseId;
    document.getElementById('edit_course_name').value = courseName;
    document.getElementById('edit_course_code').value = courseCode;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_is_active').checked = isActive;
    
    const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    modal.show();
}

document.getElementById('editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const courseId = document.getElementById('edit_course_id').value;
    const formData = new FormData(this);
    
    fetch(`{{ url('admin/update-course') }}/${courseId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editCourseModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message || 'An error occurred while updating the course.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while updating the course.');
    });
});

// Delete Course
function deleteCourse(courseId, courseName) {
    if (confirm(`Are you sure you want to delete the course "${courseName}"? This action cannot be undone.`)) {
        fetch(`{{ url('admin/delete-course') }}/${courseId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'DELETE'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'An error occurred while deleting the course.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while deleting the course.');
        });
    }
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(alert => alert.remove());
    
    // Add new alert
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection
