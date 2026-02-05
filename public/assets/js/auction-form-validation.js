// Auction Create Form Validation

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

                // Scroll to first error
                const firstError = this.form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Only focus if it's a text input or textarea, avoid selects to prevent auto-opening
                    if (firstError.tagName === 'INPUT' && (firstError.type === 'text' || firstError.type === 'number' || firstError.type === 'datetime-local')) {
                        firstError.focus();
                    } else if (firstError.tagName === 'TEXTAREA') {
                        firstError.focus();
                    }
                }

                return false;
            }

            // If valid, allow form to submit normally
            return true;
        });

        // Add real-time validation on blur and input
        const inputs = this.form.querySelectorAll('input[name], select[name], textarea[name]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });

        // Special handling for file inputs
        const imageInput = this.form.querySelector('input[name="images[]"]');
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
        const imageInput = this.form.querySelector('input[name="images[]"]');
        if (imageInput) {
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
        // Handle input names like specifications[year]
        const cleanName = name.split('[')[0];
        const value = input.value.trim();
        let error = null;

        // Clear previous error
        this.clearFieldError(input);

        // Skip validation for optional fields that are empty
        const requiredFields = ['title', 'category_id', 'description', 'starting_price', 'start_time', 'end_time'];
        if (!requiredFields.includes(cleanName) && !value) {
            return true;
        }

        // Validation rules based on field name
        switch (cleanName) {
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
                const startTimeInput = this.form.querySelector('input[name="start_time"]');
                error = this.validateEndTime(value, startTimeInput ? startTimeInput.value : null);
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
        if (value.length > 100) {
            return 'Title must not exceed 100 characters.';
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
            const error = 'At least one image is required.';
            this.showError(input, error);
            this.errors['images'] = error;
            return false;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        let error = null;

        if (input.files.length > 5) {
            error = 'You cannot upload more than 5 images.';
        } else {
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                if (!allowedTypes.includes(file.type)) {
                    error = `File "${file.name}" is not a valid image.`;
                    break;
                } else if (file.size > maxSize) {
                    error = `Image "${file.name}" exceeds 2MB limit.`;
                    break;
                }
            }
        }

        if (error) {
            this.showError(input, error);
            this.errors['images'] = error;
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

        // Target for displaying error
        let target = input;

        // If it's an image input, we might want to target the wrapper or specialized grid
        if (input.id === 'imageInput') {
            const wrapper = input.closest('.image-upload-wrapper');
            if (wrapper) target = wrapper;
        }

        // Create or update error message element
        let errorDiv = target.parentElement.querySelector('.invalid-feedback');

        // Special case for input-group (like price)
        if (input.parentElement.classList.contains('input-group')) {
            errorDiv = input.parentElement.parentElement.querySelector('.invalid-feedback');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block';
                input.parentElement.parentElement.appendChild(errorDiv);
            }
        } else if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            target.parentElement.appendChild(errorDiv);
        }

        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        errorDiv.removeAttribute('data-server-error'); // Client side error takes precedence during validation
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        let target = input;
        if (input.id === 'imageInput') {
            const wrapper = input.closest('.image-upload-wrapper');
            if (wrapper) target = wrapper;
        }

        const errorDiv = target.parentElement.querySelector('.invalid-feedback') ||
            (target.parentElement.parentElement ? target.parentElement.parentElement.querySelector('.invalid-feedback') : null);

        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
}

// Export for manual initialization
// No automatic initialization - must be called manually from the page
