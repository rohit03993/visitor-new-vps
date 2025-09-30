@extends('layouts.app')

@section('title', 'App Settings - Task Book')
@section('page-title', 'App Settings')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Setup Required Alert -->
        @if(isset($tableNotExists) && $tableNotExists)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h5>
            <p class="mb-2">The settings table hasn't been created yet. To enable logo upload feature, run this command:</p>
            <code class="bg-dark text-white p-2 d-block mb-2">php artisan migrate</code>
            <p class="mb-0"><strong>Note:</strong> Your CRM is working fine. This is only needed for the logo upload feature.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog me-2"></i>Application Settings
                </h5>
            </div>
            <div class="card-paytm-body">
                <!-- Current Logo Preview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3"><i class="fas fa-image me-2"></i>Current Logo</h6>
                        <div class="text-center p-4" style="background: #f8f9fa; border-radius: 10px;">
                            <img src="{{ asset($currentLogo) }}" alt="Current Logo" style="max-width: 300px; height: auto;">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Upload New Logo Form -->
                <form action="{{ route('admin.upload-logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fas fa-upload me-2"></i>Upload New Logo</h6>
                            <div class="mb-3">
                                <label for="logo" class="form-label-paytm">Select Logo Image</label>
                                <input type="file" class="form-control-paytm" id="logo" name="logo" accept="image/*" required>
                                <div class="form-text">Supported formats: JPG, PNG, SVG (Max: 2MB)</div>
                            </div>
                            
                            <!-- Preview -->
                            <div id="preview-container" class="mb-3" style="display: none;">
                                <label class="form-label-paytm">Preview:</label>
                                <div class="text-center p-3" style="background: #f8f9fa; border-radius: 10px;">
                                    <img id="preview-image" src="" alt="Preview" style="max-width: 300px; height: auto;">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-paytm-primary">
                                <i class="fas fa-upload me-2"></i>Upload Logo
                            </button>
                        </div>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Information -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> The logo will appear on:
                    <ul class="mb-0 mt-2">
                        <li>Login page</li>
                        <li>Sidebar navigation</li>
                        <li>All authenticated pages</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Image preview functionality
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
