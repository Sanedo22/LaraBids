@extends('website.layouts.app')

@section('title', 'Login | LaraBids')

@section('content')

<!-- Auth Hero Section -->
<section class="hero-section text-white d-flex align-items-center" style="min-height: 50vh;">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    🔐 SECURE LOGIN
                </span>
                <h1 class="display-3 fw-bold mb-3">Welcome <span class="text-white">Back!</span></h1>
                <p class="lead opacity-75">
                    Sign in to access your account and continue bidding on exclusive items.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Login Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card card-elite border-0 shadow-lg p-4 p-md-5" data-aos="fade-up">
                    
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-info border-0 mb-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold text-dark small text-uppercase">Email Address</label>
                            <input type="email" 
                                   class="form-control form-control-lg bg-light border-0 shadow-none @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email"
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold text-dark small text-uppercase">Password</label>
                            <input type="password" 
                                   class="form-control form-control-lg bg-light border-0 shadow-none @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password">
                            @error('password')
                                <div class="invalid-feedback" data-server-error>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                                <label class="form-check-label small text-secondary" for="remember_me">
                                    Remember Me
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small text-primary fw-bold">Forgot Password?</a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow fw-bold mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                        </button>

                        <!-- Register Link -->
                        <div class="text-center">
                            <span class="text-secondary small">Don't have an account? </span>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-primary fw-bold small">Create Account</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/form-validation.js') }}"></script>
@endpush
