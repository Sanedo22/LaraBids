/**
 * Auction Create Form Validation
 * Provides client-side validation for auction creation form
 */

class AuctionFormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId) || document.querySelector('form[action*="auctions"]');
        this.errors = {};
        if (this.form) {
            this.init();
        }
    }

    init() {
        // Prevent default form submission for validation
        this.form.addEventListener('submit', (e) => {
            const isValid = this.validateForm();

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // Scroll to first error
                const firstError = this.form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }

                return false;
            }

            // If valid, allow form to submit normally
            return true;
        }, true);

        // Add real-time validation on blur and input
        const inputs = this.form.querySelectorAll('input[name], select[name], textarea[name]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });

        // Special handling for file inputs
        const imageInput = this.form.querySelector('input[name="image"]');
        if (imageInput) {
            imageInput.addEventListener('change', () => this.validateImage(imageInput));
        }

        const documentInput = this.form.querySelector('input[name="document"]');
        if (documentInput) {
            documentInput.addEventListener('change', () => this.validateDocument(documentInput));
        }
    }

    validateForm() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input[name]:not([type="file"]), select[name], textarea[name]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        // Validate file inputs
        const imageInput = this.form.querySelector('input[name="image"]');
        if (imageInput && imageInput.files.length > 0) {
            if (!this.validateImage(imageInput)) {
                isValid = false;
            }
        }

        const documentInput = this.form.querySelector('input[name="document"]');
        if (documentInput && documentInput.files.length > 0) {
            if (!this.validateDocument(documentInput)) {
                isValid = false;
            }
        }

        return isValid;
    }

    validateField(input) {
        const name = input.name;
        const value = input.value.trim();
        let error = null;

        // Clear previous error
        this.clearFieldError(input);

        // Skip validation for optional fields that are empty
        const requiredFields = ['title', 'category_id', 'description', 'starting_price', 'start_time', 'end_time'];
        if (!requiredFields.includes(name) && !value) {
            return true;
        }

        // Validation rules based on field name
        switch (name) {
            case 'title':
                error = this.validateTitle(value);
                break;
            case 'category_id':
                error = this.validateCategory(value);
                break;
            case 'description':
                error = this.validateDescription(value);
                break;
            case 'starting_price':
                error = this.validatePrice(value);
                break;
            case 'start_time':
                error = this.validateStartTime(value);
                break;
            case 'end_time':
                const startTime = this.form.querySelector('input[name="start_time"]').value;
                error = this.validateEndTime(value, startTime);
                break;
        }

        if (error) {
            this.showError(input, error);
            this.errors[name] = error;
            return false;
        }

        return true;
    }

    validateTitle(value) {
        if (!value) {
            return 'Item title is required.';
        }
        if (value.length < 3) {
            return 'Title must be at least 3 characters.';
        }
        if (value.length > 255) {
            return 'Title must not exceed 255 characters.';
        }
        return null;
    }

    validateCategory(value) {
        if (!value) {
            return 'Please select a category.';
        }
        return null;
    }

    validateDescription(value) {
        if (!value) {
            return 'Description is required.';
        }
        if (value.length < 20) {
            return 'Description must be at least 20 characters.';
        }
        if (value.length > 5000) {
            return 'Description must not exceed 5000 characters.';
        }
        return null;
    }

    validatePrice(value) {
        if (!value) {
            return 'Starting price is required.';
        }
        const price = parseFloat(value);
        if (isNaN(price)) {
            return 'Starting price must be a valid number.';
        }
        if (price < 0.01) {
            return 'Starting price must be at least $0.01.';
        }
        if (price > 999999999) {
            return 'Starting price is too high.';
        }
        return null;
    }

    validateStartTime(value) {
        if (!value) {
            return 'Auction start date and time is required.';
        }
        return null;
    }

    validateEndTime(value, startTime) {
        if (!value) {
            return 'Auction end date and time is required.';
        }
        if (startTime && value) {
            const start = new Date(startTime);
            const end = new Date(value);
            if (end <= start) {
                return 'End time must be after the start time.';
            }
        }
        return null;
    }

    validateImage(input) {
        if (!input.files || input.files.length === 0) {
            return true;
        }

        const file = input.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        let error = null;

        if (!allowedTypes.includes(file.type)) {
            error = 'Image must be a JPEG, PNG, JPG, or GIF file.';
        } else if (file.size > maxSize) {
            error = 'Image size must not exceed 2MB.';
        }

        if (error) {
            this.showError(input, error);
            this.errors['image'] = error;
            return false;
        }

        this.clearFieldError(input);
        return true;
    }

    validateDocument(input) {
        if (!input.files || input.files.length === 0) {
            return true;
        }

        const file = input.files[0];
        const allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        const maxSize = 5 * 1024 * 1024; // 5MB

        let error = null;

        if (!allowedTypes.includes(file.type)) {
            error = 'Document must be a PDF, JPG, PNG, DOC, or DOCX file.';
        } else if (file.size > maxSize) {
            error = 'Document size must not exceed 5MB.';
        }

        if (error) {
            this.showError(input, error);
            this.errors['document'] = error;
            return false;
        }

        this.clearFieldError(input);
        return true;
    }

    showError(input, message) {
        input.classList.add('is-invalid');

        // Create or update error message element
        let errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            if (input.parentElement.classList.contains('input-group')) {
                input.parentElement.parentElement.appendChild(errorDiv);
                errorDiv.classList.add('d-block');
            } else {
                input.parentElement.appendChild(errorDiv);
            }
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.invalid-feedback') ||
            input.parentElement.parentElement.querySelector('.invalid-feedback');
        if (errorDiv && !errorDiv.hasAttribute('data-server-error')) {
            errorDiv.style.display = 'none';
        }
    }
}

// Export for manual initialization
// No automatic initialization - must be called manually from the page
