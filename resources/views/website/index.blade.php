@extends('website.layouts.app')

@section('title', 'LaraBids | Premier Online Auction Platform')

@section('content')

<!-- Hero Carousel -->
<section id="heroCarousel" class="carousel slide hero-section">
    <!-- Indicators -->
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>

    <div class="carousel-inner">
        <!-- Slide 1 (Original) -->
        <div class="carousel-item active">
            <div class="container h-100 d-flex align-items-center" data-aos="fade-up">
                <div class="row align-items-center w-100">
                    <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                        <h1 class="display-2 fw-bold mb-4">LaraBids: Browse, <br><span class="text-white text-glow">Bid, and Win</span></h1>
                        <p class="lead mb-5 opacity-75 pe-lg-5">
                            Join the premier community for high-value acquisitions. From rare collectibles to everyday electronics,
                            experience the power of professional bidding.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                            <a href="{{ route('auctions.index') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">Browse Live Bids</a>
                            <a href="#how-it-works" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">Learn More</a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-illustration-wrapper ps-lg-5 text-center">
                            <div class="hero-glow-blob"></div>
                            <img src="{{ asset('assets/images/banner-3.png') }}" class="img-fluid" alt="LaraBids Premium Auctions" style="max-height: 500px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-item">
            <div class="container h-100 d-flex align-items-center">
                <div class="row align-items-center w-100">
                    <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                        <h2 class="display-2 fw-bold mb-4 text-white">Launch Your <br><span class="text-glow">Auction Today</span></h2>
                        <p class="lead mb-5 opacity-75 pe-lg-5">
                            Transform your collectibles into profit. Connect with high-intent bidders and achieve the ultimate hammer price for your most prized possessions.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                            <a href="{{ route('auctions.create') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">Start Selling</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">Join Marketplace</a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-illustration-wrapper ps-lg-5 text-center">
                            <div class="hero-glow-blob" style="background: rgba(255, 215, 0, 0.2);"></div>
                            <img src="{{ asset('assets/images/banner-2.png') }}" class="img-fluid" alt="LaraBids Rare Items" style="max-height: 500px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-item">
            <div class="container h-100 d-flex align-items-center">
                <div class="row align-items-center w-100">
                    <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                        <h2 class="display-2 fw-bold mb-4 text-white">Trusted by <br><span class="text-glow">Thousands</span></h2>
                        <p class="lead mb-5 opacity-75 pe-lg-5">
                            Join India's most secure auction community. Enjoy verified listings, real-time bid tracking, and seamless, protected payments on every transaction.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                            <a href="{{ route('auctions.index') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">View Live Bids</a>
                            <a href="{{ route('about') }}" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">How it works</a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-illustration-wrapper ps-lg-5 text-center">
                            <div class="hero-glow-blob" style="background: rgba(0, 255, 127, 0.2);"></div>
                            <img src="{{ asset('assets/images/banner-3.png') }}" class="img-fluid" alt="LaraBids Secure" style="max-height: 500px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</section>


<!-- Live Auctions -->
<section id="auctions" class="pt-5 pb-0">
    <div class="container pt-lg-5 pb-0">
        <div class="row align-items-end mb-5" data-aos="fade-right">
            <div class="col-lg-6">
                <h2 class="display-4 fw-bold mb-3">Live Now</h2>
                <p class="text-secondary lead mb-0">Explore our most popular auctions currently open for bidding.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm mt-3 mt-lg-0">
                    View All Auctions <i class="fas fa-chevron-right ms-2"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            @foreach($auctions as $index => $auction)
            <div class="col-md-6 col-lg-3">
                <div class="card card-elite h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white hover-shadow-lg transition-all">
                    <a href="{{ route('auctions.show', $auction->id) }}" class="stretched-link"></a>
                    <!-- Image Section -->
                    <div class="position-relative overflow-hidden" style="height: 180px;">
                        <div class="d-block w-100 h-100">
                            @if($auction->image)
                                <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $auction->title }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                                    class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $auction->title }}">
                            @endif
                        </div>
                        <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                            <span class="badge bg-gold text-dark shadow-sm fw-bold" style="font-size: 0.7rem;">{{ $auction->category->name ?? 'Uncategorized' }}</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                            <form action="{{ route('user.watchlist.toggle', $auction->id ?? 0) }}" method="POST" class="watchlist-toggle-form">
                                @csrf
                                <button type="submit" class="btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px; border: none; background: rgba(255,255,255,0.8); backdrop-filter: blur(4px);">
                                    <i class="{{ $auction->watchlists->isNotEmpty() ? 'fas' : 'far' }} fa-heart text-danger" style="font-size: 0.8rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="card-body p-3 d-flex flex-column flex-grow-1">
                        @php
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($auction->end_time);
                            $diff = $now->diff($end);
                            $isClosed = $now->greaterThan($end);
                        @endphp
                        
                        @if(!$isClosed)
                        <div class="glass-timer text-center py-1 timer-val mb-2 shadow-none border {{ ($diff->d == 0 && $diff->h == 0) ? 'urgent-timer' : '' }}" 
                            data-days="{{ $diff->d }}" 
                            data-hours="{{ $diff->h }}" 
                            data-min="{{ $diff->i }}" 
                            data-sec="{{ $diff->s }}">
                            <div class="row g-0 px-2">
                                <div class="col border-end border-light">
                                    <div class="fw-bold fs-7" data-days>{{ sprintf('%02d', $diff->d) }}</div>
                                    <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">D</small>
                                </div>
                                <div class="col border-end border-light">
                                    <div class="fw-bold fs-7" data-hours>{{ sprintf('%02d', $diff->h) }}</div>
                                    <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">H</small>
                                </div>
                                <div class="col border-end border-light">
                                    <div class="fw-bold fs-7" data-min>{{ sprintf('%02d', $diff->i) }}</div>
                                    <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">M</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold fs-7 text-primary" data-sec>{{ sprintf('%02d', $diff->s) }}</div>
                                    <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">S</small>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-danger alert-permanent border-0 py-1 mb-2 text-center small fw-bold" style="font-size: 0.7rem; background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                            <i class="fas fa-times-circle me-1"></i> Auction Closed
                        </div>
                        @endif

                        <h3 class="h6 mb-1 fw-bold text-dark text-truncate title-hover">
                            {{ $auction->title }}
                        </h3>
                        <div class="mb-2">
                            <span class="copy-id text-muted extra-small" style="font-size: 0.65rem;" onclick="event.preventDefault(); event.stopPropagation(); copyToClipboard('{{ $auction->id }}', this)" title="Click to copy ID">ID: #{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1"></i></span>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                @if($auction->user && $auction->user->avatar)
                                    <img src="{{ str_starts_with($auction->user->avatar, 'http') ? $auction->user->avatar : asset('storage/' . $auction->user->avatar) }}" class="rounded-circle me-1 border" width="20" height="20" style="object-fit: cover;" alt="{{ $auction->user->name }}">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-1 border" style="width: 20px; height: 20px;">
                                        <i class="fas fa-user text-secondary" style="font-size: 10px;"></i>
                                    </div>
                                @endif
                                <span class="text-xs text-muted text-truncate" style="max-width: 80px;">{{ $auction->user->name ?? 'Seller' }}</span>
                            </div>
                            <span class="badge bg-light text-secondary border fw-normal text-xs px-2 py-1">
                                {{ $auction->bids->count() }} Bids
                            </span>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                                <span class="text-xs text-secondary fw-bold text-uppercase">{{ $isClosed ? 'Final Bid' : 'Current Bid' }}</span>
                                <span class="h6 mb-0 text-primary fw-bold">₹{{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            <div class="btn {{ $isClosed ? 'btn-outline-secondary' : 'btn-primary' }} w-100 py-2 rounded-pill fw-bold shadow-sm transition-all btn-hover-effect" style="font-size: 0.8rem;">
                                @if($isClosed) CLOSED @else BID NOW @endif <i class="fas {{ $isClosed ? 'fa-lock' : 'fa-gavel' }} ms-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Upcoming Auctions -->
<section class="py-4 bg-navy-shade">
    <div class="container py-lg-4">
        <div class="row align-items-end mb-5" data-aos="fade-up">
            <div class="col-lg-6 text-center text-lg-start">
                <h2 class="display-4 fw-bold mb-0">Upcoming Events</h2>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <a href="{{ route('auctions.index', ['status' => 'upcoming']) }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm mt-3 mt-lg-0">
                    View All Upcoming <i class="fas fa-chevron-right ms-2"></i>
                </a>
            </div>
        </div>
        @if(count($upcomingAuctions) > 0)
        <div class="position-relative">
            <div class="swiper upcoming-swiper px-3">
                <div class="swiper-wrapper">
                    @foreach($upcomingAuctions as $upcoming)
                    <div class="swiper-slide h-auto">
                        <div class="card card-elite h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white hover-shadow-lg transition-all">
                            <a href="{{ route('auctions.show', $upcoming->id) }}" class="stretched-link"></a>
                            <!-- Image Section -->
                            <div class="position-relative overflow-hidden" style="height: 180px;">
                                <div class="d-block w-100 h-100">
                                    @if($upcoming->image)
                                        <img src="{{ str_starts_with($upcoming->image, 'http') ? $upcoming->image : asset('storage/' . $upcoming->image) }}" class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $upcoming->title }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                                            class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $upcoming->title }}">
                                    @endif
                                </div>
                                <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                                    <span class="badge bg-gold text-dark shadow-sm fw-bold" style="font-size: 0.7rem;">{{ $upcoming->category->name ?? 'Uncategorized' }}</span>
                                </div>
                                <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                                    <form action="{{ route('user.watchlist.toggle', $upcoming->id ?? 0) }}" method="POST" class="watchlist-toggle-form">
                                        @csrf
                                        <button type="submit" class="btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px; border: none; background: rgba(255,255,255,0.8); backdrop-filter: blur(4px);">
                                            <i class="{{ $upcoming->watchlists->isNotEmpty() ? 'fas' : 'far' }} fa-heart text-danger" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Content Section -->
                            <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $startTime = \Carbon\Carbon::parse($upcoming->start_time);
                                    $diff = $now->diff($startTime);
                                @endphp
                                
                                <div class="alert alert-info alert-permanent py-1 mb-2 text-center small border-0 fw-bold" style="font-size: 0.7rem; background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                                    <i class="fas fa-clock me-1"></i> Starts {{ $startTime->format('M d, H:i') }}
                                </div>

                                <h3 class="h6 mb-1 fw-bold text-dark text-truncate title-hover">
                                    {{ $upcoming->title }}
                                </h3>
                                <div class="mb-2">
                                    <span class="copy-id text-muted extra-small" style="font-size: 0.65rem;" onclick="event.preventDefault(); event.stopPropagation(); copyToClipboard('{{ $upcoming->id }}', this)" title="Click to copy ID">ID: #{{ str_pad($upcoming->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1"></i></span>
                                </div>
                                
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        @if($upcoming->user && $upcoming->user->avatar)
                                            <img src="{{ str_starts_with($upcoming->user->avatar, 'http') ? $upcoming->user->avatar : asset('storage/' . $upcoming->user->avatar) }}" class="rounded-circle me-1 border" width="20" height="20" style="object-fit: cover;" alt="{{ $upcoming->user->name }}">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-1 border" style="width: 20px; height: 20px;">
                                                <i class="fas fa-user text-secondary" style="font-size: 10px;"></i>
                                            </div>
                                        @endif
                                        <span class="text-xs text-muted text-truncate" style="max-width: 80px;">{{ $upcoming->user->name ?? 'Seller' }}</span>
                                    </div>
                                    <span class="badge bg-light text-secondary border fw-normal text-xs px-2 py-1">
                                        {{ $upcoming->bids->count() }} Bids
                                    </span>
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                                        <span class="text-xs text-secondary fw-bold text-uppercase">Current Bid</span>
                                        <span class="h6 mb-0 text-primary fw-bold">₹{{ number_format($upcoming->current_price, 2) }}</span>
                                    </div>
                                    <div class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm transition-all btn-hover-effect" style="font-size: 0.8rem;">
                                        VIEW <i class="fas fa-gavel ms-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Navigation Buttons -->
            <div class="swiper-button-next upcoming-next"></div>
            <div class="swiper-button-prev upcoming-prev"></div>
        </div>
        @else
        <div class="text-center py-5">
            <div class="opacity-50 mb-3">
                <i class="fas fa-calendar-times fa-3x text-secondary"></i>
            </div>
            <h5 class="text-secondary">No upcoming auctions scheduled yet.</h5>
            <p class="small text-muted">Stay tuned for amazing deals!</p>
        </div>
        @endif
    </div>
</section>






@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .bg-navy-shade { background-color: #f8f9fc; }
    .text-glow {
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.4), 0 0 20px rgba(78, 115, 223, 0.2);
    }
    .card-elite {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-elite:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .bg-gold { background-color: #d4af37; }
    .glass-timer {
        background: rgba(248, 249, 250, 0.8);
        backdrop-filter: blur(4px);
        border-radius: 8px;
    }
    .fs-7 { font-size: 0.9rem; }
    .text-primary { color: #4e73df !important; }
    .text-xs { font-size: 0.75rem; }
    .urgent-timer {
        background: rgba(220, 53, 69, 0.08) !important;
        border-color: rgba(220, 53, 69, 0.3) !important;
    }
    .urgent-timer * { color: #dc3545 !important; }
    .title-hover:hover { color: #4e73df !important; }
    .btn-hover-effect:active { transform: scale(0.98); }

    /* Swiper Slider Styles */
    .upcoming-swiper { width: 100%; overflow: hidden; }
    .swiper-wrapper { display: flex !important; flex-direction: row !important; }
    .upcoming-next, .upcoming-prev {
        background: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #4e73df !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 10;
        transition: all 0.3s ease;
    }
    .upcoming-next:after, .upcoming-prev:after { font-size: 1.1rem; font-weight: bold; }
    .upcoming-next { right: -50px; }
    .upcoming-prev { left: -50px; }

    @media (max-width: 1400px) {
        .upcoming-next { right: -25px; }
        .upcoming-prev { left: -25px; }
    }

    @media (max-width: 1200px) {
        .upcoming-next { right: 10px; }
        .upcoming-prev { left: 10px; }
    }

    /* --- Testimonials Premium Style --- */
    .testimonial-card-premium {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 30px;
        padding: 3rem;
        text-align: center;
        margin: 1rem 0 3rem 0;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }
    .testimonial-quote-icon {
        font-size: 2.5rem;
        color: #4e73df;
        opacity: 0.15;
        margin-bottom: 1.5rem;
    }
    .testimonial-text {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2e59d9;
        line-height: 1.6;
        font-style: italic;
    }
    .testimonial-author-box {
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        text-align: left;
    }
    .author-avatar-wrapper { position: relative; }
    .author-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }
    .avatar-verified {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #1cc88a;
        color: white;
        font-size: 0.6rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }
    .author-name { color: #1a202c; font-size: 1rem; font-weight: 700; }
    .author-role { color: #718096; font-size: 0.85rem; }
    
    .testimonial-dot {
        width: 10px !important;
        height: 10px !important;
        border-radius: 50% !important;
        border: none !important;
        background-color: #cbd5e0 !important;
        margin: 0 5px !important;
        transition: all 0.3s ease !important;
    }
    .testimonial-dot.active {
        background-color: #4e73df !important;
        width: 25px !important;
        border-radius: 20px !important;
    }
    .testimonial-btn-prev, .testimonial-btn-next {
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 50%;
        color: #4e73df !important;
        opacity: 1;
        top: 50%;
        transform: translateY(-50%);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        z-index: 10;
        font-size: 0.9rem;
    }
    .testimonial-btn-prev:hover, .testimonial-btn-next:hover {
        background: #4e73df;
        color: #fff !important;
        box-shadow: 0 6px 15px rgba(78, 115, 223, 0.3);
    }
    .testimonial-btn-prev { left: -60px; }
    .testimonial-btn-next { right: -60px; }

    @media (max-width: 1200px) {
        .testimonial-btn-prev { left: -30px; }
        .testimonial-btn-next { right: -30px; }
    }

    @media (max-width: 991px) {
        .testimonial-btn-prev { left: 5px; }
        .testimonial-btn-next { right: 5px; }
        .testimonial-card-premium { padding: 2rem; }
    }
</style>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var heroCarousel = document.querySelector('#heroCarousel');
    if (heroCarousel) {
        var carouselInstance = bootstrap.Carousel.getOrCreateInstance(heroCarousel, {
            interval: 5000,
            pause: false,
            ride: 'carousel',
            wrap: true
        });
        carouselInstance.cycle();
    }

    var myCarousel = document.getElementById('testimonialCarousel');
    if (myCarousel) {
        // Initialize the carousel
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 5000,
            pause: 'hover',
            ride: 'carousel',
            wrap: true
        });
        
        // Force start cycling
        carousel.cycle();
        
        console.log('Testimonials carousel initialized and cycle forced.');
    }

    // Initialize Swiper for Upcoming Auctions
    if (document.querySelector('.upcoming-swiper')) {
        const swiper = new Swiper('.upcoming-swiper', {
            slidesPerView: 1,
            spaceBetween: 25,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            slidesPerGroup: 1,
            grabCursor: true,
            navigation: {
                nextEl: '.upcoming-next',
                prevEl: '.upcoming-prev',
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                992: { slidesPerView: 4 }
            }
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@endpush
@endsection

