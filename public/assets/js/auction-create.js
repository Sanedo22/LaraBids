/**
 * Auction Creation Page Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Form Validator
    const formId = 'auctionCreateForm';
    const form = document.getElementById(formId);

    if (form) {
        console.log('Initializing auction form validator...');
        // Note: AuctionFormValidator must be loaded before this script
        if (typeof AuctionFormValidator !== 'undefined') {
            const validator = new AuctionFormValidator(formId);

            // Backup submit handler to ensure validation runs
            form.onsubmit = function (e) {
                const isValid = validator.validateForm();
                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();

                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                    return false;
                }
                return true;
            };
        }
    }

    // 2. Initialize Image Upload Manager
    if (typeof ImageUploadManager !== 'undefined') {
        new ImageUploadManager({
            inputSelector: '#imageInput',
            gridSelector: '#imagePreviewGrid',
            primaryInputSelector: '#primaryImageIndex',
            maxFiles: 10
        });
    }

    // 3. Dynamic Category Fields Logic
    const categorySelect = document.querySelector('select[name="category_id"]');
    const dynamicFieldsContainer = document.getElementById('dynamicFieldsContainer');
    const categoryGroups = document.querySelectorAll('.category-fields-group');

    if (categorySelect && dynamicFieldsContainer) {
        categorySelect.onchange = function () {
            const categoryId = this.value;

            // Hide all groups first
            categoryGroups.forEach(group => group.classList.add('d-none'));
            dynamicFieldsContainer.classList.add('d-none');

            // Show relevant group
            const targetGroup = document.getElementById(`category_fields_${categoryId}`);
            if (targetGroup) {
                dynamicFieldsContainer.classList.remove('d-none');
                targetGroup.classList.remove('d-none');

                // Smooth fade-in
                targetGroup.style.opacity = 0;
                let opacity = 0;
                const timer = setInterval(function () {
                    if (opacity >= 1) clearInterval(timer);
                    targetGroup.style.opacity = opacity;
                    opacity += 0.1;
                }, 20);
            }
        };

        // Trigger change on load if category already selected (e.g. after validation error)
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
});
