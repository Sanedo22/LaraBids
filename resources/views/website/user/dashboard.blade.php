@extends('website.layouts.dashboard')

@section('title', 'Dashboard | LaraBids')

@section('content')

<style>
    .dashboard-container {
        padding-top: 1rem;
    }
    .user-welcome-section {
        margin-bottom: 2.5rem;
    }
    .quick-stat-box {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #edf2f9;
        transition: all 0.2s;
    }
    .quick-stat-box:hover {
        border-color: #4e73df;
        transform: translateY(-3px);
    }
    .activity-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #edf2f9;
        overflow: hidden;
    }
    .list-item-hover {
        padding: 1rem 1.25rem;
        transition: background 0.2s;
        border-bottom: 1px solid #f1f4f8;
    }
    .list-item-hover:hover {
        background: #f8fafc;
    }
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .btn-create {
        background: #4e73df;
        color: #fff;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border: none;
        transition: all 0.2s;
    }
    .btn-create:hover {
        background: #2e59d9;
        color: #fff;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25);
    }
</style>

<div class="dashboard-container">
    <!-- Header Section -->
    <div class="user-welcome-section d-flex flex-column flex-md-row align-items-md-center justify-content-between g-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Hello, {{ auth()->user()->username }}</h2>
            <p class="text-muted mb-0">Here's a quick look at your current auction activity.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('auctions.create') }}" class="btn-create">
                <i class="fas fa-plus me-2"></i> New Auction
            </a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="quick-stat-box shadow-sm d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Active Bids</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $stats['active_bids'] }}</h3>
                </div>
                <i class="fas fa-gavel text-primary opacity-50 fs-4"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-stat-box shadow-sm d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Items Won</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $stats['total_wins'] }}</h3>
                </div>
                <i class="fas fa-award text-success opacity-50 fs-4"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-stat-box shadow-sm d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Watchlist</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $stats['watchlist_count'] }}</h3>
                </div>
                <i class="fas fa-heart text-danger opacity-50 fs-4"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-stat-box shadow-sm d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">KYC Status</span>
                    <h5 class="fw-bold text-success mb-0">Verified <i class="fas fa-check-circle small"></i></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Live Monitoring Section -->
        <div class="col-lg-7">
            <div class="activity-card shadow-sm mb-4">
                <div class="px-4 py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">Live Participation</h6>
                    <a href="{{ route('user.my-bids') }}" class="text-primary small text-decoration-none fw-bold">Detailed View</a>
                </div>
                
                @if($recent_bids->count() > 0)
                    @foreach($recent_bids as $bid)
                    <div class="list-item-hover d-flex align-items-center">
                        <img src="{{ $bid->auction->image_url }}" class="rounded me-4 border" width="56" height="56" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-1 lh-sm">{{ $bid->auction->title }}</h6>
                            <div class="d-flex align-items-center">
                                <span class="status-indicator bg-success"></span>
                                <span class="text-muted small">Current: <strong class="text-dark">₹{{ number_format($bid->auction->current_price, 2) }}</strong></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block mb-1">Your Bid</span>
                            <span class="badge bg-light text-primary border rounded-pill px-3">₹{{ number_format($bid->amount, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="p-5 text-center">
                        <p class="text-muted mb-0">No active bids at the moment.</p>
                        <a href="{{ route('auctions.index') }}" class="btn btn-link text-primary mt-2">Explore Live Auctions</a>
                    </div>
                @endif
            </div>

            <!-- Recently Won -->
            <div class="activity-card shadow-sm">
                <div class="px-4 py-3 bg-white border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Recent Successes</h6>
                </div>
                @forelse($recent_wins as $win)
                <div class="list-item-hover d-flex align-items-center">
                    <div class="me-4 text-center">
                        <i class="fas fa-trophy text-warning fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">{{ $win->title }}</h6>
                        <span class="text-muted small">Sold for ₹{{ number_format($win->current_price, 2) }}</span>
                    </div>
                    <a href="{{ route('auctions.show', $win->id) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">View Item</a>
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    You haven't won any auctions recently.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar Section -->
        <div class="col-lg-5">
            <!-- Notifications Feed -->
            <div class="activity-card shadow-sm mb-4">
                <div class="px-4 py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">Recent Alerts</h6>
                    <a href="{{ route('user.notifications.index') }}" class="text-muted small text-decoration-none">Clear All</a>
                </div>
                <div class="p-0">
                    @forelse($recent_notifications as $notification)
                    <div class="list-item-hover border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark small">{{ $notification->data['title'] ?? 'System Update' }}</span>
                            <span class="text-muted extra-small" style="font-size: 0.65rem;">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ Str::limit($notification->data['message'] ?? '', 80) }}</p>
                    </div>
                    @empty
                    <div class="p-5 text-center">
                        <i class="far fa-bell text-muted opacity-25 mb-2 fs-3 d-block"></i>
                        <span class="text-muted small">All caught up!</span>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Support & Profile Info -->
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                        <div class="activity-card p-3 text-center shadow-sm h-100">
                            <i class="fas fa-user-circle text-info fs-4 mb-2"></i>
                            <h6 class="text-dark small fw-bold mb-0">Edit Profile</h6>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('contact') }}" class="text-decoration-none">
                        <div class="activity-card p-3 text-center shadow-sm h-100">
                            <i class="fas fa-question-circle text-warning fs-4 mb-2"></i>
                            <h6 class="text-dark small fw-bold mb-0">Get Support</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
