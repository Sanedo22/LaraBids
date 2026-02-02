@extends('website.layouts.app')

@section('title', 'LaraBids | Premier Online Auction Platform')

@section('content')

<!-- Hero -->
<section class="hero-section text-white d-flex align-items-center">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    🚀 DISCOVER THE FUTURE OF AUCTIONS
                </span>
                <h1 class="display-2 fw-bold mb-4">LaraBids: Browse, <br><span class="text-white">Bid, and Win</span></h1>
                <p class="lead mb-5 opacity-75 pe-lg-5">
                    Join the premier community for high-value acquisitions. From rare collectibles to everyday electronics,
                    experience the power of professional bidding.
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <a href="{{ route('auctions.index') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">Browse Live Bids</a>
                    <a href="#how-it-works" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-illustration-wrapper ps-lg-5 text-center">
                    <!-- Glow blob for depth -->
                    <div class="hero-glow-blob"></div>
                    
                    <!-- Template Banner -->
                    <img src="{{ asset('assets/images/banner-3.png') }}" 
                         class="img-fluid" 
                         alt="LaraBids Premium Auctions"
                         style="max-height: 500px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">

                </div>
            </div>
        </div>
    </div>
</section>


<!-- Live Auctions -->
<section id="auctions" class="py-5 metrics-overlap">
    <div class="container py-lg-5">
        <div class="row align-items-center mb-5" data-aos="fade-right">
            <div class="col-lg-4">
                <h2 class="display-4 fw-bold">Live Now</h2>
            </div>
            <div class="col-lg-8">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('auctions.index') }}" class="category-pill active">All Auctions</a>
                    @foreach($categories as $category)
                    <a href="{{ route('auctions.index', ['category' => $category->slug]) }}" class="category-pill">
                        <i class="{{ $category->icon }} me-2"></i>{{ $category->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($auctions as $index => $auction)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                <div class="card card-elite h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white hover-shadow-lg transition-all">
                    <!-- Image Section -->
                    <div class="position-relative overflow-hidden" style="height: 240px;">
                        @if($auction->image)
                            <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" class="card-img-top h-100 object-fit-cover shadow-sm" alt="{{ $auction->title }}">
                        @else
                            <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                                class="card-img-top h-100 object-fit-cover shadow-sm" alt="{{ $auction->title }}">
                        @endif
                        <div class="position-absolute top-0 start-0 m-3" style="z-index: 2;">
                            <span class="badge bg-gold text-dark shadow-sm">{{ $auction->category->name ?? 'Uncategorized' }}</span>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        @php
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($auction->end_time);
                            $diff = $now->diff($end);
                            $isClosed = $now->greaterThan($end);
                        @endphp
                        
                        @if(!$isClosed)
                        <div class="glass-timer text-center py-2 timer-val mb-3 shadow-none border" 
                            data-days="{{ $diff->d }}" 
                            data-hours="{{ $diff->h }}" 
                            data-min="{{ $diff->i }}" 
                            data-sec="{{ $diff->s }}">
                            <div class="row g-0 px-3">
                                <div class="col">
                                    <div class="fw-bold fs-6" data-days>{{ sprintf('%02d', $diff->d) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.6rem;">Days</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold fs-6" data-hours>{{ sprintf('%02d', $diff->h) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.6rem;">Hrs</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold fs-6" data-min>{{ sprintf('%02d', $diff->i) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.6rem;">Min</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold fs-6" data-sec>{{ sprintf('%02d', $diff->s) }}</div>
                                    <small class="opacity-50 text-uppercase" style="font-size: 0.6rem;">Sec</small>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-danger py-2 mb-3 text-center small border-0">Closed</div>
                        @endif

                        <h3 class="h5 mb-2 fw-bold text-dark">{{ $auction->title }}</h3>
                        <p class="text-secondary small mb-4 line-clamp-2" style="min-height: 3em;">{{ Str::limit($auction->description, 60) }}</p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                                <span class="small text-secondary fw-bold text-uppercase">Current Bid</span>
                                <span class="h5 mb-0 text-primary fw-bold">${{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            <a href="{{ route('auctions.show', $auction->id) }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold stretched-link shadow-sm transition-all">
                                Submit Bid <i class="fas fa-gavel ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Upcoming Auctions -->
<section class="py-5 bg-navy-shade">
    <div class="container py-lg-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge-elite mb-2 d-inline-block">Watch This Space</span>
            <h2 class="display-4 fw-bold">Upcoming Auctions</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card card-elite glass-panel border-0">
                    <div class="p-4">
                        <h5 class="text-primary mb-1">Modernism in Kyoto</h5>
                        <p class="small text-secondary mb-3">Architectural Masterpiece Collection</p>
                        <div class="d-flex align-items-center gap-2 mb-0">
                            <i class="far fa-calendar text-primary"></i>
                            <span class="small text-dark">Starts Feb 14, 2026</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card card-elite glass-panel border-0">
                    <div class="p-4">
                        <h5 class="text-primary mb-1">The Heritage Chronos</h5>
                        <p class="small text-secondary mb-3">50 Rare Timepieces from 1920-1950</p>
                        <div class="d-flex align-items-center gap-2 mb-0">
                            <i class="far fa-calendar text-primary"></i>
                            <span class="small text-dark">Starts Mar 02, 2026</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card card-elite glass-panel border-0">
                    <div class="p-4">
                        <h5 class="text-primary mb-1">Abstract Expressionism</h5>
                        <p class="small text-secondary mb-3">Post-War American Art Auction</p>
                        <div class="d-flex align-items-center gap-2 mb-0">
                            <i class="far fa-calendar text-primary"></i>
                            <span class="small text-dark">Starts Mar 10, 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How LaraBids Works -->
<section id="how-it-works" class="py-5 bg-dark-elite">
    <div class="container py-lg-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge-elite mb-2 d-inline-block">The Process</span>
            <h2 class="display-4 fw-bold text-white">How <span class="text-white">LaraBids</span> Works</h2>
        </div>
        <div class="row g-4 step-connector">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card text-center p-4">
                    <div class="step-number mb-4 mx-auto">01</div>
                    <h4 class="h5 text-white mb-3">Quick Registration</h4>
                    <p class="text-secondary small">Create your account and verify your email to join our
                        global community of bidders.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card text-center p-4">
                    <div class="step-number mb-4 mx-auto">02</div>
                    <h4 class="h5 text-white mb-3">Place Your Bid</h4>
                    <p class="text-secondary small">Engage in real-time bidding for verified items with transparent
                        history and real-time alerts.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card text-center p-4">
                    <div class="step-number mb-4 mx-auto">03</div>
                    <h4 class="h5 text-white mb-3">Secure Delivery</h4>
                    <p class="text-secondary small">Upon winning, enjoy secure transaction
                        handling and reliable delivery for your new item.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Client Testimonials -->
<section class="py-5">
    <div class="container py-lg-5">
        <!-- Section Header -->
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge-elite mb-2 d-inline-block">Testimonials</span>
            <h2 class="display-4 fw-bold">What Our <span class="text-primary">Clients Say</span></h2>
            <p class="lead text-secondary mt-3">Hear from satisfied bidders and sellers in our community</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                    <!-- Carousel Indicators -->
                    <div class="carousel-indicators mb-n4">
                        @foreach($testimonials as $index => $testimonial)
                        <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }} bg-primary" aria-current="{{ $index == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>

                    <div class="carousel-inner overflow-visible">
                        @foreach($testimonials as $index => $testimonial)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <div class="glass-panel-premium p-5 text-center mx-3 my-4">
                                <div class="mb-4">
                                    @for($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star text-warning small"></i>
                                    @endfor
                                </div>
                                <i class="fas fa-quote-left text-primary fs-1 mb-4 opacity-25"></i>
                                <p class="h4 display-font text-dark mb-4 lh-base fw-semibold px-lg-5">
                                    "{{ $testimonial->content }}"
                                </p>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $testimonial->avatar_url }}"
                                            class="rounded-circle border border-primary border-2 shadow-sm" width="60" height="60" alt="{{ $testimonial->name }}" style="object-fit: cover;">
                                        <div class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 14px; height: 14px; border-width: 2px !important;"></div>
                                    </div>
                                    <div class="text-start">
                                        <h6 class="text-primary mb-0 fw-bold">{{ $testimonial->name }}</h6>
                                        <small class="text-secondary opacity-75">{{ $testimonial->role }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($testimonials->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-primary rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-primary rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Promotional Banner Section -->
<section class="py-5 bg-dark-elite position-relative overflow-hidden">
    <div class="container py-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                @guest
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    🎯 START WINNING TODAY
                </span>
                <h2 class="display-4 fw-bold text-white mb-4">
                    Ready to Place Your First Bid?
                </h2>
                <p class="lead text-white opacity-75 mb-4">
                    Join thousands of successful bidders on LaraBids. Whether you're hunting for rare collectibles, 
                    premium electronics, or unique treasures, your next win is just a bid away.
                </p>
                @else
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    🎯 KEEP WINNING
                </span>
                <h2 class="display-4 fw-bold text-white mb-4">
                    Find Your Next Treasure
                </h2>
                <p class="lead text-white opacity-75 mb-4">
                    Welcome back, {{ Auth::user()->name }}! Explore our latest auctions and continue your winning streak. 
                    From rare collectibles to premium electronics, your next win awaits.
                </p>
                @endguest
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-3 text-white">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Verified Sellers</strong> - All items authenticated
                    </li>
                    <li class="mb-3 text-white">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Secure Payments</strong> - Protected transactions
                    </li>
                    <li class="mb-3 text-white">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Real-time Bidding</strong> - Never miss an opportunity
                    </li>
                </ul>
                
                <div class="d-flex flex-wrap gap-3">
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">
                        <i class="fas fa-user-plus me-2"></i>Create Free Account
                    </a>
                    <a href="{{ route('auctions.index') }}" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">
                        <i class="fas fa-gavel me-2"></i>Browse Auctions
                    </a>
                    @else
                    <a href="{{ route('auctions.index') }}" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">
                        <i class="fas fa-gavel me-2"></i>Browse Live Auctions
                    </a>
                    <a href="{{ route('auctions.create') }}" class="btn btn-outline-gold btn-lg px-5 py-3 rounded-pill">
                        <i class="fas fa-plus-circle me-2"></i>Create Auction
                    </a>
                    @endguest
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="position-relative">
                    <div class="hero-glow-blob"></div>
                    <img src="{{ asset('assets/images/banner-2.png') }}" 
                         class="img-fluid" 
                         alt="Start Bidding on LaraBids"
                         style="max-height: 500px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted Partners -->
<section class="py-5 border-top border-light">
    <div class="container py-2">
        <div class="row g-4 align-items-center justify-content-center opacity-75 px-lg-5">
            <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="100"><i
                    class="fab fa-fedex fs-1 text-secondary partner-logo"></i></div>
            <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="200"><i
                    class="fab fa-ups fs-1 text-secondary partner-logo"></i></div>
            <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="300"><i
                    class="fab fa-dhl fs-1 text-secondary partner-logo"></i></div>
            <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="400"><i
                    class="fab fa-stripe fs-1 text-secondary partner-logo"></i></div>
            <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="500"><i
                    class="fab fa-apple-pay fs-1 text-secondary partner-logo"></i></div>

        </div>
    </div>
</section>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var myCarousel = document.getElementById('testimonialCarousel');
    if (myCarousel) {
        // Initialize the carousel
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 4000,
            pause: 'hover',
            ride: 'carousel',
            wrap: true
        });
        
        // Force start cycling
        carousel.cycle();
        
        console.log('Testimonials carousel initialized and cycle forced.');
    }
});
</script>
@endpush

@endsection
