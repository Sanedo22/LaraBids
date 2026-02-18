@extends('website.layouts.app')

@section('title', 'Auction Details | LaraBids')

@section('content')

@php
    $galleryImages = collect();
    $mainPath = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : null;
    
    if ($mainPath) $galleryImages->push($mainPath);
    
    foreach ($auction->images as $img) {
        $path = asset('storage/' . $img->image_path);
        if (!$galleryImages->contains($path)) {
            $galleryImages->push($path);
        }
    }
@endphp

<!-- Auction Details -->
<section class="py-5 mt-5">
    <div class="container py-lg-5">
        <div class="row g-4">
            <!-- Left: Image Gallery (Col-lg-7) -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="position-relative overflow-hidden rounded-4 mb-3 auction-main-img-container shadow-sm border" style="height: 550px; cursor: zoom-in;">
                    @if($mainPath)
                        <img src="{{ $mainPath }}" class="w-100 h-100 object-fit-cover main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @else
                        <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                            class="w-100 h-100 object-fit-cover main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @endif
                    <div class="zoom-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0 transition-all" onclick="openLightbox()">
                        <i class="fas fa-search-plus fa-3x text-white"></i>
                    </div>
                </div>
                
                <!-- Dedicated Gallery Box -->
                <div class="card p-3 border-0 shadow-sm rounded-4 bg-white mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="h6 mb-0 fw-bold text-dark text-uppercase letter-spacing-1">
                            <i class="fas fa-images me-2 text-primary"></i>Product Gallery
                        </h5>
                        <span class="badge bg-primary-subtle text-primary rounded-pill small px-3">
                            {{ $galleryImages->count() }} Photos
                        </span>
                    </div>

                    @if($galleryImages->count() > 1)
                        <div class="row g-2 overflow-auto flex-nowrap pb-2 custom-scrollbar">
                            @foreach($galleryImages as $index => $imgUrl)
                            <div class="col-3 col-md-2 flex-shrink-0">
                                <div class="rounded-3 overflow-hidden border {{ $index === 0 ? 'border-primary border-2' : 'border-light' }} shadow-sm auction-thumb h-100" 
                                    style="height: 80px !important; min-height: 80px; cursor: pointer;"
                                    onclick="changeMainImage('{{ $imgUrl }}', this)">
                                    <img src="{{ $imgUrl }}" class="w-100 h-100 object-fit-cover" alt="Gallery View">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center bg-light rounded-3 animate__animated animate__fadeIn">
                            <i class="fas fa-camera-retro fa-2x text-secondary opacity-25 mb-2"></i>
                            <p class="small text-muted mb-0">No additional photos available for this item.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Action Sidebar (Col-lg-5) -->
            <div class="col-lg-5">
                <div class="sticky-lg-top" style="top: 120px; z-index: 1010;">
                    <div class="card p-4 mb-4 border-0 shadow-sm" data-aos="fade-left">
                        <h2 class="h3 text-dark mb-2 fw-bold">{{ $auction->title }}</h2>
                        <div class="mb-4">
                            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">{{ $auction->category->name ?? 'Uncategorized' }}</span>
                        </div>

                        <!-- Timer Section -->
                        @php
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($auction->end_time);
                            $diff = $now->diff($end);
                            $isClosed = $now->greaterThan($end);
                        @endphp

                        @if(!$isClosed)
                        <div class="glass-timer text-center py-3 timer-val mb-4 rounded-4" 
                            data-days="{{ $diff->d }}" data-hours="{{ $diff->h }}" data-min="{{ $diff->i }}" data-sec="{{ $diff->s }}">
                            <div class="row g-0">
                                <div class="col text-center border-end">
                                    <div class="fw-bold fs-4" data-days>{{ sprintf('%02d', $diff->d) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.65rem;">Days</small>
                                </div>
                                <div class="col text-center border-end">
                                    <div class="fw-bold fs-4" data-hours>{{ sprintf('%02d', $diff->h) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.65rem;">Hrs</small>
                                </div>
                                <div class="col text-center border-end">
                                    <div class="fw-bold fs-4" data-min>{{ sprintf('%02d', $diff->i) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.65rem;">Min</small>
                                </div>
                                <div class="col text-center">
                                    <div class="fw-bold fs-4" data-sec>{{ sprintf('%02d', $diff->s) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.65rem;">Sec</small>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-danger py-3 mb-4 text-center fw-bold rounded-4">Auction Closed</div>
                        @endif

                        <!-- Bidding Section -->
                        <div class="mb-4 p-4 bg-light rounded-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small fw-bold">Current Bid</span>
                                <span class="h2 mb-0 text-primary fw-bold">₹{{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Starting Price</span>
                                <span class="text-dark small fw-bold">₹{{ number_format($auction->starting_price, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="text-secondary small">Min Increment</span>
                                <span class="text-dark small fw-bold">₹{{ number_format($auction->min_increment, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                                <span class="text-secondary small fw-bold">Max Bid Increment</span>
                                <span class="text-danger small fw-bold">₹{{ number_format(\App\Models\Auction::MAX_INCREMENT_ALLOWED, 2) }}</span>
                            </div>
                        </div>

                        @if(!$isClosed)
                        @auth
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show mb-4 rounded-4" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-4" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('auctions.bid', $auction->id) }}" method="POST" class="mb-4" id="place-bid-form">
                                @csrf
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold text-dark small text-uppercase mb-0">Your Bid Increment (₹)</label>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                                            Min: ₹{{ number_format($auction->min_increment ?? 0.01, 2) }}
                                        </span>
                                    </div>
                                    <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden mb-3">
                                        <span class="input-group-text bg-white border-end-0 text-primary fw-bold">₹</span>
                                        <input type="number" name="increment" class="form-control bg-white border-start-0 ps-0 fw-bold border-0 shadow-none" id="bid-increment" 
                                            placeholder="{{ number_format($auction->min_increment ?? 0.01, 2, '.', '') }}" 
                                            value="{{ old('increment') }}"
                                            min="{{ $auction->min_increment ?? 0.01 }}" step="0.01" max="{{ \App\Models\Auction::MAX_INCREMENT_ALLOWED }}" required>
                                    </div>

                                    <!-- Increment Shortcuts -->
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach([100, 300, 500, 700, 1000] as $amount)
                                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill py-2 rounded-3 fw-bold bid-shortcut" data-amount="{{ $amount }}">
                                                +₹{{ $amount }}
                                            </button>
                                        @endforeach
                                    </div>
                                    
                                    <div id="bid-feedback-area" class="mt-2">
                                        @error('increment')
                                            <div class="alert alert-danger py-2 px-3 rounded-3 small border-0 shadow-sm animate__animated animate__shakeX">
                                                <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="bid-feedback-total"></div>
                                    
                                    <small class="text-muted mt-2 d-block text-center opacity-75">
                                        Max jump: ₹{{ number_format(\App\Models\Auction::MAX_INCREMENT_ALLOWED, 2) }}
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm hover-up">
                                    Place Bid Now <i class="fas fa-gavel ms-2"></i>
                                </button>
                            </form>
                        @else
                            <div class="alert alert-soft-primary border-0 mb-4 p-3 rounded-4 bg-light">
                                <p class="small mb-0 text-center">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    Please <a href="{{ route('login') }}" class="text-primary fw-bold">login</a> to participate.
                                </p>
                            </div>
                        @endauth
                        @endif

                        <div class="d-flex gap-3 mt-2">
                            @auth
                            <form action="{{ route('user.watchlist.toggle', $auction->id) }}" method="POST" class="flex-fill watchlist-toggle-form">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100 rounded-pill py-2">
                                    <i class="{{ $auction->watchlists->isNotEmpty() ? 'fas' : 'far' }} fa-heart me-2"></i>Watchlist
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary flex-fill rounded-pill py-2">
                                <i class="far fa-heart me-2"></i>Watchlist
                            </a>
                            @endauth
                            <button class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Compact Seller Info -->
                    <div class="card p-4 border-0 shadow-sm" data-aos="fade-left" data-aos-delay="100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <a href="{{ route('sellers.show', $auction->user->id) }}">
                                <img src="{{ $auction->user->avatar_url }}"
                                    class="rounded-circle border border-light shadow-sm" height="50" width="50" style="object-fit: cover;" alt="Seller">
                            </a>
                            <div class="flex-grow-1">
                                <a href="{{ route('sellers.show', $auction->user->id) }}" class="text-decoration-none">
                                    <h6 class="text-dark fw-bold mb-0 transition-all hover-primary">{{ $auction->user->name }}</h6>
                                </a>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-success fw-bold" style="font-size: 0.7rem;"><i class="fas fa-check-circle me-1"></i>VERIFIED</small>
                                    <div class="text-warning small" style="font-size: 0.7rem;">
                                        <i class="fas fa-star"></i> 4.9
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">Contact</a>
                        </div>
                        @if($auction->user->location || $auction->user->bio)
                            <div class="border-top pt-3">
                                @if($auction->user->location)
                                    <div class="small text-secondary mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $auction->user->location }}
                                    </div>
                                @endif
                                @if($auction->user->bio)
                                    <p class="small text-muted mb-0 text-truncate-2">
                                        {{ Str::limit($auction->user->bio, 80) }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Row -->
        <div class="row g-4 mt-2">
            <!-- Left: Description & Specs (Col-lg-8) -->
            <div class="col-lg-8" data-aos="fade-up">
                <!-- Description Card -->
                <div class="card p-4 border-0 shadow-sm mb-4">
                    <h4 class="h5 text-dark mb-4 fw-bold border-bottom pb-3">Product Description</h4>
                    <div class="text-secondary lh-lg">
                        {!! nl2br(e($auction->description)) !!}
                    </div>
                </div>

                <!-- Specifications Card -->
                @if($auction->specifications && count(array_filter($auction->specifications)) > 0)
                <div class="card p-4 border-0 shadow-sm mb-4">
                    <h4 class="h5 text-dark mb-4 fw-bold border-bottom pb-3">Technical Specifications</h4>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                @foreach($auction->specifications as $key => $value)
                                    @if($value)
                                    <tr>
                                        <td class="text-secondary small text-uppercase fw-bold py-3" style="width: 35%;">{{ str_replace('_', ' ', $key) }}</td>
                                        <td class="text-dark fw-bold py-3">{{ $value }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: History & Files (Col-lg-4) -->
            <div class="col-lg-4" data-aos="fade-up">
                <!-- Document Card -->
                @if($auction->document)
                <div class="card p-4 border-0 shadow-sm mb-4">
                    <h4 class="h5 text-dark mb-4 fw-bold">Verification</h4>
                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                        <i class="far fa-file-pdf fa-2x text-danger me-3"></i>
                        <div class="flex-grow-1">
                            <span class="d-block text-dark fw-bold small">Audit Report.pdf</span>
                            <small class="text-secondary">Verified Document</small>
                        </div>
                        <a href="{{ asset('storage/' . $auction->document) }}" target="_blank" class="text-primary">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Bid History Card -->
                <div class="card p-4 border-0 shadow-sm">
                    <h4 class="h5 text-dark mb-4 fw-bold border-bottom pb-3">Bid History</h4>
                    <div class="bid-history-scroll pe-2">
                        <ul class="list-unstyled mb-0">
                            @forelse($auction->bids->sortByDesc('created_at') as $bid)
                        <li class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light last-child-border-0">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $bid->user->avatar_url }}" 
                                    class="rounded-circle border" height="35" width="35" style="object-fit: cover;" alt="{{ $bid->user->name }}">
                                <div>
                                    <span class="d-block text-dark fw-bold small">{{ $bid->user->name }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $bid->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <span class="text-primary fw-bold">₹{{ number_format($bid->amount, 2) }}</span>
                        </li>
                        @empty
                        <li class="text-center py-4 text-secondary">
                            <i class="fas fa-history d-block mb-3 opacity-25 fs-2"></i>
                            <span class="small">No bids placed in the last 24 hours.</span>
                        </li>
                        @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>



    </div>
</section>

<!-- Lightbox Modal -->
<div id="imageLightbox" class="lightbox-modal">
    <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
    
    <div class="lightbox-controls">
        <span class="lightbox-counter" id="lightboxCounter">1 / 1</span>
        <button class="close-lightbox" onclick="closeLightbox()" title="Close (Esc)">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <button class="lightbox-nav-btn prev" onclick="changeLightboxImage(-1)" title="Previous (Left Arrow)">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <div class="lightbox-content-wrapper">
        <img class="lightbox-image" id="lightboxImage">
        <div id="lightboxCaption" class="lightbox-caption"></div>
    </div>
    
    <button class="lightbox-nav-btn next" onclick="changeLightboxImage(1)" title="Next (Right Arrow)">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auction-gallery.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/auction-gallery.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = [
            @foreach($galleryImages as $imgUrl)
                "{{ $imgUrl }}",
            @endforeach
        ];
        initGallery(images, "{{ str_replace('"', '\"', $auction->title) }}");

        // Bid Increment Real-time Feedback
        const bidInput = document.getElementById('bid-increment');
        const totalFeedback = document.querySelector('.bid-feedback-total');
        const errorFeedback = document.getElementById('bid-feedback-area');
        const placeBidForm = document.getElementById('place-bid-form');
        const currentPrice = {{ $auction->current_price }};
        const minIncrement = {{ $auction->min_increment ?? 0.01 }};
        const maxIncrement = {{ \App\Models\Auction::MAX_INCREMENT_ALLOWED }};

        if (bidInput) {
            // Function to update bid feedback
            function updateBidFeedback() {
                const val = parseFloat(bidInput.value);
                const inputGroup = bidInput.closest('.input-group');
                
                if (!isNaN(val) && val > 0) {
                    const newTotal = (currentPrice + val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    // Client-side validation checks
                    if (val < minIncrement) {
                        inputGroup.classList.add('border', 'border-danger');
                        errorFeedback.innerHTML = `<div class="alert alert-danger py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm">
                            <i class="fas fa-exclamation-circle me-2"></i>Min increment is ₹${minIncrement.toFixed(2)}
                        </div>`;
                        totalFeedback.innerHTML = '';
                    } else if (val > maxIncrement) {
                        inputGroup.classList.add('border', 'border-danger');
                        errorFeedback.innerHTML = `<div class="alert alert-danger py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm">
                            <i class="fas fa-exclamation-circle me-2"></i>Max jump is ₹${maxIncrement.toFixed(2)}
                        </div>`;
                        totalFeedback.innerHTML = '';
                    } else {
                        inputGroup.classList.remove('border', 'border-danger');
                        inputGroup.classList.add('border', 'border-success');
                        errorFeedback.innerHTML = '';
                        totalFeedback.innerHTML = `<div class="alert alert-info py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm animate__animated animate__fadeIn">
                            <i class="fas fa-calculator me-2 text-primary"></i><strong>New Total Bid:</strong> ₹${newTotal}
                        </div>`;
                    }
                } else {
                    totalFeedback.innerHTML = '';
                    errorFeedback.innerHTML = '';
                    if (inputGroup) {
                        inputGroup.classList.remove('border', 'border-danger', 'border-success');
                    }
                }
            }

            bidInput.addEventListener('input', updateBidFeedback);

            // Handle Shortcut Buttons
            document.querySelectorAll('.bid-shortcut').forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = this.getAttribute('data-amount');
                    bidInput.value = amount;
                    updateBidFeedback();
                    
                    // Add active class temporarily for visual feedback
                    this.classList.add('active');
                    setTimeout(() => this.classList.remove('active'), 200);
                });
            });

            placeBidForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const val = parseFloat(bidInput.value);

                if (isNaN(val) || val < minIncrement || val > maxIncrement) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Bid',
                        text: `Please enter an increment between ₹${minIncrement.toFixed(2)} and ₹${maxIncrement.toFixed(2)}.`,
                        confirmButtonColor: '#4e73df'
                    });
                    return;
                }

                const newTotal = (currentPrice + val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                Swal.fire({
                    title: 'Confirm Your Bid',
                    html: `You are adding <strong>₹${val.toFixed(2)}</strong> to the current price.<br>Your total bid will be <strong>₹${newTotal}</strong>.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Place Bid!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }
    });
</script>
@endpush

@endsection
