@extends('layouts.app')

@section('title', 'Change Password - Log Book')
@section('page-title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('staff.change-password') }}" id="changePasswordForm">
                    @csrf
                    
                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Current Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   value="{{ old('current_password') }}"
                                   required 
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key me-1"></i>New Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" 
                                   name="new_password" 
                                   value="{{ old('new_password') }}"
                                   required 
                                   minlength="6"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Password must be at least 6 characters long
                            </small>
                        </div>
                        @error('new_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label">
                            <i class="fas fa-check-circle me-1"></i>Confirm New Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                   id="new_password_confirmation" 
                                   name="new_password_confirmation" 
                                   value="{{ old('new_password_confirmation') }}"
                                   required 
                                   minlength="6"
                                   autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                            </button>
                        </div>
                        @error('new_password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <small class="strength-text" id="strengthText">Enter a password</small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.password-strength {
    margin-top: 10px;
}

.strength-bar {
    width: 100%;
    height: 8px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 4px;
}

.strength-fill.weak {
    background-color: #dc3545;
    width: 25%;
}

.strength-fill.fair {
    background-color: #ffc107;
    width: 50%;
}

.strength-fill.good {
    background-color: #17a2b8;
    width: 75%;
}

.strength-fill.strong {
    background-color: #28a745;
    width: 100%;
}

.strength-text {
    font-size: 12px;
    color: #6c757d;
}

.input-group .btn {
    border-color: #ced4da;
}

.input-group .btn:hover {
    background-color: #e9ecef;
    border-color: #ced4da;
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    let strengthLabel = '';
    
    // Length check
    if (password.length >= 6) strength += 1;
    if (password.length >= 8) strength += 1;
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Update strength indicator
    strengthFill.className = 'strength-fill';
    
    if (password.length === 0) {
        strengthText.textContent = 'Enter a password';
        strengthFill.style.width = '0%';
    } else if (strength <= 2) {
        strengthFill.classList.add('weak');
        strengthText.textContent = 'Weak password';
        strengthText.style.color = '#dc3545';
    } else if (strength <= 4) {
        strengthFill.classList.add('fair');
        strengthText.textContent = 'Fair password';
        strengthText.style.color = '#ffc107';
    } else if (strength <= 5) {
        strengthFill.classList.add('good');
        strengthText.textContent = 'Good password';
        strengthText.style.color = '#17a2b8';
    } else {
        strengthFill.classList.add('strong');
        strengthText.textContent = 'Strong password';
        strengthText.style.color = '#28a745';
    }
});

// Form validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('New password must be at least 6 characters long!');
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Changing Password...';
    submitBtn.disabled = true;
});
</script>
@endsection
