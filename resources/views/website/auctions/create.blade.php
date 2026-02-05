@extends('website.layouts.app')

@section('title', 'List New Item | LaraBids')

@section('content')

<!-- Hero Header -->
<section class="hero-section text-center text-white d-flex align-items-center mb-5">
    <div class="container" data-aos="fade-up">
        <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
            ✨ START SELLING TODAY
        </span>
        <h1 class="display-3 fw-bold mb-3">List Your <span class="text-white">Item</span></h1>
        <p class="lead opacity-75 mx-auto" style="max-width: 700px;">
            Turn your treasures into cash. Fill out the details below to reach thousands of potential bidders.
        </p>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card card-elite p-4 p-md-5 border-0 shadow-lg">
                    <form action="{{ route('auctions.store') }}" method="POST" enctype="multipart/form-data" id="auctionCreateForm" novalidate>
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Basic Information</h5>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small text-uppercase">Item Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0 shadow-none @error('title') is-invalid @enderror" 
                                    placeholder="What are you selling?" value="{{ old('title') }}">
                                @error('title')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small text-uppercase">Category</label>
                                <select name="category_id" class="form-select form-select-lg bg-light border-0 shadow-none @error('category_id') is-invalid @enderror">
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small text-uppercase">Starting Price ($)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-0 text-primary fw-bold border-end">$</span>
                                    <input type="number" name="starting_price" step="0.01" min="0.01" class="form-control bg-light border-0 shadow-none @error('starting_price') is-invalid @enderror" 
                                        placeholder="0.00" value="{{ old('starting_price') }}">
                                </div>
                                @error('starting_price')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small text-uppercase">Description</label>
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
                                <label class="form-label fw-bold text-dark small text-uppercase">Auction Start Date & Time</label>
                                <input type="datetime-local" name="start_time" class="form-control form-control-lg bg-light border-0 shadow-none @error('start_time') is-invalid @enderror" 
                                    value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}">
                                <small class="text-muted">When should the bidding begin?</small>
                                @error('start_time')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small text-uppercase">Auction End Date & Time</label>
                                <input type="datetime-local" name="end_time" class="form-control form-control-lg bg-light border-0 shadow-none @error('end_time') is-invalid @enderror" 
                                    value="{{ old('end_time') }}">
                                <small class="text-muted">When should the bidding conclude?</small>
                                @error('end_time')
                                    <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-dark small text-uppercase">Item Photos</label>
                                <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
                                    <div class="upload-content text-center">
                                        <i class="fas fa-images fa-3x text-primary mb-3"></i>
                                        <h6 class="fw-bold mb-1">Click to upload multiple images</h6>
                                        <p class="text-muted small mb-0">Drag and drop photos or click to browse. Max 5 photos.</p>
                                    </div>
                                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif" class="d-none" id="imageInput">
                                </div>
                                <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">
                                <small class="text-muted d-block mt-2">JPG, PNG or GIF (Max 2MB per image). Reorder by dragging. Click "Set Primary" to choose the main photo.</small>
                                
                                <div id="imagePreviewGrid" class="image-preview-grid">
                                    <!-- Previews will be injected here by JS -->
                                    <div class="col-12 text-center text-muted p-4">No images uploaded yet.</div>
                                </div>

                                @error('images')
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message }}</div>
                                @enderror
                                @foreach($errors->get('images.*') as $message)
                                    <div class="invalid-feedback d-block" data-server-error>{{ $message[0] }}</div>
                                @endforeach
                                <div class="invalid-feedback" id="image-client-error"></div>
                            </div>

                            <!-- Dynamic Category Fields -->
                            <div id="dynamicFieldsContainer" class="col-12 mt-4 d-none">
                                <h5 class="fw-bold text-dark border-start border-primary border-4 ps-3 mb-4">Category Specific Details</h5>
                                
                                <!-- Vintage Cars (3) -->
                                <div id="category_fields_3" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold">Year</label>
                                            <input type="number" name="specifications[year]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 1965">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold">Mileage (km)</label>
                                            <input type="number" name="specifications[mileage]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 50000">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold">Fuel Type</label>
                                            <select name="specifications[fuel_type]" class="form-select bg-light border-0 shadow-none">
                                                <option value="">Select Fuel</option>
                                                <option value="Petrol">Petrol</option>
                                                <option value="Diesel">Diesel</option>
                                                <option value="Electric">Electric</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="form-label small text-uppercase fw-bold">Vehicle Documentation (PDF/Image)</label>
                                            <input type="file" name="document" class="form-control bg-light border-0 shadow-none">
                                            <small class="text-muted">Registration, Title, or Inspection reports.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jewelry (4) -->
                                <div id="category_fields_4" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold">Metal Type</label>
                                            <input type="text" name="specifications[metal]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. 24K Gold">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold">Certificate of Authenticity</label>
                                            <input type="file" name="document" class="form-control bg-light border-0 shadow-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Art (5) -->
                                <div id="category_fields_5" class="category-fields-group d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold">Artist Name</label>
                                            <input type="text" name="specifications[artist]" class="form-control bg-light border-0 shadow-none" placeholder="e.g. Vincent van Gogh">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold">Authenticity Document</label>
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
@endpush

@push('scripts')
<script src="{{ asset('assets/js/image-upload-manager.js') }}"></script>
<script src="{{ asset('assets/js/auction-form-validation.js') }}"></script>
<script src="{{ asset('assets/js/auction-create.js') }}"></script>
@endpush

@endsection
