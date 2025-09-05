@extends('layouts.app')

@section('title', 'Visitor Search - VMS')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-search text-primary me-2"></i>
                        Visitor Search
                    </h2>
                    <p class="text-muted mb-0">Search for existing visitors or add new ones</p>
                </div>
                <div>
                    <a href="{{ route('frontdesk.old-dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar me-1"></i>View Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Google-like Search Box -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <!-- Search Logo/Icon -->
                    <div class="text-center mb-4">
                        <div class="search-icon mb-3">
                            <i class="fas fa-user-search text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-muted">Search Visitor</h4>
                        <p class="text-muted">Enter mobile number to find existing visitor or add new one</p>
                    </div>

                    <!-- Search Form -->
                    <form action="{{ route('frontdesk.search-visitor') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-mobile-alt text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 border-end-0" 
                                   id="mobileSearchInput" 
                                   name="mobile_number"
                                   placeholder="Enter 10-digit mobile number (e.g., 9876543210)"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   required>
                            <button class="btn btn-primary border-start-0" type="submit" id="searchBtn">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                        <div class="form-text text-center mt-2">
                            <small class="text-muted">Enter exactly 10 digits without +91 or spaces</small>
                        </div>
                    </form>

                    <!-- Error Display -->
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileInput = document.getElementById('mobileSearchInput');

    // Mobile number validation
    mobileInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        this.value = value;
    });

    // Auto-focus on mobile input
    mobileInput.focus();
});
</script>
@endsection

@section('styles')
<style>
.search-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.input-group-lg .form-control {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.input-group-lg .input-group-text {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.input-group-lg .btn {
    font-size: 1.1rem;
    padding: 0.75rem 1.5rem;
}

.card {
    border-radius: 15px;
}

.btn {
    border-radius: 10px;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endsection
