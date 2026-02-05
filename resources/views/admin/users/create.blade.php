@extends('admin.layouts.admin')

@section('title', 'Create User - LaraBids')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Create New User</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" id="userCreateForm" novalidate>
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" placeholder="Enter full name">
                            <div class="invalid-feedback" id="name-error"></div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Enter email address">
                            <div class="invalid-feedback" id="email-error"></div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password">
                            <small class="form-text text-muted">Minimum 8 characters</small>
                            <div class="invalid-feedback" id="password-error"></div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                            <div class="invalid-feedback" id="password_confirmation-error"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">User Role <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
                                <option value="" disabled selected>Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="role-error"></div>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-sm text-white-50 mr-1"></i> Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userCreateForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const roleSelect = document.getElementById('role');

    // Validation functions
    function validateName() {
        const value = nameInput.value.trim();
        const errorDiv = document.getElementById('name-error');
        
        if (value === '') {
            nameInput.classList.add('is-invalid');
            errorDiv.textContent = 'Full name is required.';
            return false;
        } else if (value.length < 2) {
            nameInput.classList.add('is-invalid');
            errorDiv.textContent = 'Name must be at least 2 characters.';
            return false;
        } else if (value.length > 255) {
            nameInput.classList.add('is-invalid');
            errorDiv.textContent = 'Name must not exceed 255 characters.';
            return false;
        } else {
            nameInput.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        }
    }

    function validateEmail() {
        const value = emailInput.value.trim();
        const errorDiv = document.getElementById('email-error');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value === '') {
            emailInput.classList.add('is-invalid');
            errorDiv.textContent = 'Email address is required.';
            return false;
        } else if (!emailRegex.test(value)) {
            emailInput.classList.add('is-invalid');
            errorDiv.textContent = 'Please enter a valid email address.';
            return false;
        } else if (value.length > 255) {
            emailInput.classList.add('is-invalid');
            errorDiv.textContent = 'Email must not exceed 255 characters.';
            return false;
        } else {
            emailInput.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        }
    }

    function validatePassword() {
        const value = passwordInput.value;
        const errorDiv = document.getElementById('password-error');
        
        if (value === '') {
            passwordInput.classList.add('is-invalid');
            errorDiv.textContent = 'Password is required.';
            return false;
        } else if (value.length < 8) {
            passwordInput.classList.add('is-invalid');
            errorDiv.textContent = 'Password must be at least 8 characters.';
            return false;
        } else {
            passwordInput.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        }
    }

    function validatePasswordConfirmation() {
        const password = passwordInput.value;
        const confirmation = passwordConfirmInput.value;
        const errorDiv = document.getElementById('password_confirmation-error');
        
        if (confirmation === '') {
            passwordConfirmInput.classList.add('is-invalid');
            errorDiv.textContent = 'Please confirm your password.';
            return false;
        } else if (password !== confirmation) {
            passwordConfirmInput.classList.add('is-invalid');
            errorDiv.textContent = 'Passwords do not match.';
            return false;
        } else {
            passwordConfirmInput.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        }
    }

    function validateRole() {
        const value = roleSelect.value;
        const errorDiv = document.getElementById('role-error');
        
        if (value === '' || value === null) {
            roleSelect.classList.add('is-invalid');
            errorDiv.textContent = 'Please select a user role.';
            return false;
        } else {
            roleSelect.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        }
    }

    // Real-time validation
    nameInput.addEventListener('blur', validateName);
    nameInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateName();
        }
    });

    emailInput.addEventListener('blur', validateEmail);
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateEmail();
        }
    });

    passwordInput.addEventListener('blur', validatePassword);
    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validatePassword();
        }
        // Also revalidate confirmation if it has been filled
        if (passwordConfirmInput.value !== '') {
            validatePasswordConfirmation();
        }
    });

    passwordConfirmInput.addEventListener('blur', validatePasswordConfirmation);
    passwordConfirmInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') || this.value !== '') {
            validatePasswordConfirmation();
        }
    });

    roleSelect.addEventListener('change', validateRole);
    roleSelect.addEventListener('blur', validateRole);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isPasswordConfirmValid = validatePasswordConfirmation();
        const isRoleValid = validateRole();

        if (!isNameValid || !isEmailValid || !isPasswordValid || !isPasswordConfirmValid || !isRoleValid) {
            e.preventDefault();
            
            // Focus on first invalid field
            if (!isNameValid) {
                nameInput.focus();
            } else if (!isEmailValid) {
                emailInput.focus();
            } else if (!isPasswordValid) {
                passwordInput.focus();
            } else if (!isPasswordConfirmValid) {
                passwordConfirmInput.focus();
            } else if (!isRoleValid) {
                roleSelect.focus();
            }
        }
    });
});
</script>
@endpush
