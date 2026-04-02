@extends('website.layouts.dashboard')

@section('title', 'Profile Settings | LaraBids')

@push('styles')
<style>
    .card-profile {
        border: 1px solid rgba(0,0,0,.05);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,.03);
        margin-bottom: 24px;
        background-color: #fff;
    }
    .card-profile-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.05);
        border-radius: 12px 12px 0 0;
        padding: 16px 24px;
        font-weight: 600;
        color: #333;
    }
    .card-profile-body {
        padding: 24px;
    }
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 15px;
    }
    .avatar-image {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    .avatar-upload-trigger {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: #4e73df;
        color: white;
        border: 2px solid white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .avatar-upload-trigger:hover {
        background-color: #0b5ed7;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
    }
    .form-control {
        border-radius: 0.35rem;
        border: 1px solid #d1d3e2;
        padding: 0.4rem 0.75rem;
    }
    .form-control:focus {
        border-color: #bac8f3;
        box-shadow: none;
        outline: 0;
    }
    .input-group-text {
        background-color: #eaecf4;
        border: 1px solid #d1d3e2;
        color: #6e707e;
        border-radius: 0.35rem 0 0 0.35rem;
        border-right: 0;
    }
    input.form-control {
        border-radius: 0 0.35rem 0.35rem 0;
    }
    input.form-control:not(.input-group > input) {
        border-radius: 0.35rem;
    }
    .btn-primary {
        padding: 0.5rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-primary:active {
        transform: translateY(1px);
    }
    .btn-outline-primary, .btn-outline-danger {
        border-radius: 6px !important;
        font-weight: 500;
    }
    .stat-item {
        text-align: center;
        padding: 12px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #4e73df;
        margin-bottom: 2px;
    }
    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">Profile Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Profile Summary & Stats -->
    <div class="col-lg-4">
        <!-- Avatar Card -->
        <div class="card-profile text-center pt-4 pb-4">
            <div class="avatar-wrapper">
                <img src="{{ auth()->user()->avatar_url }}" class="avatar-image" alt="User Avatar" id="avatar-preview">
                <label for="avatar-input" class="avatar-upload-trigger" title="Upload new avatar">
                    <i class="fas fa-camera fa-sm"></i>
                </label>
            </div>
            <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
            <p class="text-muted small mb-3">{{ '@' . auth()->user()->username }}</p>

            <div class="d-flex justify-content-center gap-2 mb-4 px-4">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100" onclick="document.getElementById('avatar-input').click()">
                    Change Photo
                </button>
                @if(auth()->user()->avatar)
                    <form action="{{ route('profile.avatar.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to remove your photo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Remove Photo">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                @endif
            </div>

            <div class="row px-4 g-2">
                <div class="col-6">
                    <div class="stat-item">
                        <div class="stat-value">{{ auth()->user()->bids()->count() }}</div>
                        <div class="stat-label">Total Bids</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-item">
                        <div class="stat-value text-success">{{ auth()->user()->getWonAuctionsCount() }}</div>
                        <div class="stat-label">Wins</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Status Card -->
        <div class="card-profile">
            <div class="card-profile-header">
                <i class="fas fa-shield-alt text-primary me-2"></i> Identity Verification
            </div>
            <div class="card-profile-body">
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <div class="d-flex align-items-center">
                        <div class="me-3 fs-3 text-info"><i class="fas fa-user-shield"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1 text-info">Administrator</h6>
                            <p class="mb-0 small text-muted">System actions are fully authorized.</p>
                        </div>
                    </div>
                @else
                    @php $kyc = auth()->user()->kyc; @endphp
                    
                    @if($kyc && $kyc->status === 'approved')
                        <div class="d-flex align-items-center">
                            <div class="me-3 fs-3 text-success"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 text-success">Verified User</h6>
                                <p class="mb-0 small text-muted">Your identity has been verified.</p>
                            </div>
                        </div>
                    @elseif($kyc && $kyc->status === 'pending')
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center">
                                <div class="me-3 fs-3 text-warning"><i class="fas fa-clock"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-warning">Review Pending</h6>
                                    <p class="mb-0 small text-muted">Submitted on {{ $kyc->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="bg-warning-subtle p-2 rounded border border-warning-subtle small text-dark">
                                <i class="fas fa-info-circle me-1"></i> Documents are under review. This usually takes 24-48 hours.
                            </div>
                        </div>
                    @elseif($kyc && $kyc->status === 'rejected')
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 fs-3 text-danger"><i class="fas fa-times-circle"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger">Verification Failed</h6>
                                    <p class="mb-0 small text-muted">Your application was rejected.</p>
                                </div>
                            </div>
                            @if($kyc->admin_note)
                                <div class="bg-danger-subtle p-2 px-3 rounded border border-danger-subtle small text-danger">
                                    <div class="fw-bold text-uppercase" style="font-size: 0.65rem;">Reason for Rejection:</div>
                                    <div>{{ $kyc->admin_note }}</div>
                                </div>
                            @endif
                            <a href="{{ route('user.kyc.form') }}" class="btn btn-sm btn-danger w-100 rounded-pill">Re-submit Documents</a>
                        </div>
                    @else
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3 fs-3 text-secondary"><i class="fas fa-id-card"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Not Verified</h6>
                                <p class="mb-0 small text-muted">Verify your identity to place bids.</p>
                            </div>
                        </div>
                        <a href="{{ route('user.kyc.form') }}" class="btn btn-sm btn-primary w-100">Start Verification</a>
                    @endif
                @endif
            </div>
        </div>
        <!-- Reputation & Strikes Card -->
        <div class="card-profile">
            <div class="card-profile-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-circle text-danger me-2"></i> Reputation & Strikes</span>
                <span class="badge bg-danger rounded-pill px-2">{{ auth()->user()->unpaid_strikes_count }} Active</span>
            </div>
            <div class="card-profile-body p-0">
                @if(auth()->user()->strikes->count() > 0)
                    <div class="list-group list-group-flush rounded-bottom-4">
                        @foreach(auth()->user()->strikes as $strike)
                            <div class="list-group-item p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge {{ $strike->status === 'active' ? 'bg-danger' : ($strike->status === 'appealed' ? 'bg-warning text-dark' : 'bg-success') }} extra-small text-uppercase">
                                        {{ $strike->status }}
                                    </span>
                                    <small class="text-muted extra-small">{{ $strike->created_at->format('M d, Y') }}</small>
                                </div>
                                <h6 class="fw-bold text-dark small mb-1">
                                    <i class="fas fa-gavel me-1 opacity-50"></i> {{ $strike->auction->title ?? 'Deleted Auction' }}
                                </h6>
                                <p class="mb-2 text-muted extra-small italic">"{{ $strike->reason }}"</p>
                                
                                @if($strike->status === 'active')
                                    <button type="button" class="btn btn-outline-warning btn-sm extra-small py-1 w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#appealModal{{ $strike->id }}">
                                        <i class="fas fa-balance-scale me-1"></i> Appeal Strike
                                    </button>

                                    <!-- Appeal Modal -->
                                    <div class="modal fade" id="appealModal{{ $strike->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-bottom-0 pt-4 px-4">
                                                    <h5 class="modal-title fw-bold"><i class="fas fa-balance-scale text-warning me-2"></i> Appeal Strike</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('user.strikes.appeal', $strike->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body px-4 pb-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-uppercase">Reason for Appeal</label>
                                                            <textarea class="form-control" name="appeal_reason" rows="4" placeholder="Explain why this strike is unfair (min 20 characters)..." required minlength="20"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 px-4 pb-4">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Submit Appeal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($strike->status === 'appealed')
                                    <div class="bg-warning-subtle p-2 rounded small text-dark extra-small">
                                        <i class="fas fa-clock me-1"></i> Under Review: "{{ Str::limit($strike->appeal_reason, 100) }}"
                                    </div>
                                @elseif($strike->status === 'dismissed')
                                    <div class="bg-success-subtle p-2 rounded small text-success extra-small">
                                        <i class="fas fa-check-circle me-1"></i> Strike Dismissed by Admin.
                                        @if($strike->admin_note)
                                            <div class="mt-1 fw-bold italic">Note: {{ $strike->admin_note }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center">
                        <div class="mb-2 text-success fs-3"><i class="fas fa-shield-virus"></i></div>
                        <h6 class="fw-bold small text-dark mb-1">Clean Record</h6>
                        <p class="text-muted extra-small mb-0">You have no strikes on your account. Good job!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Settings Forms -->
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card-profile">
            <div class="card-profile-header">
                <i class="fas fa-user-edit text-primary me-2"></i> Personal Details
            </div>
            <div class="card-profile-body">
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Profile details updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('status') === 'avatar-deleted')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Profile photo removed successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <input type="file" id="avatar-input" name="avatar" accept="image/*" class="d-none">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                                value="{{ old('name', auth()->user()->name) }}" placeholder="Enter your full name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" 
                                    value="{{ old('username', auth()->user()->username) }}" placeholder="your_handle" required>
                            </div>
                            @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control bg-light text-muted" id="email" 
                                    value="{{ auth()->user()->email }}" disabled readonly>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Cannot be changed</small>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" 
                                    value="{{ old('phone', auth()->user()->phone) }}" placeholder="Enter phone number">
                            </div>
                            @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="location" class="form-label">Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" 
                                    value="{{ old('location', auth()->user()->location) }}" placeholder="City, Country">
                            </div>
                            @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="bio" class="form-label">Bio / About Me</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3" 
                                placeholder="Write something about yourself...">{{ old('bio', auth()->user()->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security / Password -->
        @if(is_null(auth()->user()->google_id))
        <div class="card-profile">
            <div class="card-profile-header">
                <i class="fas fa-lock text-primary me-2"></i> Security Settings
            </div>
            <div class="card-profile-body">
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Password has been changed successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="update_password_current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                id="update_password_current_password" name="current_password" placeholder="Enter current password" autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="update_password_password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                id="update_password_password" name="password" placeholder="Enter new password" autocomplete="new-password">
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                id="update_password_password_confirmation" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('avatar-input').onchange = function (evt) {
        const [file] = this.files
        if (file) {
            document.getElementById('avatar-preview').src = URL.createObjectURL(file)
        }
    }
</script>
@endpush



