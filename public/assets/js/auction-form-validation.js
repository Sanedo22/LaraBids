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
            input.addEventListener('change', () => this.validateField(input)); // Add change listener for selects and hidden inputs
        });

        // Special handling for file inputs
        const imageInput = this.form.querySelector('input[name="images[]"]');
        if (imageInput) {
            imageInput.addEventListener('change', () => this.validateImage(imageInput));
            imageInput.addEventListener('filesUpdated', () => this.validateImage(imageInput));
        }

        const documentInputs = this.form.querySelectorAll('input[name="document"]');
        documentInputs.forEach(input => {
            input.addEventListener('change', () => this.validateDocument(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    validateForm() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input[name]:not([type="file"]), select[name], textarea[name]');
        let isValid = true;

        inputs.forEach(input => {
            // Check if input is visible before validating
            if (input.offsetParent !== null || input.type === 'hidden') {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            }
        });

        // Validate file inputs
        const imageInput = this.form.querySelector('input[name="images[]"]');
        if (imageInput) {
            if (!this.validateImage(imageInput)) {
                isValid = false;
            }
        }

        const documentInputs = this.form.querySelectorAll('input[name="document"]');
        documentInputs.forEach(input => {
            // Only validate if visible
            if (input.offsetParent !== null) {
                if (!this.validateDocument(input)) {
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    validateField(input) {
        const name = input.name;
        const cleanName = name.split('[')[0];
        const value = input.value.trim();
        let error = null;

        // Clear previous error
        this.clearFieldError(input);

        // Skip validation for hidden/invisible or disabled fields
        if ((input.offsetParent === null && input.type !== 'hidden') || input.disabled) {
            return true;
        }

        // Handle specifications sub-fields (dynamic category fields)
        if (cleanName === 'specifications') {
            // Regex to get text inside brackets: specifications[year] -> year
            const match = name.match(/\[(.*?)\]/);
            const subName = (match && match[1]) ? match[1] : null;

            if (subName && !value) {
                // Friendly names for the fields
                const displayNames = {
                    'year': 'Model Year',
                    'mileage': 'Mileage',
                    'fuel_type': 'Fuel Type',
                    'metal': 'Metal Type',
                    'artist': 'Artist Name',
                    'condition': 'Condition'
                };
                error = `${displayNames[subName] || subName.charAt(0).toUpperCase() + subName.slice(1)} is required.`;
            } else if (subName === 'year') {
                const year = parseInt(value);
                const currentYear = new Date().getFullYear();
                if (isNaN(year) || year < 1850 || year > currentYear + 1) {
                    error = 'Please enter a valid year (1850-' + (currentYear + 1) + ').';
                }
            } else if (subName === 'mileage') {
                if (isNaN(parseFloat(value)) || parseFloat(value) < 0) {
                    error = 'Please enter a valid mileage.';
                }
            }
        } else {
            // Validation rules for standard fields
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
                case 'min_increment':
                    error = this.validateMinIncrement(value);
                    break;
            }
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
        const mainCategorySelect = this.form.querySelector('#mainCategorySelect');
        const subCategorySelect = this.form.querySelector('#subCategorySelect');
        const subCategoryWrapper = this.form.querySelector('#subCategoryDropdownWrapper');

        if (!value) {
            // Check if a main category is selected
            if (mainCategorySelect && mainCategorySelect.value) {
                // If sub-category dropdown is visible, it means a sub-category is expected
                if (subCategoryWrapper && subCategoryWrapper.style.display !== 'none') {
                    return 'Please select a specific sub-category for your item.';
                }
            }
            return 'Please select a category for your item.';
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
        if (price < 100) {
            return 'Starting price must be at least ₹100.00.';
        }
        if (price > 999999999) {
            return 'Starting price is too high.';
        }
        return null;
    }

    validateMinIncrement(value) {
        if (!value) return null; // It's optional on client side
        const inc = parseFloat(value);
        if (isNaN(inc)) return 'Min increment must be a number.';
        if (inc < 0.01) return 'Min increment must be at least ₹0.01.';
        if (inc > 1000) return 'Min increment cannot exceed ₹1000.';
        return null;
    }

    validateStartTime(value) {
        if (!value) {
            return 'Auction start date and time is required.';
        }

        const startTimePicker = document.querySelector('#start_time_picker') && document.querySelector('#start_time_picker')._flatpickr;
        if (startTimePicker && startTimePicker.selectedDates[0]) {
            const start = startTimePicker.selectedDates[0];
            const now = new Date();

            if (start < now) {
                return 'Auction start time cannot be in the past.';
            }
        }

        return null;
    }

    validateEndTime(value, startTime) {
        if (!value) {
            return 'Auction end date and time is required.';
        }

        const parseDateStr = (dateStr) => {
            if (!dateStr) return null;
            // Matches YYYY-MM-DD hh:mm AM/PM or similar
            const parts = dateStr.match(/(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})\s+([AP]M)/i);
            if (!parts) return new Date(dateStr);

            let [_, year, month, day, hours, minutes, ampm] = parts;
            hours = parseInt(hours, 10);
            if (ampm.toUpperCase() === 'PM' && hours < 12) hours += 12;
            if (ampm.toUpperCase() === 'AM' && hours === 12) hours = 0;

            return new Date(year, month - 1, day, hours, minutes);
        };

        if (startTime && value) {
            const start = parseDateStr(startTime);
            const end = parseDateStr(value);

            if (start && end && end <= start) {
                return 'End time must be after the start time.';
            }
        }

        return null;
    }

    validateImage(input) {
        const existingImages = document.querySelectorAll('.existing-image-container');
        const totalImages = (input.files ? input.files.length : 0) + existingImages.length;

        if (totalImages === 0) {
            const error = 'At least one image is required.';
            this.showError(input, error);
            this.errors['images'] = error;
            return false;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        let error = null;

        if (totalImages > 5) {
            error = 'You cannot upload more than 5 images.';
        } else if (input.files && input.files.length > 0) {
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
        // Document is required for certain categories if visible
        if ((!input.files || input.files.length === 0) && input.offsetParent !== null) {
            const error = 'Document/Certificate is required for this category.';
            this.showError(input, error);
            this.errors['document'] = error;
            return false;
        }

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

        // If it's the hidden category input, highlight the visible select(s)
        if (input.id === 'selected_category_id' || input.name === 'category_id') {
            const mainCategorySelect = this.form.querySelector('#mainCategorySelect');
            const subCategorySelect = this.form.querySelector('#subCategorySelect');
            const subCategoryWrapper = this.form.querySelector('#subCategoryDropdownWrapper');

            if (mainCategorySelect) {
                if (!mainCategorySelect.value) {
                    mainCategorySelect.classList.add('is-invalid');
                    target = mainCategorySelect;
                } else if (subCategorySelect && subCategoryWrapper && subCategoryWrapper.style.display !== 'none' && !subCategorySelect.value) {
                    subCategorySelect.classList.add('is-invalid');
                    target = subCategorySelect;
                }
            }
        }

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
            input.parentElement.querySelector('.input-group-text').classList.add('border-danger', 'text-danger');
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
        if (input.parentElement.classList.contains('input-group')) {
            input.parentElement.querySelector('.input-group-text').classList.remove('border-danger', 'text-danger');
        }

        // If it's the hidden category input, clear visible select(s)
        if (input.id === 'selected_category_id') {
            const mainCategorySelect = this.form.querySelector('#mainCategorySelect');
            const subCategorySelect = this.form.querySelector('#subCategorySelect');
            if (mainCategorySelect) mainCategorySelect.classList.remove('is-invalid');
            if (subCategorySelect) subCategorySelect.classList.remove('is-invalid');
        }

        let target = input;
        if (input.id === 'imageInput') {
            const wrapper = input.closest('.image-upload-wrapper');
            if (wrapper) {
                target = wrapper;
                target.classList.remove('is-invalid'); // Ensure the wrapper loses the red border
                const errorDivs = target.parentElement.querySelectorAll('.invalid-feedback');
                errorDivs.forEach(div => {
                    div.style.display = 'none';
                    div.classList.remove('d-block');
                });
                return;
            }
        }

        const errorDiv = target.parentElement.querySelector('.invalid-feedback') ||
            (target.parentElement.parentElement ? target.parentElement.parentElement.querySelector('.invalid-feedback') : null);

        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.classList.remove('d-block');
            errorDiv.textContent = '';
        }
    }
}

