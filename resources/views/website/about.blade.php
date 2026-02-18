@extends('website.layouts.app')

@section('title', 'About Us | LaraBids')

@section('content')

<!-- Hero Header -->
<section class="hero-section text-white d-flex align-items-center">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    ✨ OUR STORY & MISSION
                </span>
                <h1 class="display-3 fw-bold mb-3">About <span class="text-white">LaraBids</span></h1>
                <p class="lead opacity-75 pe-lg-5">
                    LaraBids is the premier online auction platform designed for high-value acquisitions and a seamless bidding experience for collectors worldwide.
                </p>
                <div class="mt-4">
                    <a href="#about-content" class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow">Explore Our Journey</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-illustration-wrapper ps-lg-4 text-center">
                    <div class="hero-glow-blob"></div>
                    <img src="{{ asset('assets/images/banner-1.png') }}" 
                         class="img-fluid" 
                         alt="About LaraBids"
                         style="max-height: 400px; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.3));">

                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= MISSION & VISION ================= -->
<section id="about-content" class="py-5 bg-light position-relative">
    <div class="container py-lg-5">
        <div class="row g-4">
            <!-- Mission -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-up">
                    <div class="icon-box-lg bg-primary-soft text-primary mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(78, 115, 223, 0.1);">
                        <i class="fas fa-bullseye fs-3"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Our Mission</h3>
                    <p class="text-muted mb-0">
                        To democratize access to rare and valuable items by providing a transparent, secure, and accessible auction platform for everyone, everywhere.
                    </p>
                </div>
            </div>

            <!-- Vision -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-up">
                    <div class="icon-box-lg bg-warning-soft text-warning mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(255, 193, 7, 0.1);">
                        <i class="fas fa-eye fs-3"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Our Vision</h3>
                    <p class="text-muted mb-0">
                        To become the global standard for online auctions, where integrity meets innovation, creating a vibrant community of passionate collectors and sellers.
                    </p>
                </div>
            </div>

            <!-- Values -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-up">
                    <div class="icon-box-lg bg-success-soft text-success mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(28, 200, 138, 0.1);">
                        <i class="fas fa-heart fs-3"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Our Values</h3>
                    <p class="text-muted mb-0">
                        Trust, transparency, and community are the pillars of LaraBids. We believe in fair play, authentic items, and putting our users' safety above all else.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= ABOUT STORY SECTION ================= -->
<section class="py-5">
    <div class="container py-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Our Team Meeting" class="img-fluid rounded-4 shadow-lg position-relative z-1">
                    <!-- Decorative Elements -->
                    <div class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle" style="width: 100px; height: 100px; z-index: 0; opacity: 0.1;"></div>
                    <div class="position-absolute bottom-0 end-0 translate-middle-x bg-warning rounded-circle" style="width: 80px; height: 80px; z-index: 2; transform: translate(30%, 30%); opacity: 0.8;"></div>
                    <div class="experience-badge position-absolute bottom-0 start-0 bg-white p-4 rounded-4 shadow-lg mb-n4 ms-n4 z-index-2 d-none d-md-block" style="z-index: 2;">
                        <div class="d-flex align-items-center">
                            <h2 class="display-4 fw-bold text-primary mb-0 me-3">10+</h2>
                            <div class="lh-1">
                                <span class="d-block fw-bold text-dark">Years of</span>
                                <span class="text-muted small">Excellence</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="text-primary fw-bold text-uppercase small mb-2 d-block" style="letter-spacing: 0.1em;">Our Journey</span>
                <h2 class="display-5 fw-bold mb-4 text-dark">From a Simple Idea to a <span class="text-primary">Global Marketplace</span></h2>
                <p class="lead text-secondary mb-4">
                    Founded in 2024, LaraBids started with a simple question: "Why should high-end auctions be exclusive?"
                </p>
                <p class="text-secondary mb-4">
                    What began as a small project to help local collectors trade items has grown into a comprehensive platform serving thousands of users. We've bridged the gap between traditional auction houses and the digital age, bringing the thrill of the gavel to your screen.
                </p>
                <p class="text-secondary mb-5">
                    Today, LaraBids handles millions in transactions, but our core promise remains the same: <strong>Authenticity Guaranteed.</strong> Every item, every bid, and every user is verified to ensure a safe ecosystem.
                </p>

                <div class="row g-4">
                    <div class="col-6">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> Verified Sellers
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> Secure Payments
                            </li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> Global Shipping
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> 24/7 Expert Support
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= STATS COUNTER ================= -->
<section class="py-5 bg-primary text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4='); opacity: 0.3;"></div>

    <div class="container position-relative z-1 py-4">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="p-3 border-end border-white border-opacity-10">
                    <h2 class="display-4 fw-bold mb-0 text-white">10K+</h2>
                    <p class="opacity-75 mb-0 text-uppercase small" style="letter-spacing: 0.1em;">Active Users</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="p-3 border-end border-white border-opacity-10">
                    <h2 class="display-4 fw-bold mb-0 text-white">50K+</h2>
                    <p class="opacity-75 mb-0 text-uppercase small" style="letter-spacing: 0.1em;">Auctions Closed</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="p-3 border-end border-white border-opacity-10">
                    <h2 class="display-4 fw-bold mb-0 text-white">₹10M+</h2>
                    <p class="opacity-75 mb-0 text-uppercase small" style="letter-spacing: 0.1em;">Volume Traded</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="400">
                <div class="p-3">
                    <h2 class="display-4 fw-bold mb-0 text-white">99%</h2>
                    <p class="opacity-75 mb-0 text-uppercase small" style="letter-spacing: 0.1em;">Happy Customers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US ================= -->
<section class="py-5 bg-light">
    <div class="container py-lg-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase small" style="letter-spacing: 0.1em;">Features</span>
            <h2 class="display-5 fw-bold text-dark">Why Choose <span class="text-primary">LaraBids?</span></h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                We provide the most comprehensive suite of tools for serious collectors and casual buyers alike.
            </p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-primary rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">Real-Time Bidding</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Experience the adrenaline of live auctions with zero latency. Our websocket technology ensures you never miss a bid.
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-warning rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">Buyer Protection</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Your funds are held in escrow until you receive your item as described. We mediate all disputes fairly.
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-success rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">Market Insights</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Get access to historical pricing data and trends to make informed decisions on your investments.
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-danger rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">Mobile Optimized</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Bid on the go with our fully responsive design. Manage your watchlist and bids from anywhere.
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-info rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">VIP Program</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Exclusive access to high-value auctions and lower fees for our most active members.
                    </p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-md bg-white shadow-sm text-secondary rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #6f42c1 !important;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-0 text-dark">Community Forums</h4>
                    </div>
                    <p class="text-secondary mb-0 small">
                        Connect with other collectors, discuss items, and share your finds in our active community.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CTA SECTION ================= -->

<section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
    <!-- Background Glow -->
    <div class="position-absolute top-50 start-50 translate-middle bg-white rounded-circle" style="width: 500px; height: 500px; filter: blur(150px); opacity: 0.1;"></div>

    <div class="container position-relative z-1 py-4 text-center">
        @auth
            <h2 class="display-5 fw-bold text-white mb-4">Continue Your Journey, {{ Auth::user()->name }}!</h2>
            <p class="lead text-white-50 mb-5 mx-auto" style="max-width: 600px;">
                Your next great find is just a bid away. Check your dashboard for updates or explore new listings.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">
                    Go to Dashboard
                </a>
                <a href="{{ route('auctions.index') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">
                    Explore Auctions
                </a>
            </div>
        @else
            <h2 class="display-5 fw-bold text-white mb-4">Ready to Start Your Collection?</h2>
            <p class="lead text-white-50 mb-5 mx-auto" style="max-width: 600px;">
                Join thousands of collectors who trust LaraBids for their buying and selling. Sign up today and get access to exclusive auctions.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">
                    Join Now - It's Free
                </a>
                <a href="{{ route('auctions.index') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">
                    Browse Auctions
                </a>
            </div>
        @endauth
    </div>
</section>

<style>
/* Custom hover effects that couldn't be done with just utility classes */
.hover-lift:hover {
    transform: translateY(-10px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
}
.hover-up:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
}
</style>

@push('styles')
<style>
@media (max-width: 768px) {
    .display-3 { font-size: 2.5rem; }
    .display-4 { font-size: 2rem; }
    .display-5 { font-size: 1.75rem; }
}
</style>
@endpush

@endsection
