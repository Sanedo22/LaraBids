@extends('website.layouts.app')

@section('title', 'Refine Listing | LaraBids')

@section('content')

@php
    $hasBids = $auction->bids()->exists();
@endphp

<section class="py-5 mt-3 mt-lg-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card card-elite p-4 p-md-5 border-0 shadow-lg">
                    <form action="{{ route('auctions.update', $auction->id) }}" method="POST" enctype="multipart/form-data" id="auctionEditForm" novalidate>
                        @csrf
                        @method('PUT')
                        
                        <div class="text-center mb-5">
                            <h2 class="fw-bolder text-dark mb-2">Update Your Listing</h2>
                            <p class="text-muted">Modify your item details. Some fields are locked once bidding has started.</p>
                            
                            @if($auction->status === 'active')
                                <div class="alert alert-warning border-0 shadow-sm rounded-4 small p-3 d-inline-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Since this auction is already approved, editing it will move it back to <strong>Pending</strong> for admin re-approval.
                                </div>
                            @endif
                        </div>
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Basic Information</h5>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Item Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0 shadow-none @error('title') is-invalid @enderror" 
                                    placeholder="What are you selling?" value="{{ old('title', $auction->title) }}">
                                @error('title')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-3">Item Category <span class="text-danger">*</span></label>
                                
                                <input type="hidden" name="category_id" id="selected_category_id" value="{{ old('category_id', $auction->category_id) }}">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select id="mainCategorySelect" class="form-select form-select-lg bg-light border-0 shadow-none" {{ $hasBids ? 'disabled' : '' }}>
                                            <option value="" disabled>Choose Main Category</option>
                                            @foreach($categories as $parent)
                                                <option value="{{ $parent->id }}" 
                                                    {{ old('category_id', $auction->category_id) == $parent->id || ($parent->children->contains('id', old('category_id', $auction->category_id))) ? 'selected' : '' }}>
                                                    {{ $parent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="subCategoryDropdownWrapper" style="display: none;">
                                            <select id="subCategorySelect" class="form-select form-select-lg bg-light border-0 shadow-none" {{ $hasBids ? 'disabled' : '' }}>
                                                <option value="" disabled selected>Choose Sub-Category</option>
                                                {{-- Populated via JS --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($hasBids)
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-lock me-1"></i> Category cannot be changed after bids are placed.</small>
                                @endif

                                @error('category_id')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Pass child data to JS -->
                            <script>
                                window.categoryTree = @json($categoryTree);
                            </script>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Starting Price (₹)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">₹</span>
                                    <input type="number" name="starting_price" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('starting_price') is-invalid @enderror" 
                                        placeholder="0.00" value="{{ old('starting_price', $auction->starting_price) }}" {{ $hasBids ? 'disabled' : '' }}>
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>The initial amount at which bidding will open.</small>
                                @if($hasBids)
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i> Locked due to active bids.</small>
                                @endif
                                @error('starting_price')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Min Bid Increment (₹)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">₹</span>
                                    <input type="number" name="min_increment" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('min_increment') is-invalid @enderror" 
                                        placeholder="100" value="{{ old('min_increment', $auction->min_increment) }}">
                                </div>
                                <small class="text-muted">Minimum amount each next bid must increase by.</small>
                                @error('min_increment')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Reserve Price (₹) <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">₹</span>
                                    <input type="number" name="reserve_price" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('reserve_price') is-invalid @enderror" 
                                        placeholder="0.00" value="{{ old('reserve_price', $auction->reserve_price) }}" {{ $hasBids ? 'disabled' : '' }}>
                                </div>
                                <small class="text-muted"><i class="fas fa-eye-slash me-1"></i>The minimum acceptable price. If the final bid is lower, the item remains unsold. (Hidden from buyers) {{ $hasBids ? 'Locked due to active bids.' : '' }}</small>
                                @error('reserve_price')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Item Location</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                    <input type="text" name="location" class="form-control bg-light border-0 shadow-none @error('location') is-invalid @enderror" 
                                        placeholder="e.g. Mumbai, India or Local Pickup Only" value="{{ old('location', $auction->location) }}">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Where the item is physically located, or 'Online / Ships Worldwide'.</small>
                                @error('location')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold text-dark small">Max Unpaid Strikes Allowed <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-shield-alt text-danger"></i></span>
                                    <input type="number" name="max_strikes_allowed" min="0" class="form-control bg-light border-0 shadow-none @error('max_strikes_allowed') is-invalid @enderror" 
                                        placeholder="Leave blank for no limit" value="{{ old('max_strikes_allowed', $auction->requirement?->max_strikes_allowed) }}">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Block users who have this many unpaid items from bidding on your auction.</small>
                                @error('max_strikes_allowed')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Description</label>
                                <textarea name="description" rows="5" class="form-control bg-light border-0 shadow-none @error('description') is-invalid @enderror" 
                                    placeholder="Describe your item in detail...">{{ old('description', $auction->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Timing & Media -->
                            <div class="col-12 mt-5">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Timing & Media</h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Auction Start Date & Time</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="far fa-calendar-alt text-primary"></i></span>
                                    <input type="text" name="start_time" id="start_time_picker" class="form-control bg-light border-0 shadow-none @error('start_time') is-invalid @enderror" 
                                        placeholder="Select start date & time" value="{{ old('start_time', $auction->start_time->format('Y-m-d h:i A')) }}" {{ $hasBids ? 'disabled' : '' }}>
                                </div>
                                @if($hasBids)
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i> Start time is locked.</small>
                                @endif
                                @error('start_time')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Auction End Date & Time</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="far fa-calendar-check text-primary"></i></span>
                                    <input type="text" name="end_time" id="end_time_picker" class="form-control bg-light border-0 shadow-none @error('end_time') is-invalid @enderror" 
                                        placeholder="Select end date & time" value="{{ old('end_time', $auction->end_time->format('Y-m-d h:i A')) }}">
                                </div>
                                <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>The exact moment your auction will automatically close to new bids.</small>
                                @error('end_time')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-dark small mb-3">Item Photos</label>
                                
                                <!-- Existing Images Row -->
                                <div class="row g-3 mb-4 mt-1">
                                    @foreach($auction->images as $img)
                                        <div class="col-md-3 existing-image-container" id="existing-img-{{ $img->id }}">
                                            <div class="position-relative rounded-4 overflow-hidden border shadow-sm h-100 bg-white">
                                                <img src="{{ str_starts_with($img->image_path, 'http') ? $img->image_path : asset('storage/' . $img->image_path) }}" 
                                                    class="w-100 h-100 object-fit-cover" style="aspect-ratio: 1/1;">
                                                
                                                @if($img->is_primary)
                                                    <div class="position-absolute top-0 start-0 m-2">
                                                        <span class="badge bg-primary rounded-pill small px-2">Main</span>
                                                    </div>
                                                @endif
                                                
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle shadow ripple-on-click" 
                                                    style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; padding: 0;"
                                                    onclick="removeExistingImage({{ $img->id }})">
                                                    <i class="fas fa-times fa-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="deletedImagesContainer"></div>

                                <div class="image-upload-wrapper d-flex flex-column justify-content-center align-items-center py-4 px-3 position-relative" id="dragDropArea" onclick="document.getElementById('imageInput').click()">
                                    <div class="upload-content text-center" style="pointer-events: none;">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%;">
                                            <i class="fas fa-plus fa-lg"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Add More Photos</h6>
                                        <p class="text-muted small mb-0">Drag & Drop or click to browse</p>
                                    </div>
                                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif" class="d-none" id="imageInput">
                                </div>
                                
                                <div id="imageLimitInfo" class="small text-muted mt-2 fw-semibold px-1">
                                    You have {{ $auction->images->count() }} images. Max limit is 5.
                                </div>

                                <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">
                                
                                <div id="imagePreviewGrid" class="image-preview-grid mt-3">
                                    <!-- New previews injected here -->
                                </div>

                                @error('images')
                                    <div class="invalid-feedback d-block mt-2 fw-bold" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dynamic Category Fields -->
                            <div id="dynamicFieldsContainer" class="col-12 mt-4 d-none">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Category Specific Details</h5>
                                
                                <!-- Vintage Cars (vintage-cars) -->
                                <div id="category_fields_vintage-cars" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Year</label>
                                            <input type="number" name="specifications[year]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 1965">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Mileage (km)</label>
                                            <input type="number" name="specifications[mileage]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 50000">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Fuel Type</label>
                                            <select name="specifications[fuel_type]" class="form-select bg-light border-0 shadow-none">
                                                <option value="">Select Fuel</option>
                                                <option value="Petrol">Petrol</option>
                                                <option value="Diesel">Diesel</option>
                                                <option value="Electric">Electric</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="form-label small fw-bold">Vehicle Documentation (PDF/Image)</label>
                                            <input type="file" name="document" class="form-control bg-light border-0 shadow-none">
                                            <small class="text-muted">Registration, Title, or Inspection reports.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jewelry (jewelry) -->
                                <div id="category_fields_jewelry" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Metal Type</label>
                                            <input type="text" name="specifications[metal]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 24K Gold">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Certificate of Authenticity</label>
                                            <input type="file" name="document" class="form-control bg-light border-0 shadow-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Art (art) -->
                                <div id="category_fields_art" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Artist Name</label>
                                            <input type="text" name="specifications[artist]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. Vincent van Gogh">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Authenticity Document</label>
                                            <input type="file" name="document" class="form-control bg-light border-0 shadow-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow fw-bold">
                                    💾 Save Changes
                                </button>
                                <a href="{{ route('user.my-auctions') }}" class="btn btn-link w-100 text-muted mt-3 text-decoration-none small">
                                    <i class="fas fa-chevron-left me-1"></i> Back to My Auctions
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Side Info -->
            <div class="col-lg-4 mt-5 mt-lg-0" data-aos="fade-left">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card card-elite border-0 shadow-sm p-4 mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                                <i class="fas fa-shield-alt text-primary fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">Editing Rules</h5>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex gap-3 mb-4">
                                <div class="text-primary fw-bold mt-1 small">01.</div>
                                <div class="small">Description and increment can be updated anytime to attract more bidders.</div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <div class="text-{{ $hasBids ? 'warning' : 'primary' }} fw-bold mt-1 small">02.</div>
                                <div class="small {{ $hasBids ? 'text-muted' : '' }}">
                                    Starting price is locked once the first bid is placed to maintain marketplace integrity.
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <div class="text-primary fw-bold mt-1 small">03.</div>
                                <div class="small">You can add up to 5 photos. High quality images lead to faster sales.</div>
                            </li>
                        </ul>
                    </div>

                    <div class="card bg-primary text-white p-4 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="position-relative" style="z-index: 2;">
                            <h5 class="fw-bold mb-3">Questions?</h5>
                            <p class="small opacity-75 mb-4">Need help adjusting your auction? Our support team is here to assist.</p>
                            <a href="{{ route('contact') }}" class="btn btn-white btn-sm px-4 rounded-pill fw-bold text-primary">Get Help</a>
                        </div>
                        <i class="fas fa-headset position-absolute opacity-10" style="font-size: 8rem; right: -1rem; bottom: -1rem; z-index: 1;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/image-upload.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/category-selection.css') }}">
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/image-upload-manager.js') }}"></script>
<script src="{{ asset('assets/js/auction-form-validation.js') }}"></script>
<script src="{{ asset('assets/js/category-selection.js') }}"></script>
<script src="{{ asset('assets/js/auction-edit.js') }}"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dynamic category selection logic
        // (Handled by category-selection.js - ensure categories are passed via categoryTree)
        
        // Initialize edit-specific logic
        if (typeof initAuctionEdit === 'function') {
            initAuctionEdit({
                existingImageCount: {{ $auction->images->count() }}
            });
        }

        // Initialize Time Pickers
        const startPicker = flatpickr("#start_time_picker", {
            enableTime: true,
            dateFormat: "Y-m-d h:i K",
            minDate: @json($hasBids ? null : 'today'), // If has bids, don't restrict min date for start time (though it's disabled anyway)
            time_24hr: false,
            onChange: function(selectedDates, dateStr, instance) {
                instance.element.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        
        const endPicker = flatpickr("#end_time_picker", {
            enableTime: true,
            dateFormat: "Y-m-d h:i K",
            minDate: "today",
            time_24hr: false,
            onChange: function(selectedDates, dateStr, instance) {
                instance.element.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
</script>
@endpush

@endsection



