@extends('website.layouts.dashboard')

@section('title', 'Payment Summary | LaraBids')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="h2 fw-bold text-dark">Confirm Your Payment</h1>
                <p class="text-muted">Review the auction details and total amount before proceeding to the secure payment gateway.</p>
            </div>

            <!-- Summary Card -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark py-4 text-white text-center border-0">
                    <h5 class="mb-0 text-uppercase letter-spacing-1">Order Summary</h5>
                    <small class="opacity-75">Auction ID: #{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }}</small>
                </div>
                <div class="card-body p-4 p-md-5">
                    <!-- Auction Item Info -->
                    <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                        <div class="flex-shrink-0">
                            <img src="{{ $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120' }}" 
                                 alt="{{ $auction->title }}" 
                                 class="rounded-3 border shadow-sm"
                                 width="80" height="80" style="object-fit: cover;">
                        </div>
                        <div class="ms-4">
                            <h4 class="fw-bold text-dark mb-1">{{ $auction->title }}</h4>
                            <span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                                <i class="fas fa-tag me-2"></i>{{ $auction->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                    </div>

                    <!-- Seller Info -->
                    <div class="mb-5">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 letter-spacing-1">Seller Information</h6>
                        <div class="bg-light rounded-3 p-3 d-flex align-items-center">
                            <div class="avatar-circle bg-white text-primary fw-bold shadow-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; border: 1px solid #dee2e6;">
                                {{ strtoupper(substr($auction->user->name, 0, 1)) }}
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 fw-bold text-dark">{{ $auction->user->name }}</p>
                                <p class="mb-0 text-muted small">{{ $auction->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="mb-5">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 letter-spacing-1">Price Details</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Winning Bid Amount</span>
                            <span class="fw-bold text-dark">₹{{ number_format($winningBid, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Platform Commission (5% deducted from final prize)</span>
                            <span class="text-danger">- ₹{{ number_format($commission, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Proceeds for Seller</span>
                            <span class="text-dark">₹{{ number_format($winningBid - $commission, 2) }}</span>
                        </div>
                        <div class="border-top my-3"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="h5 fw-bold text-dark mb-0 d-block">Total Payable Amount</span>
                                <small class="text-muted">Inclusive of all platform fees</small>
                            </div>
                            <span class="h3 fw-bold text-success">₹{{ number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>

                    <!-- Proceed Button -->
                    <div class="d-grid gap-3">
                        <a href="{{ route('payment.payu.checkout', $auction->id) }}" class="btn btn-primary btn-lg rounded-pill shadow fw-bold py-3" style="background: linear-gradient(135deg, #a88b77 0%, #7d6355 100%); border: none;">
                            <i class="fas fa-lock me-2"></i> Proceed to Secure Payment
                        </a>
                        <a href="{{ route('user.winning-items') }}" class="btn btn-link text-muted text-decoration-none small">
                            <i class="fas fa-arrow-left me-2"></i> Back to Won Items
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 border-0 text-center text-muted small">
                    <i class="fas fa-shield-alt me-1 text-success"></i> Secured by PayU Payment Gateway
                </div>
            </div>

            <!-- Note Section -->
            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-0">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle fs-4 text-info mt-1"></i>
                    </div>
                    <div class="ms-3">
                        <p class="mb-0 small text-dark">
                            <strong>Note:</strong> By clicking "Proceed to Secure Payment", you will be redirected to the PayU checkout page. Your payment information is encrypted and never stored on our servers.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 0.1rem; }
</style>
@endsection
