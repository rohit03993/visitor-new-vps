@extends('layouts.app')

@section('title', 'File Management - Task Book')
@section('page-title', 'File Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-folder-open me-2"></i>File Management
                        </h5>
                        <small class="opacity-75">Manage file transfers from server to Google Drive</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button onclick="refreshFileStatus()" class="btn btn-outline-success btn-sm" title="Refresh status">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <button onclick="bulkTransferSelected()" class="btn btn-warning btn-sm" id="bulkTransferBtn" disabled>
                            <i class="fas fa-cloud-upload-alt me-1"></i>Transfer Selected
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-paytm-body">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ $stats['total_files'] }}</h4>
                                <small>Total Files</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ $stats['server_files'] }}</h4>
                                <small>On Server</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ $stats['drive_files'] }}</h4>
                                <small>On Drive</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ $stats['pending_files'] }}</h4>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ $stats['failed_files'] }}</h4>
                                <small>Failed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-1">{{ number_format($stats['total_size'] / 1024 / 1024, 1) }} MB</h4>
                                <small>Total Size</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Management Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Uploaded By</th>
                                <th>Upload Date</th>
                                <th>Transferred By</th>
                                <th>Transfer Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($files as $file)
                                <tr>
                                    <td>
                                        @if($file->status === 'server')
                                            <input type="checkbox" class="file-checkbox" value="{{ $file->id }}" onchange="updateBulkTransferBtn()">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file me-2 text-muted"></i>
                                            <div>
                                                <strong>{{ $file->original_filename }}</strong>
                                                @if($file->interaction)
                                                    <br><small class="text-muted">Interaction: {{ $file->interaction->purpose }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $file->getFileSizeFormattedAttribute() }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ strtoupper($file->file_type) }}</span>
                                    </td>
                                    <td>
                                        @if($file->status === 'server')
                                            <span class="badge bg-info">On Server</span>
                                        @elseif($file->status === 'drive')
                                            <span class="badge bg-success">On Drive</span>
                                        @elseif($file->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($file->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user me-2 text-muted"></i>
                                            <div>
                                                <strong>{{ $file->uploadedBy->name ?? 'Unknown' }}</strong>
                                                @if($file->uploadedBy && $file->uploadedBy->branch)
                                                    <br><small class="text-muted">{{ $file->uploadedBy->branch->branch_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $file->created_at->format('M d, Y') }}</small>
                                        <br><small class="text-muted">{{ $file->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($file->transferredBy)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user me-2 text-muted"></i>
                                                <div>
                                                    <strong>{{ $file->transferredBy->name }}</strong>
                                                    @if($file->transferredBy->branch)
                                                        <br><small class="text-muted">{{ $file->transferredBy->branch->branch_name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($file->transferred_at)
                                            <small>{{ $file->transferred_at->format('M d, Y') }}</small>
                                            <br><small class="text-muted">{{ $file->transferred_at->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($file->status === 'server')
                                                <button class="btn btn-sm btn-warning" onclick="transferSingleFile({{ $file->id }})" title="Transfer to Drive">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </button>
                                            @endif
                                            <a href="{{ $file->getFileUrlAttribute() }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View File">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($file->status === 'drive')
                                                <a href="{{ $file->google_drive_url }}" target="_blank" class="btn btn-sm btn-outline-success" title="Open in Drive">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No files found</h5>
                                        <p class="text-muted">Files will appear here once users start uploading.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $files->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Progress Modal -->
<div class="modal fade" id="transferProgressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Transferring Files
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6>Transferring files to Google Drive...</h6>
                    <p class="text-muted">Please wait while files are being uploaded.</p>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="transferStatus" class="text-muted"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Select All functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.file-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkTransferBtn();
}

// Update bulk transfer button state
function updateBulkTransferBtn() {
    const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
    const bulkBtn = document.getElementById('bulkTransferBtn');
    
    if (selectedFiles.length > 0) {
        bulkBtn.disabled = false;
        bulkBtn.textContent = `Transfer Selected (${selectedFiles.length})`;
    } else {
        bulkBtn.disabled = true;
        bulkBtn.textContent = 'Transfer Selected';
    }
}

// Transfer single file
function transferSingleFile(fileId) {
    if (!confirm('Are you sure you want to transfer this file to Google Drive?')) {
        return;
    }

    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    fetch('/admin/transfer-files-to-drive', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ file_id: fileId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to transfer file');
    })
    .finally(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
}

// Bulk transfer selected files
function bulkTransferSelected() {
    const selectedFiles = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);
    
    if (selectedFiles.length === 0) {
        showAlert('warning', 'Please select files to transfer');
        return;
    }

    if (!confirm(`Are you sure you want to transfer ${selectedFiles.length} files to Google Drive?`)) {
        return;
    }

    // Show progress modal
    const modal = new bootstrap.Modal(document.getElementById('transferProgressModal'));
    modal.show();

    fetch('/admin/bulk-transfer-files', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ file_ids: selectedFiles })
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();
        
        if (data.success) {
            showAlert('success', data.message);
            if (data.errors && data.errors.length > 0) {
                console.log('Transfer errors:', data.errors);
            }
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        showAlert('error', 'Failed to transfer files');
    });
}

// Refresh file status
function refreshFileStatus() {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
    btn.disabled = true;

    fetch('/admin/file-management/status')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update statistics cards
            updateStatsCards(data.stats);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to refresh status');
    })
    .finally(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
}

// Update statistics cards
function updateStatsCards(stats) {
    // This would update the stats cards dynamically
    // For now, we'll just reload the page
    location.reload();
}

// Show alert
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-warning';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(alert => alert.remove());
    
    // Add new alert
    document.querySelector('.card-paytm-body').insertAdjacentHTML('afterbegin', alertHtml);
    
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
