@extends('website.layouts.app')

@section('title', 'Login | LaraBids')

@section('content')

<div class="main-auth-container d-flex align-items-center justify-content-center py-5" style="min-height: 80vh; background-color: #ffffff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="form-box">
                    <h2 class="text-center fw-bold mb-4">Login</h2>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success border-0 small mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                                <label class="form-check-label small" for="remember_me">
                                    Remember Me
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot Password?</a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-4">
                            Login
                        </button>

                        <!-- Register Link -->
                        <div class="text-center mt-3">
                            <span class="text-muted small">Don't have an account? </span>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="fw-bold small text-decoration-none">Register Here</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .form-box {
        background: #ffffff;
        padding: 40px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/form-validation.js') }}"></script>
@if (session('kicked_out'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Logged Out',
                text: "{{ session('kicked_out') }}",
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif
@endpush
