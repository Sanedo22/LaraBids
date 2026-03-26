@extends('website.layouts.app')

@section('title', 'List New Item | LaraBids')

@section('content')

<section class="py-5 mt-3 mt-lg-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card card-elite p-4 p-md-5 border-0 shadow-lg">
                    <form action="{{ route('auctions.store') }}" method="POST" enctype="multipart/form-data" id="auctionCreateForm" novalidate>
                        @csrf
                        
                        <div class="text-center mb-5">
                            <h2 class="fw-bolder text-dark mb-2">Create Your Listing</h2>
                            <p class="text-muted">Fill out the details below to reach thousands of potential bidders.</p>
                        </div>
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Basic Information</h5>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Item Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0 shadow-none @error('title') is-invalid @enderror" 
                                    placeholder="What are you selling?" value="{{ old('title') }}">
                                @error('title')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-3">Item Category <span class="text-danger">*</span></label>
                                
                                <input type="hidden" name="category_id" id="selected_category_id" value="{{ old('category_id') }}">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select id="mainCategorySelect" class="form-select form-select-lg bg-light border-0 shadow-none">
                                            <option value="" disabled selected>Choose Main Category</option>
                                            @foreach($categories as $parent)
                                                <option value="{{ $parent->id }}" 
                                                    {{ old('category_id') && ($parent->id == old('category_id') || $parent->children->contains('id', old('category_id'))) ? 'selected' : '' }}>
                                                    {{ $parent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="subCategoryDropdownWrapper" style="display: none;">
                                            <select id="subCategorySelect" class="form-select form-select-lg bg-light border-0 shadow-none">
                                                <option value="" disabled selected>Choose Sub-Category</option>
                                                {{-- Populated via JS --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                @error('category_id')
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const mainSelect = document.getElementById('mainCategorySelect');
                                            const subSelect = document.getElementById('subCategorySelect');
                                            const subWrapper = document.getElementById('subCategoryDropdownWrapper');
                                            const errorMessage = "{{ $message }}";
                                            
                                            const errorDiv = document.createElement('div');
                                            errorDiv.className = 'invalid-feedback d-block';
                                            errorDiv.textContent = errorMessage;
                                            errorDiv.setAttribute('data-server-error', '');

                                            // Highlight main category if nothing selected
                                            if (mainSelect && !mainSelect.value) {
                                                mainSelect.classList.add('is-invalid');
                                                mainSelect.parentElement.appendChild(errorDiv);
                                            } 
                                            // Highlight sub category if main is selected but sub is not (and allowed)
                                            else if (subSelect && subWrapper && subWrapper.style.display !== 'none' && !subSelect.value) {
                                                subSelect.classList.add('is-invalid');
                                                subWrapper.appendChild(errorDiv);
                                            }
                                        });
                                    </script>
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
                                        placeholder="0.00" value="{{ old('starting_price') }}">
                                </div>
                                @error('starting_price')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Min Bid Increment (₹)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">₹</span>
                                    <input type="number" name="min_increment" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('min_increment') is-invalid @enderror" 
                                        placeholder="100" value="{{ old('min_increment') }}">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Minimum amount each next bid must increase by.</small>
                                @error('min_increment')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Reserve Price (₹) <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">₹</span>
                                    <input type="number" name="reserve_price" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('reserve_price') is-invalid @enderror" 
                                        placeholder="0.00" value="{{ old('reserve_price') }}">
                                </div>
                                <small class="text-muted"><i class="fas fa-eye-slash me-1"></i>Min price buyers must reach. (Hidden)</small>
                                @error('reserve_price')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Item Location</label>
                                <div class="input-group input-group-lg position-relative">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                    <input type="text" name="location" id="locationInput" class="form-control bg-light border-0 shadow-none @error('location') is-invalid @enderror" 
                                        placeholder="e.g. Mumbai, India" value="{{ old('location') }}" autocomplete="off">
                                    <div id="locationSuggestions" class="location-suggestions-container d-none"></div>
                                </div>
                                @error('location')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold text-dark small">Max Unpaid Strikes Allowed <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-shield-alt text-danger"></i></span>
                                    <input type="number" name="max_strikes_allowed" min="0" class="form-control bg-light border-0 shadow-none @error('max_strikes_allowed') is-invalid @enderror" 
                                        placeholder="Leave blank for no limit" value="{{ old('max_strikes_allowed') }}">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Block users who have this many unpaid items from bidding on your auction.</small>
                                @error('max_strikes_allowed')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Description</label>
                                <textarea name="description" rows="5" class="form-control bg-light border-0 shadow-none @error('description') is-invalid @enderror" 
                                    placeholder="Describe your item in detail...">{{ old('description') }}</textarea>
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
                                        placeholder="Select start date & time" value="{{ old('start_time', now()->format('Y-m-d h:i A')) }}">
                                </div>
                                <small class="text-muted d-block mt-2">When should the bidding begin?</small>
                                @error('start_time')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Auction End Date & Time</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0"><i class="far fa-calendar-check text-primary"></i></span>
                                    <input type="text" name="end_time" id="end_time_picker" class="form-control bg-light border-0 shadow-none @error('end_time') is-invalid @enderror" 
                                        placeholder="Select end date & time" value="{{ old('end_time') }}">
                                </div>
                                <small class="text-muted d-block mt-2">When should the bidding conclude?</small>
                                @error('end_time')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-dark small mb-3">Item Photos</label>
                                
                                <div class="image-upload-wrapper d-flex flex-column justify-content-center align-items-center py-4 px-3 position-relative" id="dragDropArea" onclick="document.getElementById('imageInput').click()">
                                    <div class="upload-content text-center" style="pointer-events: none;">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%;">
                                            <i class="fas fa-image fa-lg"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Drag & Drop your images here</h6>
                                        <p class="text-muted small mb-0">or click to browse files</p>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">Max 5 photos</span>
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">Up to 2MB each</span>
                                        </div>
                                    </div>
                                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif" class="d-none" id="imageInput">
                                </div>
                                <div class="invalid-feedback fw-bold mt-2" style="display: none;"></div>

                                <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">
                                <small class="text-muted d-block mt-2 px-1"><i class="fas fa-info-circle me-1"></i> Tip: Reorder images by dragging them below. Click "Set Primary" to choose the main photo.</small>
                                
                                <div id="imagePreviewGrid" class="image-preview-grid mt-3">
                                    <!-- Previews will be injected here by JS -->
                                    <div class="col-12 text-center text-muted p-4 border rounded-3 bg-light w-100 placeholder-text" style="grid-column: 1 / -1;">No images uploaded yet.</div>
                                </div>

                                @error('images')
                                    <div class="invalid-feedback d-block mt-2 fw-bold" data-server-error>{{ $message }}</div>
                                @enderror
                                @foreach($errors->get('images.*') as $message)
                                    <div class="invalid-feedback d-block mt-1 fw-bold" data-server-error>{{ $message[0] }}</div>
                                @endforeach
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
                                    🚀 Launch Your Auction
                                </button>
                                <p class="text-center text-muted small mt-3">By listing an item, you agree to LaraBids' terms of service.</p>
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
                                <i class="fas fa-lightbulb text-primary fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">Selling Tips</h5>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex gap-3 mb-4">
                                <div class="text-primary fw-bold mt-1">01.</div>
                                <div class="small">Use high-quality photos from multiple angles to build trust.</div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <div class="text-primary fw-bold mt-1">02.</div>
                                <div class="small">Be honest and detailed in your description to avoid disputes.</div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <div class="text-primary fw-bold mt-1">03.</div>
                                <div class="small">Start with a competitive price to attract more initial bidders.</div>
                            </li>
                            <li class="d-flex gap-3">
                                <div class="text-primary fw-bold mt-1">04.</div>
                                <div class="small">Schedule your auction to end when your target audience is online.</div>
                            </li>
                        </ul>
                    </div>

                    <div class="card bg-primary text-white p-4 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="position-relative" style="z-index: 2;">
                            <h5 class="fw-bold mb-3">Need Help?</h5>
                            <p class="small opacity-75 mb-4">Our support team is available 24/7 to help you with your listings.</p>
                            <a href="{{ route('contact') }}" class="btn btn-white btn-sm px-4 rounded-pill fw-bold text-primary">Contact Us</a>
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
<link rel="stylesheet" href="{{ asset('assets/css/location-autocomplete.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/image-upload-manager.js') }}"></script>
<script src="{{ asset('assets/js/auction-form-validation.js') }}"></script>
<script src="{{ asset('assets/js/auction-create.js') }}"></script>
<script src="{{ asset('assets/js/category-selection.js') }}"></script>
<script src="{{ asset('assets/js/location-autocomplete.js') }}"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startPicker = flatpickr("#start_time_picker", {
            enableTime: true,
            dateFormat: "Y-m-d h:i K",
            minDate: "today",
            time_24hr: false,
            onChange: function(selectedDates, dateStr, instance) {
                instance.element.dispatchEvent(new Event('input', { bubbles: true }));
                // When start time changes, re-validate end time
                const endPickerElement = document.getElementById('end_time_picker');
                if (endPickerElement && endPickerElement.value) {
                    endPickerElement.dispatchEvent(new Event('input', { bubbles: true }));
                }
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



