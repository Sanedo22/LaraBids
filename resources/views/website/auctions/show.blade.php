@extends('website.layouts.app')

@section('title', 'Auction Details | LaraBids')

@section('content')

<!-- Auction Details -->
<section class="py-5 mt-5">
    <div class="container py-lg-5">
        <div class="row g-5">
            <!-- Left: Image Gallery (Col-lg-7) -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="position-relative overflow-hidden rounded-4 mb-3 auction-main-img-container" style="height: 550px; cursor: zoom-in;">
                    @if($auction->image)
                        <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" class="w-100 h-100 object-fit-cover main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @else
                        <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                            class="w-100 h-100 object-fit-cover main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @endif
                    <div class="zoom-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0 transition-all" onclick="openLightbox()">
                        <i class="fas fa-search-plus fa-3x text-white"></i>
                    </div>
                </div>
                
                <!-- Thumbnail Slider -->
                @if($auction->images->count() > 1)
                <div class="row g-3 mt-2">
                    @foreach($auction->images as $img)
                    <div class="col-3 col-md-2">
                        <div class="rounded-3 overflow-hidden border {{ $img->is_primary ? 'border-primary border-2' : 'border-light' }} shadow-sm auction-thumb" 
                            style="height: 80px; cursor: pointer; transition: all 0.2s;"
                            onclick="changeMainImage('{{ asset('storage/' . $img->image_path) }}', this)">
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-100 h-100 object-fit-cover" alt="Thumbnail">
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Right: Action Sidebar (Col-lg-5) -->
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card card-elite p-4 mb-4 border-0 shadow-sm" data-aos="fade-left">
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
                                <span class="h2 mb-0 text-primary fw-bold">${{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Starting Price</span>
                                <span class="text-dark small fw-bold">${{ number_format($auction->starting_price, 2) }}</span>
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

                            <form action="{{ route('auctions.bid', $auction->id) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-end-0 text-primary fw-bold">$</span>
                                        <input type="number" name="amount" class="form-control bg-white border-start-0 ps-0 fw-bold @error('amount') is-invalid @enderror" id="bid-amount" 
                                            placeholder="{{ number_format($auction->current_price + 10, 2, '.', '') }}" 
                                            value="{{ old('amount') }}"
                                            min="{{ $auction->current_price + 0.01 }}" step="0.01">
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>
                                    <small class="text-muted mt-2 d-block text-center">Enter more than ${{ number_format($auction->current_price, 2) }}</small>
                                </div>
                                <button type="submit" class="btn btn-gold w-100 py-3 rounded-pill fw-bold shadow">
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
                            <button class="btn btn-outline-primary flex-fill rounded-pill py-2">
                                <i class="far fa-heart me-2"></i>Wishlist
                            </button>
                            <button class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Compact Seller Info -->
                    <div class="card card-elite p-4 border-0 shadow-sm" data-aos="fade-left" data-aos-delay="100">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($auction->user->name) }}&background=4e73df&color=ffffff"
                                class="rounded-circle border border-primary border-opacity-25" height="45" alt="Seller">
                            <div class="flex-grow-1">
                                <h6 class="text-dark fw-bold mb-0">{{ $auction->user->name }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-success fw-bold" style="font-size: 0.7rem;">VERIFIED</small>
                                    <div class="text-warning small" style="font-size: 0.7rem;">
                                        <i class="fas fa-star"></i> 4.9
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Row -->
        <div class="row g-5 mt-4">
            <!-- Left: Description & Specs (Col-lg-8) -->
            <div class="col-lg-8" data-aos="fade-up">
                <!-- Description Card -->
                <div class="card card-elite p-4 border-0 shadow-sm mb-4">
                    <h4 class="h5 text-dark mb-4 fw-bold border-bottom pb-3">Product Description</h4>
                    <div class="text-secondary lh-lg">
                        {!! nl2br(e($auction->description)) !!}
                    </div>
                </div>

                <!-- Specifications Card -->
                @if($auction->specifications && count(array_filter($auction->specifications)) > 0)
                <div class="card card-elite p-4 border-0 shadow-sm mb-4">
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
                <div class="card card-elite p-4 border-0 shadow-sm mb-4">
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
                <div class="card card-elite p-4 border-0 shadow-sm">
                    <h4 class="h5 text-dark mb-4 fw-bold border-bottom pb-3">Recent Bids</h4>
                    <ul class="list-unstyled mb-0">
                        @forelse($auction->bids->take(5) as $bid)
                        <li class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light last-child-border-0">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($bid->user->name) }}&background=random" 
                                    class="rounded-circle" height="35" width="35" alt="{{ $bid->user->name }}">
                                <div>
                                    <span class="d-block text-dark fw-bold small">{{ $bid->user->name }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $bid->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <span class="text-primary fw-bold">${{ number_format($bid->amount, 2) }}</span>
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
            @foreach($auction->images as $img)
                "{{ asset('storage/' . $img->image_path) }}",
            @endforeach
        ];
        initGallery(images, "{{ str_replace('"', '\"', $auction->title) }}");
    });
</script>
@endpush

@endsection
