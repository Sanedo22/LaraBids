@extends('website.layouts.app')

@section('title', $seller->name . ' - Seller Profile | LaraBids')

@section('content')

<!-- Seller Profile Hero -->
<section class="py-5 mt-5 bg-light border-bottom">
    <div class="container py-lg-4">
        <div class="row align-items-center g-4">
            <div class="col-md-auto text-center text-md-start">
                <img src="{{ $seller->avatar_url }}" 
                    class="rounded-circle border border-white shadow-sm p-1 bg-white" 
                    width="150" height="150" style="object-fit: cover;" alt="{{ $seller->name }}">
            </div>
            <div class="col-md text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 mb-2">
                    <h1 class="h2 text-dark fw-bold mb-0">{{ $seller->name }}</h1>
                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i>VERIFIED SELLER</span>
                </div>
                <div class="d-flex flex-wrap items-center justify-content-center justify-content-md-start gap-4 text-secondary mb-3">
                    <span><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $seller->location ?? 'Global' }}</span>
                    <span><i class="fas fa-calendar-alt text-primary me-2"></i>Member Since {{ $stats['member_since'] }}</span>
                    <div class="text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span class="ms-1 fw-bold text-dark">4.9 (42 reviews)</span>
                    </div>
                </div>
                <p class="text-secondary mb-0 max-width-600">
                    {{ $seller->bio ?? 'No bio available for this seller.' }}
                </p>
            </div>
            <div class="col-md-auto text-center text-md-end">
                <div class="bg-white p-4 rounded-4 shadow-sm border border-light">
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-auto px-lg-4 border-end">
                            <div class="h4 fw-bold text-primary mb-0">{{ $stats['total_auctions'] }}</div>
                            <small class="text-muted text-uppercase small" style="font-size: 0.65rem;">Total Items</small>
                        </div>
                        <div class="col-6 col-md-auto px-lg-4">
                            <div class="h4 fw-bold text-success mb-0">{{ $stats['active_auctions'] }}</div>
                            <small class="text-muted text-uppercase small" style="font-size: 0.65rem;">Active Now</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seller's Active Auctions -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="h3 text-dark fw-bold mb-1">Active Auctions</h2>
                <p class="text-secondary mb-0">Check out what {{ $seller->name }} is currently selling.</p>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                    Short By: Latest
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Newest First</a></li>
                    <li><a class="dropdown-item" href="#">Price: Low to High</a></li>
                    <li><a class="dropdown-item" href="#">Price: High to Low</a></li>
                </ul>
            </div>
        </div>

        <div class="row g-4">
            @forelse($seller->auctions as $auction)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden auction-card">
                        <div class="position-relative" style="height: 200px;">
                            <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" 
                                class="w-100 h-100 object-fit-cover" alt="{{ $auction->title }}">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-dark rounded-pill px-3 py-2 shadow-sm small">
                                    <i class="far fa-clock text-primary me-1"></i>
                                    {{ $auction->end_time->diffForHumans(null, true) }} left
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="h6 fw-bold mb-2">
                                <a href="{{ route('auctions.show', $auction->id) }}" class="text-dark text-decoration-none text-truncate d-block">
                                    {{ $auction->title }}
                                </a>
                            </h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block small">Current Bid</small>
                                    <span class="fw-bold text-primary h5 mb-0">₹{{ number_format($auction->current_price, 2) }}</span>
                                </div>
                                <a href="{{ route('auctions.show', $auction->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">Bid Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                        <i class="fas fa-gavel fs-1 text-muted opacity-25 mb-3"></i>
                        <h5 class="text-secondary">No active auctions at the moment.</h5>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

@push('styles')
<style>
    .auction-card {
        transition: transform 0.3s ease, shadow 0.3s ease;
    }
    .auction-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
    }
    .max-width-600 {
        max-width: 600px;
    }
</style>
@endpush

@endsection
