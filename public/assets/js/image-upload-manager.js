/**
 * Universal Image Upload Manager with Preview and Reordering
 */

class ImageUploadManager {
    constructor(config) {
        this.input = document.querySelector(config.inputSelector);
        this.grid = document.querySelector(config.gridSelector);
        this.primaryInput = document.querySelector(config.primaryInputSelector);
        this.fileWrappers = []; // Array of { file: File, id: string, previewUrl: string }
        this.maxFiles = config.maxFiles || 5;
        this.existingCount = config.existingCount || 0;

        this.init();
    }

    setExistingCount(count) {
        this.existingCount = count;
    }

    init() {
        if (!this.input || !this.grid) return;

        this.input.addEventListener('change', (e) => this.handleFileSelect(e));

        // Setup dropzone file upload styling. 
        // We let the native file input (which covers the wrapper) handle the actual drop and 'change' event to prevent duplicate triggers.
        const wrapper = this.input.closest('.image-upload-wrapper');
        if (wrapper) {
            ['dragenter', 'dragover'].forEach(eventName => {
                wrapper.addEventListener(eventName, (e) => {
                    wrapper.classList.add('border-primary', 'bg-light');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                wrapper.addEventListener(eventName, (e) => {
                    wrapper.classList.remove('border-primary', 'bg-light');
                });
            });
            
            // If the drop target isn't the input itself for some reason, manually handle it
            wrapper.addEventListener('drop', (e) => {
                if (e.target !== this.input) {
                    e.preventDefault(); // only prevent if not on the input
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        this.input.files = e.dataTransfer.files;
                        this.input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }

        // Drag and Drop for reordering
        this.grid.addEventListener('dragover', (e) => e.preventDefault());
    }

    handleFileSelect(e) {
        const selectedFiles = Array.from(e.target.files);
        let skipped = 0;

        selectedFiles.forEach(file => {
            if (this.existingCount + this.fileWrappers.length < this.maxFiles) {
                // Validation (Frontend only)
                if (!file.type.startsWith('image/')) {
                    skipped++;
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    skipped++;
                    return;
                }

                this.fileWrappers.push({
                    file: file,
                    id: Math.random().toString(36).substr(2, 9),
                    previewUrl: URL.createObjectURL(file)
                });
            } else {
                skipped++;
            }
        });

        if (skipped > 0) {
            const wrapper = this.input.closest('.image-upload-wrapper');
            if (wrapper) {
                const existingError = wrapper.parentElement.querySelector('.skip-feedback');
                if (existingError) existingError.remove();

                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block skip-feedback mt-2 fw-bold text-center';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> Some files were skipped (exceeded 2MB limit, invalid format, or reached ${this.maxFiles} file limit).`;
                wrapper.parentElement.appendChild(errorDiv);
                
                wrapper.classList.remove('border-primary');
                wrapper.classList.add('border-danger');

                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                    wrapper.classList.remove('border-danger');
                }, 6000);
            }
        }

        this.render();
        this.updateInput();
    }

    setAsPrimary(index) {
        this.primaryInput.value = index;
        this.render();
    }

    removeImage(index) {
        const removed = this.fileWrappers.splice(index, 1)[0];
        if (removed && removed.previewUrl) {
            URL.revokeObjectURL(removed.previewUrl);
        }

        // Adjust primary index
        let currentPrimary = parseInt(this.primaryInput.value);
        if (currentPrimary === index) {
            this.primaryInput.value = 0;
        } else if (currentPrimary > index) {
            this.primaryInput.value = currentPrimary - 1;
        }

        this.render();
        this.updateInput();
    }

    render() {
        this.grid.innerHTML = '';
        const primaryIndex = parseInt(this.primaryInput.value) || 0;

        if (this.fileWrappers.length === 0) {
            this.grid.innerHTML = '';
            return;
        }

        this.fileWrappers.forEach((wrapper, index) => {
            const isPrimary = index === primaryIndex;
            const card = this.createPreviewCard(wrapper, index, isPrimary);
            this.grid.appendChild(card);
        });
    }

    createPreviewCard(wrapper, index, isPrimary) {
        const card = document.createElement('div');
        card.className = `image-preview-card ${isPrimary ? 'primary' : ''}`;
        card.draggable = true;
        card.dataset.index = index;

        card.innerHTML = `
            <img src="${wrapper.previewUrl}" alt="Preview">
            <span class="primary-badge">Primary</span>
            <button type="button" class="delete-btn" title="Remove image">
                <i class="fas fa-times"></i>
            </button>
            <div class="card-actions">
                ${!isPrimary ? `
                    <button type="button" class="btn btn-warning btn-sm set-primary-btn">
                        Set Primary
                    </button>
                ` : '<span class="text-white small fw-bold">Primary Image</span>'}
            </div>
        `;

        card.querySelector('.delete-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeImage(index);
        });

        const primaryBtn = card.querySelector('.set-primary-btn');
        if (primaryBtn) {
            primaryBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.setAsPrimary(index);
            });
        }

        // Drag and Drop events
        card.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', index);
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', () => card.classList.remove('dragging'));

        card.addEventListener('dragover', (e) => {
            e.preventDefault();
            card.classList.add('drag-over');
        });

        card.addEventListener('dragleave', () => card.classList.remove('drag-over'));

        card.addEventListener('drop', (e) => {
            e.preventDefault();
            card.classList.remove('drag-over');
            const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
            if (!isNaN(fromIndex)) {
                this.handleReorder(fromIndex, index);
            }
        });

        return card;
    }

    handleReorder(fromIndex, toIndex) {
        if (fromIndex === toIndex) return;

        const item = this.fileWrappers.splice(fromIndex, 1)[0];
        this.fileWrappers.splice(toIndex, 0, item);

        let currentPrimary = parseInt(this.primaryInput.value);
        if (currentPrimary === fromIndex) {
            this.primaryInput.value = toIndex;
        } else if (fromIndex < currentPrimary && toIndex >= currentPrimary) {
            this.primaryInput.value = currentPrimary - 1;
        } else if (fromIndex > currentPrimary && toIndex <= currentPrimary) {
            this.primaryInput.value = currentPrimary + 1;
        }

        this.render();
        this.updateInput();
    }

    updateInput() {
        const dataTransfer = new DataTransfer();
        this.fileWrappers.forEach(wrapper => {
            dataTransfer.items.add(wrapper.file);
        });
        this.input.files = dataTransfer.files;
        this.input.dispatchEvent(new Event('filesUpdated', { bubbles: true }));
    }
}
