@extends('website.layouts.app')

@section('title', 'Contact Support | LaraBids')

@section('content')

<!-- ================= HERO SECTION ================= -->
<section class="hero-section text-white d-flex align-items-center">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">

            <div class="col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
                <span class="badge bg-white text-primary fw-semibold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    SUPPORT CENTER
                </span>

                <h1 class="display-4 fw-bold mb-3">
                    Contact <span class="text-white">LaraBids</span>
                </h1>

                <p class="lead opacity-75 pe-lg-5">
                    Need help with auctions, bids, or account-related queries?
                    Our support team is here to assist you every step of the way.
                </p>

                <a href="#contact-content"
                   class="btn btn-gold btn-lg px-5 py-3 rounded-pill shadow mt-3">
                    Contact Support
                </a>
            </div>

            <div class="col-lg-5 text-center">
                <div class="hero-illustration-wrapper">
                    <!-- Glow blob for depth -->
                    <div class="hero-glow-blob"></div>
                    
                    <img src="{{ asset('assets/images/banner-5.png') }}"
                         class="img-fluid"
                         alt="LaraBids Support"
                         style="max-height: 380px; filter: drop-shadow(0 20px 40px rgba(0,0,0,.35));">
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================= CONTACT CONTENT ================= -->
<section id="contact-content" class="py-5" style="background-color: #f8f9fc;">
    <div class="container py-lg-5">
        
        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="fas fa-times-circle me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="row g-5">
            
            <!-- LEFT COLUMN: Contact Information -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <div class="card-body p-5 position-relative">
                        <!-- Background Pattern -->
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.3;"></div>
                        
                        <div class="position-relative z-1">
                            <h3 class="fw-bold mb-3 text-white">Contact Information</h3>
                            <p class="mb-5 opacity-75">Fill up the form and our team will get back to you within 24 hours.</p>

                            <div class="d-flex align-items-start mb-4 contact-detail-item">
                                <div class="icon-box-white me-4">
                                    <i class="fas fa-phone-alt text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 opacity-90">Phone Number</h6>
                                    <a href="tel:+919876543210" class="text-white text-decoration-none d-block">+91 98765 43210</a>
                                    <span class="small opacity-50">Mon-Fri 9am-6pm</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-4 contact-detail-item">
                                <div class="icon-box-white me-4">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 opacity-90">Email Address</h6>
                                    <a href="mailto:support@larabids.com" class="text-white text-decoration-none d-block">support@larabids.com</a>
                                    <span class="small opacity-50">Online Support 24/7</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-5 contact-detail-item">
                                <div class="icon-box-white me-4">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 opacity-90">Office Address</h6>
                                    <p class="mb-0 opacity-75 small">123, Business Park, Sector 18<br>Gurugram, Haryana - 122001, India</p>
                                </div>
                            </div>

                            <!-- Additional Info Boxes -->
                            <div class="pt-4 border-top border-white border-opacity-25">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="info-box-small">
                                            <i class="fas fa-clock mb-2 fs-5"></i>
                                            <h6 class="small fw-bold mb-1">Business Hours</h6>
                                            <p class="small opacity-75 mb-0">Mon-Fri<br>9:00 AM - 6:00 PM</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box-small">
                                            <i class="fas fa-headset mb-2 fs-5"></i>
                                            <h6 class="small fw-bold mb-1">Response Time</h6>
                                            <p class="small opacity-75 mb-0">Within<br>24 Hours</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg rounded-4 h-100 bg-white" data-aos="fade-left">
                    <div class="card-body p-4 p-md-5">

                        <h2 class="fw-bold mb-2 text-dark">Send us a Message</h2>
                        <p class="text-muted mb-4">
                            Have a specific inquiry? Please fill out the form below.
                        </p>

                        <form action="{{ route('contact.store') }}" method="POST" id="contactForm" novalidate>
                            @csrf

                            <!-- Name -->
                            <div class="mb-4">
                                <label class="form-label">Full Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control form-control-lg @error('name') is-invalid @enderror"
                                       value="{{ old('name', auth()->check() ? auth()->user()->name : '') }}"
                                       placeholder="Enter your full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- Subject -->
                            <div class="mb-4">
                                <label class="form-label">Subject</label>
                                <input type="text"
                                       name="subject"
                                       class="form-control form-control-lg @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}"
                                       placeholder="e.g. Auction approval issue">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label class="form-label">Message</label>
                                <textarea name="message"
                                          rows="5"
                                          class="form-control form-control-lg @error('message') is-invalid @enderror"
                                          placeholder="Describe your issue in detail...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <button type="submit"
                                    id="contactSubmitBtn"
                                    class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Client-Side Form Validation
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('contactSubmitBtn');

    // Validation function
    function validateForm(e) {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback:not([data-server-error])').forEach(el => el.remove());

        // Name validation
        const nameInput = document.querySelector('input[name="name"]');
        if (!nameInput.value.trim()) {
            showError(nameInput, 'Name is required');
            isValid = false;
        } else if (nameInput.value.trim().length < 3) {
            showError(nameInput, 'Name must be at least 3 characters');
            isValid = false;
        }

        // Email validation (for guests only)
        const emailInput = document.querySelector('input[name="email"]:not([type="hidden"])');
        if (emailInput) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim()) {
                showError(emailInput, 'Email is required');
                isValid = false;
            } else if (!emailRegex.test(emailInput.value)) {
                showError(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
        }

        // Subject validation
        const subjectInput = document.querySelector('input[name="subject"]');
        if (!subjectInput.value.trim()) {
            showError(subjectInput, 'Subject is required');
            isValid = false;
        } else if (subjectInput.value.trim().length < 5) {
            showError(subjectInput, 'Subject must be at least 5 characters');
            isValid = false;
        }

        // Message validation
        const messageInput = document.querySelector('textarea[name="message"]');
        if (!messageInput.value.trim()) {
            showError(messageInput, 'Message is required');
            isValid = false;
        } else if (messageInput.value.trim().length < 10) {
            showError(messageInput, 'Message must be at least 10 characters');
            isValid = false;
        }

        return isValid;
    }

    function showError(input, message) {
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    }

    // Guest user login alert
    submitBtn.addEventListener('click', function (e) {
        @guest
            e.preventDefault();
            
            // Validate form first
            if (!validateForm(e)) {
                return;
            }

            Swal.fire({
                title: 'Authentication Required',
                text: "Please login to submit your support request.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Login Now',
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('login') }}";
                }
            });
        @else
            // For logged-in users, validate before submit
            if (!validateForm(e)) {
                e.preventDefault();
            }
        @endguest
    });
</script>
@endpush

<style>
/* ============================================
   PREMIUM CONTACT PAGE STYLES
   ============================================ */

/* Contact Info Card Icons */
.icon-box-white {
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.contact-detail-item:hover .icon-box-white {
    transform: scale(1.1);
}

/* Social Buttons */
.social-btn-white {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-btn-white:hover {
    background: white;
    color: #4e73df;
    transform: translateY(-3px);
}

/* Info Boxes in Contact Card */
.info-box-small {
    text-align: center;
    padding: 1rem 0.5rem;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.info-box-small:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-3px);
}

/* Form Controls */
#contact-content .form-control {
    background-color: #f8f9fc;
    border: 2px solid #eaecf4;
    font-size: 0.95rem;
    padding: 1rem 1.2rem;
    color: #6e707e;
    transition: all 0.3s ease;
}

#contact-content .form-control:focus {
    background-color: #fff;
    border-color: #4e73df; /* Blue border */
    box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25); /* Blue shadow */
    color: #6e707e; /* Keep text neutral */
}

/* Validation Error State Override */
#contact-content .form-control.is-invalid,
#contact-content .form-control.is-invalid:focus {
    border-color: #dc3545; /* Standard Professional Red */
    box-shadow: none; /* Clean look without colored shadow */
    color: #495057;
    background-image: none;
}

#contact-content .form-label {
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Submit Button */
#contact-content .btn-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border: none;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
}

#contact-content .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(78, 115, 223, 0.4);
}

#contact-content .btn-primary:active {
    transform: translateY(-1px);
}

</style>
