@extends('website.layouts.app')

@section('title', 'Auction Details | LaraBids')

@section('content')

<!-- Auction Details -->
<section class="py-5 mt-5">
    <div class="container py-lg-5">
        <div class="row g-5">
            <!-- Image Gallery -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="position-relative overflow-hidden rounded-3 mb-3 auction-main-img-container" style="height: 500px; cursor: zoom-in;">
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
                <div class="row g-2 mt-2">
                    @foreach($auction->images as $img)
                    <div class="col-3 col-md-2">
                        <div class="rounded-3 overflow-hidden border {{ $img->is_primary ? 'border-primary border-2' : 'border-light' }} shadow-sm auction-thumb" 
                            style="height: 70px; cursor: pointer; transition: all 0.2s;"
                            onclick="changeMainImage('{{ asset('storage/' . $img->image_path) }}', this)">
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-100 h-100 object-fit-cover" alt="Thumbnail">
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>

            <!-- Auction Info -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card card-elite p-4 mb-4 border-0 shadow-sm">
                    <h2 class="h3 text-dark mb-3 fw-bold">{{ $auction->title }}</h2>
                    <p class="badge bg-light text-primary mb-4">{{ $auction->category->name ?? 'Uncategorized' }}</p>


                    <!-- Timer -->
                    @php
                        $now = \Carbon\Carbon::now();
                        $end = \Carbon\Carbon::parse($auction->end_time);
                        $diff = $now->diff($end);
                        $isClosed = $now->greaterThan($end);
                    @endphp

                    @if(!$isClosed)
                    <div class="glass-timer text-center py-3 timer-val mb-4" 
                        data-days="{{ $diff->d }}" data-hours="{{ $diff->h }}" data-min="{{ $diff->i }}" data-sec="{{ $diff->s }}">
                        <div class="row g-0 px-3">
                            <div class="col">
                                <div class="fw-bold fs-4" data-days>{{ sprintf('%02d', $diff->d) }}</div>
                                <small class="opacity-50">DAYS</small>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-4" data-hours>{{ sprintf('%02d', $diff->h) }}</div>
                                <small class="opacity-50">HRS</small>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-4" data-min>{{ sprintf('%02d', $diff->i) }}</div>
                                <small class="opacity-50">MIN</small>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-4" data-sec>{{ sprintf('%02d', $diff->s) }}</div>
                                <small class="opacity-50">SEC</small>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-danger py-3 mb-4 text-center fw-bold">This auction has closed.</div>
                    @endif

                    <!-- Current Bid -->
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">Current Bid</span>
                            <span class="h3 mb-0 text-primary fw-bold">${{ number_format($auction->current_price, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Starting Bid</span>
                            <span class="text-dark small fw-bold">${{ number_format($auction->starting_price, 2) }}</span>
                        </div>
                    </div>


                    <!-- Bid Form -->
                    @if(!$isClosed)
                        @auth
                            <form action="#" method="POST" class="mb-4">
                                @csrf
                                <label for="bid-amount" class="form-label text-dark fw-bold">Your Bid Amount</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light text-primary border-light fw-bold">$</span>
                                    <input type="number" class="form-control bg-light border-light" id="bid-amount" 
                                        placeholder="{{ number_format($auction->current_price + 10, 2, '.', '') }}" 
                                        min="{{ $auction->current_price + 0.01 }}" step="0.01">
                                </div>
                                <button type="submit" class="btn btn-gold w-100 py-3 shadow-sm">Place Bid Now</button>
                            </form>
                        @else
                            <div class="alert alert-light border-light mb-4">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                Please <a href="{{ route('login') }}" class="text-primary fw-bold">login</a> to place a bid.
                            </div>
                        @endauth
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>

                </div>

                <!-- Description Card -->
                <div class="card card-elite p-4 border-0 shadow-sm mt-4" data-aos="fade-up">
                    <h4 class="h5 text-dark mb-4 fw-bold">Product Description</h4>
                    <div class="text-secondary lh-lg small">
                        {!! nl2br(e($auction->description)) !!}
                    </div>
                </div>

                <!-- Specifications Card -->
                @if($auction->specifications && count(array_filter($auction->specifications)) > 0)
                <div class="card card-elite p-4 border-0 shadow-sm mt-4" data-aos="fade-up">
                    <h4 class="h5 text-dark mb-4 fw-bold">Technical Specifications</h4>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                @foreach($auction->specifications as $key => $value)
                                    @if($value)
                                    <tr>
                                        <td class="text-secondary small text-uppercase fw-bold py-2" style="width: 40%;">{{ str_replace('_', ' ', $key) }}</td>
                                        <td class="text-dark fw-bold py-2">{{ $value }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Documents Card -->
                @if($auction->document)
                <div class="card card-elite p-4 border-0 shadow-sm mt-4" data-aos="fade-up">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="h5 text-dark mb-1 fw-bold">Verification Documents</h4>
                            <p class="text-secondary small mb-0">Authenticated and verified by the seller.</p>
                        </div>
                        <a href="{{ asset('storage/' . $auction->document) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                </div>
                @endif

                <!-- Recent Bids Card -->
                <div class="card card-elite p-4 border-0 shadow-sm mt-4" data-aos="fade-up">
                    <h4 class="h5 text-dark mb-4 fw-bold">Recent Bids</h4>
                    <ul class="list-unstyled mb-0">
                        <li class="text-secondary small py-4 text-center border rounded-3 bg-light">
                            <i class="fas fa-history d-block mb-2 opacity-25 fs-2"></i>
                            No bids placed yet.
                        </li>
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
