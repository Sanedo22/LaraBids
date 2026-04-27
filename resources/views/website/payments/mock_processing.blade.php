@extends('website.layouts.app')

@section('title', 'Processing Secure Payment...')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 p-5 text-center bg-white" id="payment-card">
                <!-- Step 1: Processing -->
                <div id="processing-state">
                    <div class="mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem; border-width: 0.35em;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <i class="fas fa-lock text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">Processing Payment</h3>
                    <p class="text-muted small mb-4">Please wait while we securely process your transaction of <strong>₹{{ number_format($amount, 2) }}</strong>. <br>Do not refresh this page.</p>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Form to submit -->
                <form action="{{ route('payment.mock.callback') }}" method="POST" id="mock-payment-form" class="d-none">
                    @csrf
                    <input type="hidden" name="txnid" value="{{ $txnid }}">
                    <input type="hidden" name="status" value="success">
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    body { background-color: #f8fafc; }
    #payment-card {
        animation: slideUpFade 0.5s ease-out forwards;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simulate a 3.5 second secure payment gateway delay
        setTimeout(function() {
            document.getElementById('mock-payment-form').submit();
        }, 3500);
    });
</script>
@endpush
@endsection
