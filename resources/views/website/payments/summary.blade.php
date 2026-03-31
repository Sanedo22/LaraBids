@extends('website.layouts.dashboard')

@section('title', 'Payment Summary | LaraBids')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Order Header -->
            <div class="text-start mb-4">
                <h4 class="fw-bold text-dark mb-1">Confirm and Pay</h4>
                <p class="text-muted small">Please review your order details below before proceeding.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <!-- Auction Details -->
                <div class="p-4 p-md-5 border-bottom bg-white">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <img src="{{ $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120' }}" 
                                 alt="{{ $auction->title }}" class="rounded-3 border" width="70" height="70" style="object-fit: cover;">
                        </div>
                        <div class="ms-3">
                            <span class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 d-block mb-1">Winning Bid For:</span>
                            <h5 class="fw-bold text-dark mb-0">{{ $auction->title }}</h5>
                            <span class="text-primary small fw-medium">{{ $auction->category->name ?? 'General' }}</span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-sm-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-2">Seller Information</h6>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border: 1px solid #eee;">
                                    <i class="fas fa-user-check text-muted" style="font-size: 0.8rem;"></i>
                                </div>
                                <div class="ms-2">
                                    <p class="mb-0 fw-bold small text-dark">{{ $auction->user->name }}</p>
                                    <p class="mb-0 text-muted extra-small">{{ $auction->user->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-2">Auction Details</h6>
                            <p class="mb-0 text-dark small">ID: <span class="fw-bold">#{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }}</span></p>
                            <p class="mb-0 text-muted extra-small">Ended on: {{ $auction->end_time?->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="p-4 p-md-5 bg-white">
                    <h6 class="text-dark small fw-bold mb-4">Price Breakdown</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Winning Bid Amount</span>
                        <span class="text-dark fw-bold small">₹{{ number_format($winningBid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                        <div class="d-flex flex-column">
                            <span class="text-secondary small">Platform Commission (5% Deduced)</span>
                            <span class="text-muted extra-small italic">Commission is deducted from the final sale amount</span>
                        </div>
                        <span class="text-danger fw-bold small">- ₹{{ number_format($commission, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mt-3 mb-1">
                        <span class="text-secondary small">Proceeds for Seller</span>
                        <span class="text-muted fw-bold small">₹{{ number_format($winningBid - $commission, 2) }}</span>
                    </div>
                    
                    <div class="bg-light rounded-3 p-3 mt-4 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Total Payable</h5>
                                <span class="text-muted extra-small">Securely via PayU Payment Gateway</span>
                            </div>
                            <h3 class="fw-bold text-success mb-0">₹{{ number_format($totalAmount, 2) }}</h3>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="mt-5 text-center">
                        <a href="{{ route('payment.payu.checkout', $auction->id) }}" class="btn btn-payment-normal w-100 py-3 rounded-3 fw-bold text-uppercase letter-spacing-1">
                            Proceed to Secure Payment
                        </a>
                        <a href="{{ route('user.winning-items') }}" class="btn btn-link text-muted small mt-3 text-decoration-none">
                            <i class="fas fa-chevron-left me-1" style="font-size: 0.7rem;"></i> Back to My Items
                        </a>
                    </div>
                </div>

                
                <div class="card-footer bg-white py-3 border-0 text-center opacity-75">
                    <div class="d-flex justify-content-center gap-4 align-items-center">
                        <img src="https://static.payu.in/images/payu-logo.png" alt="PayU" height="15" style="filter: grayscale(100%);">
                        <span class="text-muted extra-small">|<i class="fas fa-shield-alt ms-4 me-1 text-primary"></i> 256-Bit Secure Payment</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 px-2">
                <p class="text-muted extra-small italic lh-sm">
                    <strong>Note:</strong> You are the confirmed winner of this auction. Please complete the payment to finalize the transaction. Once paid, the seller will be notified to start the shipping process.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 0.05em; }
    .extra-small { font-size: 0.75rem; }
    .italic { font-style: italic; }
    
    .btn-payment-normal {
        background: #4e73df;
        color: #fff;
        border: none;
        box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
        transition: all 0.2s;
    }
    
    .btn-payment-normal:hover {
        background: #2e59d9;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(78, 115, 223, 0.25);
    }
    
    .btn-payment-normal:active {
        transform: translateY(0);
    }
    
    .bg-light-subtle { background-color: #f8fafc !important; }
</style>
@endsection

