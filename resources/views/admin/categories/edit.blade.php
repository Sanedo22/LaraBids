@extends('admin.layouts.admin')

@section('title', 'Edit Category - LaraBids')

@section('content')php
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Category: {{ $category->name }}</h1>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary shadow-sm btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Category Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" id="categoryEditForm" novalidate>
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}">
                    <div class="invalid-feedback" id="name-error"></div>
                    @error('name')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="parent_id">Parent Category</label>
                    <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                        <option value="">-- No Parent (Top Level) --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select a parent category if this is a sub-category.</small>
                    @error('parent_id')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="icon">Icon Class (Font Awesome)</label>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="e.g. fas fa-laptop">
                    <div class="invalid-feedback" id="icon-error"></div>
                    @error('icon')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Active Status Removed -->

                <hr>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-sm text-white-50 mr-1"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('categoryEditForm');
    const nameInput = document.getElementById('name');
    const iconInput = document.getElementById('icon');

    // Validation functions
    function validateName() {
        const value = nameInput.value.trim();
        const errorDiv = document.getElementById('name-error');

        if (value === '') {
            nameInput.classList.add('is-invalid');

            errorDiv.textContent = 'Category name is required.';
            return false;
        } else if (value.length < 2) {
            nameInput.classList.add('is-invalid');

            errorDiv.textContent = 'Category name must be at least 2 characters.';
            return false;
        } else if (value.length > 255) {
            nameInput.classList.add('is-invalid');

            errorDiv.textContent = 'Category name must not exceed 255 characters.';
            return false;
        } else {
            nameInput.classList.remove('is-invalid');

            errorDiv.textContent = '';
            return true;
        }
    }

    function validateIcon() {
        const value = iconInput.value.trim();
        const errorDiv = document.getElementById('icon-error');

        if (value !== '' && value.length > 50) {
            iconInput.classList.add('is-invalid');

            errorDiv.textContent = 'Icon class must not exceed 50 characters.';
            return false;
        } else {
            iconInput.classList.remove('is-invalid');
            if (value !== '') {

            }
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

    iconInput.addEventListener('blur', validateIcon);
    iconInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateIcon();
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const isNameValid = validateName();
        const isIconValid = validateIcon();

        if (!isNameValid || !isIconValid) {
            e.preventDefault();

            // Focus on first invalid field
            if (!isNameValid) {
                nameInput.focus();
            } else if (!isIconValid) {
                iconInput.focus();
            }
        }
    });
});
</script>
@endpush
