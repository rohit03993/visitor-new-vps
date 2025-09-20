<!-- File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paperclip me-2"></i>Upload File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="fileUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="upload_interaction_id" name="interaction_id">
                    
                    <!-- File Upload Area -->
                    <div class="upload-area" onclick="openFileDialog()">
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <h6>Drag & Drop Files Here</h6>
                            <p class="text-muted mb-3">or click to browse</p>
                            <input type="file" id="fileInput" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp3,.wav" style="display: none;" onchange="handleFileSelection()">
                            <button type="button" class="btn btn-outline-primary" onclick="event.stopPropagation(); openFileDialog();">
                                <i class="fas fa-folder-open me-1"></i>Browse Files
                            </button>
                        </div>
                    </div>
                    
                    <!-- File Info -->
                    <div id="fileInfo" class="file-info mt-3" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i id="fileIcon" class="fas fa-file me-2"></i>
                            <div class="flex-grow-1">
                                <div id="fileName" class="fw-bold"></div>
                                <div id="fileSize" class="text-muted small"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- File Type Limits -->
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle me-1"></i>File Limits:</h6>
                        <small>
                            <strong>PDF:</strong> 5MB max &nbsp;|&nbsp;
                            <strong>Images:</strong> 2MB max &nbsp;|&nbsp;
                            <strong>Audio:</strong> 10MB max<br>
                            <strong>Supported:</strong> PDF, JPG, PNG, WebP, MP3, WAV
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="uploadBtn" disabled onclick="submitFileUpload()">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #28a745;
    background-color: #f8fff9;
}

.upload-area.dragover {
    border-color: #28a745;
    background-color: #e8f5e8;
    transform: scale(1.02);
}

.upload-icon {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.file-info {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
}
</style>

<script>

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Simple file upload handling - no complex event listeners
function handleFileSelection() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput && fileInput.files.length > 0) {
        displayFileInfo(fileInput.files[0]);
    }
}

function openFileDialog() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.click();
    }
}

function displayFileInfo(file) {
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');
    const fileInfo = document.getElementById('fileInfo');
    const uploadBtn = document.getElementById('uploadBtn');
    
    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);
    
    // Set icon based on file type
    const extension = file.name.split('.').pop().toLowerCase();
    switch(extension) {
        case 'pdf':
            fileIcon.className = 'fas fa-file-pdf text-danger me-2';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'webp':
            fileIcon.className = 'fas fa-file-image text-primary me-2';
            break;
        case 'mp3':
        case 'wav':
            fileIcon.className = 'fas fa-file-audio text-success me-2';
            break;
        default:
            fileIcon.className = 'fas fa-file text-secondary me-2';
    }
    
    fileInfo.style.display = 'block';
    uploadBtn.disabled = false;
}
</script>