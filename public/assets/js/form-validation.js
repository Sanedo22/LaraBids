/**
 * Form Validation Utility
 * Provides client-side validation for login and register forms
 */

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        this.init();
    }

    init() {
        if (!this.form) return;

        // Prevent default form submission for validation
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
            }
        });

        // Add real-time validation on blur
        const inputs = this.form.querySelectorAll('input[name]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    validateForm() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input[name]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(input) {
        const name = input.name;
        const value = input.value.trim();
        let error = null;

        // Clear previous error
        this.clearFieldError(input);

        // Validation rules based on field name
        switch (name) {
            case 'name':
                error = this.validateName(value);
                break;
            case 'email':
                error = this.validateEmail(value);
                break;
            case 'password':
                error = this.validatePassword(value, input.form.id === 'registerForm');
                break;
            case 'password_confirmation':
                const password = input.form.querySelector('input[name="password"]').value;
                error = this.validatePasswordConfirmation(value, password);
                break;
        }

        if (error) {
            this.showError(input, error);
            this.errors[name] = error;
            return false;
        }

        return true;
    }

    validateName(value) {
        if (!value) {
            return 'Full name is required.';
        }
        if (value.length < 2) {
            return 'Name must be at least 2 characters.';
        }
        if (value.length > 255) {
            return 'Name must not exceed 255 characters.';
        }
        if (!/^[a-zA-Z\s'-]+$/.test(value)) {
            return 'Name can only contain letters, spaces, hyphens, and apostrophes.';
        }
        return null;
    }

    validateEmail(value) {
        if (!value) {
            return 'Email address is required.';
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return 'Please enter a valid email address.';
        }
        if (value.length > 255) {
            return 'Email must not exceed 255 characters.';
        }
        return null;
    }

    validatePassword(value, isRegistration = false) {
        if (!value) {
            return 'Password is required.';
        }
        if (isRegistration) {
            if (value.length < 8) {
                return 'Password must be at least 8 characters.';
            }
            if (!/[a-z]/.test(value)) {
                return 'Password must contain at least one lowercase letter.';
            }
            if (!/[A-Z]/.test(value)) {
                return 'Password must contain at least one uppercase letter.';
            }
            if (!/[0-9]/.test(value)) {
                return 'Password must contain at least one number.';
            }
        }
        return null;
    }

    validatePasswordConfirmation(value, password) {
        if (!value) {
            return 'Password confirmation is required.';
        }
        if (value !== password) {
            return 'Passwords do not match.';
        }
        return null;
    }

    showError(input, message) {
        input.classList.add('is-invalid');
        
        // Create or update error message element
        let errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (errorDiv && !errorDiv.hasAttribute('data-server-error')) {
            errorDiv.style.display = 'none';
        }
    }
}

/**
 * Password Strength Indicator
 */
class PasswordStrengthIndicator {
    constructor(passwordInputId, indicatorId) {
        this.passwordInput = document.getElementById(passwordInputId);
        this.indicator = document.getElementById(indicatorId);
        
        if (this.passwordInput && this.indicator) {
            this.init();
        }
    }

    init() {
        this.passwordInput.addEventListener('input', () => {
            this.updateStrength();
        });
    }

    calculateStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 10;
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
        
        return Math.min(strength, 100);
    }

    getStrengthLabel(strength) {
        if (strength === 0) return { label: '', color: '' };
        if (strength < 40) return { label: 'Weak', color: 'danger' };
        if (strength < 70) return { label: 'Fair', color: 'warning' };
        if (strength < 90) return { label: 'Good', color: 'info' };
        return { label: 'Strong', color: 'success' };
    }

    updateStrength() {
        const password = this.passwordInput.value;
        const strength = this.calculateStrength(password);
        const { label, color } = this.getStrengthLabel(strength);

        // Update progress bar
        const progressBar = this.indicator.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${strength}%`;
            progressBar.className = `progress-bar bg-${color}`;
            progressBar.setAttribute('aria-valuenow', strength);
        }

        // Update label
        const labelElement = this.indicator.querySelector('.strength-label');
        if (labelElement) {
            labelElement.textContent = label;
            labelElement.className = `strength-label text-${color} small fw-bold`;
        }

        // Show/hide indicator
        this.indicator.style.display = password.length > 0 ? 'block' : 'none';
    }
}

// Initialize validators when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize login form validation
    if (document.getElementById('loginForm')) {
        new FormValidator('loginForm');
    }

    // Initialize register form validation
    if (document.getElementById('registerForm')) {
        new FormValidator('registerForm');
        new PasswordStrengthIndicator('password', 'passwordStrength');
    }
});
