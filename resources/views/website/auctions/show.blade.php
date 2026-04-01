@extends('website.layouts.app')

@section('title', 'Auction Details | LaraBids')

@section('content')

@php
    $galleryImages = collect();
    $mainPath = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : null;
    
    if ($mainPath) $galleryImages->push($mainPath);
    
    foreach ($auction->images as $img) {
        $path = str_starts_with($img->image_path, 'http') ? $img->image_path : asset('storage/' . $img->image_path);
        if (!$galleryImages->contains($path)) {
            $galleryImages->push($path);
        }
    }
@endphp

@push('meta')
    @php
        $seoTitle = $auction->title . ' | LaraBids';
        $seoDescription = Str::limit(strip_tags($auction->description), 160);
        $seoImage = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : asset('assets/images/hero-premium.png');
        $currentPrice = '₹' . number_format($auction->current_price);
    @endphp
    <!-- Standard Meta Tags -->
    <meta name="description" content="{{ $seoDescription }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }} - Current Bid: {{ $currentPrice }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $seoTitle }}">
    <meta property="twitter:description" content="{{ $seoDescription }} - Current Bid: {{ $currentPrice }}">
    <meta property="twitter:image" content="{{ $seoImage }}">
@endpush

<!-- Auction Details -->
<section class="hibid-auction-section pt-4 pb-3 mt-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <!-- Left: Image Gallery -->
            <div class="col-lg-7" data-aos="fade-right">
                <!-- Main Image -->
                <div class="hibid-main-image-wrap mb-3">
                    @if($mainPath)
                        <img src="{{ $mainPath }}" class="hibid-main-img main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @else
                        <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                            class="hibid-main-img main-auction-image" alt="{{ $auction->title }}" onclick="openLightbox()">
                    @endif
                    <div class="hibid-zoom-hint">
                        <i class="fas fa-search-plus"></i>
                    </div>
                    <div class="zoom-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0 transition-all" onclick="openLightbox()" style="cursor:zoom-in;">
                        <i class="fas fa-search-plus fa-3x text-white"></i>
                    </div>
                </div>

                <!-- Gallery Strip -->
                <div class="hibid-gallery-box">
                    <div class="hibid-gallery-header">
                        <span class="hibid-gallery-label">
                            <i class="fas fa-th me-2"></i>PRODUCT GALLERY
                        </span>
                        <span class="hibid-photo-badge">{{ $galleryImages->count() }} Photos</span>
                    </div>

                    @if($galleryImages->count() > 0)
                        <div class="hibid-thumb-row">
                            @foreach($galleryImages as $index => $imgUrl)
                            <div class="hibid-thumb {{ $index === 0 ? 'active' : '' }}"
                                onclick="changeMainImage('{{ $imgUrl }}', this)">
                                <img src="{{ $imgUrl }}" alt="Photo {{ $index + 1 }}">
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="hibid-no-photos">
                            <i class="fas fa-camera-retro"></i>
                            <p>No additional photos available.</p>
                        </div>
                    @endif
                </div>

                <!-- Seller Info Card (below gallery) -->
                <div class="hibid-seller-card mt-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="hibid-seller-inner">
                        <a href="{{ route('sellers.show', $auction->user->id) }}">
                            <img src="{{ $auction->user->avatar_url }}"
                                class="hibid-seller-avatar" alt="Seller">
                        </a>
                        <div class="hibid-seller-info">
                            <a href="{{ route('sellers.show', $auction->user->id) }}" class="hibid-seller-name">@_{{ $auction->user->username }}</a>
                            <div class="hibid-seller-meta d-flex flex-wrap gap-2 mt-1 align-items-center">
                                <span class="hibid-verified m-0"><i class="fas fa-check-circle me-1"></i>VERIFIED</span>
                                <span class="hibid-rating m-0"><i class="fas fa-star me-1"></i>4.9</span>
                                @if($auction->user->location)
                                    <span class="badge bg-light text-secondary border fw-normal py-1"><i class="fas fa-map-marker-alt me-1 text-primary"></i>{{ $auction->user->location }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('sellers.show', $auction->user->id) }}" class="hibid-contact-btn">Contact</a>
                    </div>
                </div>
            </div>

            <!-- Right: Action Sidebar -->
            <div class="col-lg-5">
                <div class="sticky-lg-top" style="top: 120px; z-index: 1010;">

                    <!-- Main Bid Card -->
                    <div class="hibid-bid-card mb-3" data-aos="fade-left">

                        <!-- Title & Category -->
                        <h2 class="hibid-auction-title mb-1">{{ $auction->title }}</h2>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <a href="{{ route('auctions.index', ['category' => $auction->category->slug ?? '']) }}"
                               class="hibid-category-link">{{ $auction->category->name ?? 'Uncategorized' }}</a>
                            <span class="copy-id text-muted extra-small" style="font-size: 0.75rem;" onclick="copyToClipboard('{{ $auction->id }}', this)" title="Click to copy ID">ID: #{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1"></i></span>
                        </div>

                        <!-- Timer -->
                        @php
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($auction->end_time);
                            $start = \Carbon\Carbon::parse($auction->start_time);
                            $diff = $now->diff($end);
                            $isClosed = $now->greaterThan($end);
                            $isUpcoming = $now->lessThan($start);
                        @endphp

                        @if($isUpcoming)
                        <div class="alert alert-info alert-permanent border-0 shadow-sm mb-4 rounded-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-3 fs-4 text-primary"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Starting Soon</h6>
                                    <p class="mb-0 small text-muted">Bidding begins on {{ \Carbon\Carbon::parse($auction->start_time)->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        @elseif(!$isClosed)
                        <div class="hibid-timer timer-val mb-3"
                            data-days="{{ $diff->d }}" data-hours="{{ $diff->h }}" data-min="{{ $diff->i }}" data-sec="{{ $diff->s }}">
                            <div class="hibid-timer-unit">
                                <span class="hibid-timer-num" data-days>{{ sprintf('%02d', $diff->d) }}</span>
                                <span class="hibid-timer-label">DAYS</span>
                            </div>
                            <div class="hibid-timer-unit">
                                <span class="hibid-timer-num" data-hours>{{ sprintf('%02d', $diff->h) }}</span>
                                <span class="hibid-timer-label">HRS</span>
                            </div>
                            <div class="hibid-timer-unit">
                                <span class="hibid-timer-num" data-min>{{ sprintf('%02d', $diff->i) }}</span>
                                <span class="hibid-timer-label">MIN</span>
                            </div>
                            <div class="hibid-timer-unit">
                                <span class="hibid-timer-num" data-sec>{{ sprintf('%02d', $diff->s) }}</span>
                                <span class="hibid-timer-label">SEC</span>
                            </div>
                        </div>
                        @elseif($auction->status === 'cancelled')
                        <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ban me-3 fs-4 text-danger"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold text-danger">Auction Cancelled</h6>
                                    <p class="mb-0 small text-muted">This auction has been cancelled by the administrator.</p>
                                    @if($auction->cancellation_reason)
                                        <div class="mt-2 p-2 bg-white rounded border border-danger-subtle small font-italic">
                                            <strong>Reason:</strong> {{ $auction->cancellation_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="hibid-closed-badge mb-4">🔒 Auction Closed</div>
                        @endif

                        <div class="hibid-bid-info mb-3">
                            <div class="hibid-bid-row hibid-bid-row--current">
                                <span class="hibid-bid-label">
                                    Current Bid
                                    @if($auction->reserve_price)
                                        <div class="mt-1 fw-bold">
                                            @if($auction->isReserveMet())
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.65rem;"><i class="fas fa-check-circle me-1"></i>Reserve met</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.65rem;"><i class="fas fa-exclamation-circle me-1"></i>Reserve not met</span>
                                            @endif
                                        </div>
                                    @endif
                                </span>
                                <span class="hibid-bid-value hibid-bid-value--primary" id="current-bid-display">₹{{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            <div class="hibid-bid-row">
                                <span class="hibid-bid-label">Bids Placed</span>
                                <span class="hibid-bid-value hibid-bid-value--success"><span id="total-bids-count-card">{{ $auction->bids->count() }}</span> Bids</span>
                            </div>
                            <div class="hibid-bid-row">
                                <span class="hibid-bid-label">Min Increment</span>
                                <span class="hibid-bid-value">₹{{ number_format($auction->min_increment, 2) }}</span>
                            </div>
                            <div class="hibid-bid-row">
                                <span class="hibid-bid-label">Max Bid Increment</span>
                                <span class="hibid-bid-value hibid-bid-value--danger">₹{{ number_format(\App\Models\Auction::MAX_INCREMENT_ALLOWED, 2) }}</span>
                            </div>
                                           <!-- Bid Form / Login / Registration -->
                        @if(!$isClosed && $auction->status !== 'cancelled')
                        @auth
                            @if(auth()->id() === $auction->user_id)
                                <div class="alert alert-secondary alert-permanent border-0 shadow-sm mb-4 rounded-3 text-center">
                                    <i class="fas fa-user-tie me-2"></i> You are the seller of this auction.
                                </div>
                            @elseif(!auth()->user()->isKycApproved())
                                <div class="alert alert-warning alert-permanent border-0 shadow-sm mb-3 rounded-3 small">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2 text-warning fs-5"></i>
                                        <div>
                                            <strong>Identity Verification Required!</strong><br>
                                            You must <a href="{{ route('user.kyc.form') }}" class="fw-bold text-decoration-none">Complete KYC</a> to participate in this auction.
                                        </div>
                                    </div>
                                </div>
                            @elseif(!auth()->user()->isRegisteredFor($auction))
                                <div class="registration-prompt mb-4 p-4 border rounded-3 text-center bg-light">
                                    <div class="mb-3">
                                        <div class="registration-icon-wrap mb-2">
                                            <i class="fas fa-id-card-alt fa-2x text-primary opacity-50"></i>
                                        </div>
                                        <h5 class="fw-bold mb-1">Join this Auction</h5>
                                        <p class="text-muted small mb-0">Register now to unlock bidding permissions for this item.</p>
                                    </div>
                                    <form action="{{ route('user.auctions.register', $auction->id) }}" method="POST" class="registration-form">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm transition-all">
                                            Register for Auction <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </form>
                                    <div class="mt-3 small text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i> KYC Verified
                                    </div>
                                </div>
                            @else
                                <div id="bid-status-container" class="mb-2"></div>
                                 <!-- Winner Status Badge -->
                                @php
                                    $highestBid = $auction->bids()->first();
                                    $isWinning = ($highestBid && (int)$highestBid->user_id === (int)auth()->id());
                                    $userProxy = $auction->autoBids()->where('user_id', auth()->id())->where('active', true)->first();
                                @endphp

                                <div class="mb-3" id="winner-status-badge" data-user-id="{{ auth()->id() }}">
                                    @if($isWinning)
                                        <div class="badge bg-success py-2 px-3 rounded-pill w-100 hibid-pulse mb-2">
                                            <i class="fas fa-check-circle me-1"></i> You are the highest bidder!
                                        </div>
                                    @elseif($highestBid)
                                        <div class="badge bg-danger py-2 px-3 rounded-pill w-100 mb-2">
                                            <i class="fas fa-times-circle me-1"></i> Someone has outbid you!
                                        </div>
                                    @endif
                                </div>
                                    @if($userProxy)
                                        <div class="card border-primary-subtle bg-primary-subtle bg-opacity-10 border-dashed rounded-3 p-2 text-center mb-2">
                                            <div class="small text-primary fw-bold">
                                                <i class="fas fa-robot me-1"></i> Your Auto-Bid Limit: 
                                                <span class="fs-6">₹{{ number_format($userProxy->max_bid_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show mb-3 rounded-3" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show mb-3 rounded-3" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                @if(session('warning'))
                                    <div class="alert alert-warning alert-dismissible fade show mb-3 rounded-3" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if(!$isUpcoming)
                                <form action="{{ route('auctions.bid', $auction->id) }}" method="POST" class="mb-3" id="place-bid-form">
                                    @csrf
                                    
                                    <!-- Bid Tabs -->
                                    <ul class="nav nav-pills nav-justified hibid-bid-tabs mb-3" id="bidTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="quick-bid-tab" data-bs-toggle="pill" data-bs-target="#quick-bid" type="button" role="tab">Quick Bid</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="auto-bid-tab" data-bs-toggle="pill" data-bs-target="#auto-bid" type="button" role="tab">Auto Bid (Proxy)</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="bidTabContent">
                                        <!-- Quick Bid Pane -->
                                        <div class="tab-pane fade show active" id="quick-bid" role="tabpanel">
                                            <div class="hibid-increment-header">
                                                <label class="hibid-increment-label">YOUR BID INCREMENT (₹) <span class="text-muted ms-2 fw-normal">(<span id="total-bids-count-box">{{ $auction->bids->count() }}</span> bids so far)</span></label>
                                                <span class="hibid-min-badge">Min: ₹{{ number_format($auction->min_increment ?? 100.00, 2) }}</span>
                                            </div>

                                            <div class="hibid-input-wrap">
                                                <span class="hibid-input-prefix">₹</span>
                                                <input type="number" name="increment" id="bid-increment"
                                                    class="hibid-bid-input"
                                                    placeholder="{{ number_format($auction->min_increment ?? 100.00, 2, '.', '') }}"
                                                    value="{{ old('increment') }}"
                                                    min="{{ $auction->min_increment ?? 100.00 }}" step="0.01"
                                                    max="{{ \App\Models\Auction::MAX_INCREMENT_ALLOWED }}">
                                            </div>

                                            <!-- Shortcut Buttons -->
                                            <div class="hibid-shortcuts mb-2">
                                                @foreach([100, 300, 500, 700, 1000] as $amount)
                                                    <button type="button" class="hibid-shortcut-btn bid-shortcut" data-amount="{{ $amount }}">+₹{{ $amount }}</button>
                                                @endforeach
                                            </div>
                                            <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Enter the exact amount you wish to add on top of the current bid.</p>
                                        </div>

                                        <!-- Auto Bid Pane -->
                                        <div class="tab-pane fade" id="auto-bid" role="tabpanel">
                                            <div class="hibid-increment-header">
                                                <label class="hibid-increment-label">MAXIMUM BID AMOUNT (₹)</label>
                                                <span class="hibid-min-badge">Min: ₹{{ number_format($auction->current_price + ($auction->min_increment ?? 100.00), 2) }}</span>
                                            </div>

                                            <div class="hibid-input-wrap">
                                                <span class="hibid-input-prefix">₹</span>
                                                <input type="number" name="max_bid_amount" id="max-bid-amount"
                                                    class="hibid-bid-input"
                                                    placeholder="Enter your maximum limit"
                                                    value="{{ old('max_bid_amount') }}"
                                                    min="{{ $auction->current_price + ($auction->min_increment ?? 100.00) }}" step="0.01">
                                            </div>
                                            <p class="text-muted small mt-2 mb-0"><i class="fas fa-robot me-1"></i>Enter the absolute maximum price you are willing to pay. Our system will automatically bid the absolute minimum increments perfectly needed to keep you in the lead, only up to your limit.</p>
                                        </div>
                                    </div>

                                    <div id="bid-feedback-area" class="mb-2">
                                        @error('increment')
                                            <div class="alert alert-danger py-2 px-3 rounded-3 small border-0 shadow-sm">
                                                <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        @error('max_bid_amount')
                                            <div class="alert alert-danger py-2 px-3 rounded-3 small border-0 shadow-sm">
                                                <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="bid-feedback-total"></div>

                                    <button type="submit" class="hibid-place-bid-btn mt-2">
                                        Place Bid Now <i class="fas fa-gavel ms-2"></i>
                                    </button>
                                </form>
                                @else
                                <div class="alert alert-light border shadow-sm rounded-3 text-center mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>You are registered!</strong><br>
                                    <span class="small text-muted">We'll notify you when bidding opens.</span>
                                </div>
                                @endif
                            @endif
                        @else
                            <div class="hibid-login-prompt">
                                <i class="fas fa-info-circle me-2"></i>
                                Please <a href="{{ route('login') }}">login</a> to place a bid.
                            </div>
                        @endauth
                        @endif

                        <!-- Watchlist & Share -->
                        <div class="hibid-action-row">
                            @auth
                            <form action="{{ route('user.watchlist.toggle', $auction->id) }}" method="POST" class="flex-fill watchlist-toggle-form">
                                @csrf
                                <button type="submit" class="hibid-watchlist-btn">
                                    <i class="{{ $auction->watchlists->isNotEmpty() ? 'fas text-danger' : 'far' }} fa-heart me-2"></i>Watchlist
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="hibid-watchlist-btn">
                                <i class="far fa-heart me-2"></i>Watchlist
                            </a>
                            @endauth
                            <button class="hibid-share-btn" onclick="navigator.share ? navigator.share({title:'{{ $auction->title }}', url: window.location.href}) : null">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Accordion Details Section -->
        <div class="row mt-4">
            <div class="col-12" data-aos="fade-up">
                <div class="accordion premium-accordion" id="auctionAccordion">
                    
                    <!-- 1. Description -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo">
                                Description
                            </button>
                        </h2>
                        <div id="collapseInfo" class="accordion-collapse collapse show" data-bs-parent="#auctionAccordion">
                            <div class="accordion-body px-4 py-4 text-secondary lh-lg description-content">
                                {!! $auction->description !!}
                            </div>
                        </div>
                    </div>

                    <!-- 2. Auction Information -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAuction">
                                Auction Information
                            </button>
                        </h2>
                        <div id="collapseAuction" class="accordion-collapse collapse" data-bs-parent="#auctionAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 info-table">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light ps-4 py-3" style="width: 200px;">Item Name</th>
                                                <td class="ps-4 py-3 fw-medium">{{ $auction->title }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Auction ID</th>
                                                <td class="ps-4 py-3 fw-medium">
                                                    <span class="copy-id" onclick="copyToClipboard('{{ $auction->id }}', this)" title="Click to copy ID">#{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1 text-primary"></i></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Auctioneer</th>
                                                <td class="ps-4 py-3 fw-medium">@_{{ $auction->user->username }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Category</th>
                                                <td class="ps-4 py-3 fw-medium">{{ $auction->category->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Duration</th>
                                                <td class="ps-4 py-3 fw-medium">
                                                    {{ \Carbon\Carbon::parse($auction->start_time)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($auction->end_time)->format('M d, Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Closing Time</th>
                                                <td class="ps-4 py-3 fw-medium" id="auction-end-time-display">{{ \Carbon\Carbon::parse($auction->end_time)->format('F d, Y \a\t g:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Location</th>
                                                <td class="ps-4 py-3 fw-medium">
                                                    @if($auction->location)
                                                        {{ $auction->location }}
                                                    @elseif($auction->user->location)
                                                        {{ $auction->user->location }}
                                                    @else
                                                        Online Auction - Ships Worldwide
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light ps-4 py-3">Bid Currency</th>
                                                <td class="ps-4 py-3 fw-medium">INR (₹)</td>
                                            </tr>
                                            @if($auction->specifications)
                                                @foreach($auction->specifications as $key => $value)
                                                    @if($value)
                                                    <tr>
                                                        <th class="bg-light ps-4 py-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                        <td class="ps-4 py-3 fw-medium">{{ $value }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Terms and Conditions -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerms">
                                Terms and Conditions
                            </button>
                        </h2>
                        <div id="collapseTerms" class="accordion-collapse collapse" data-bs-parent="#auctionAccordion">
                            <div class="accordion-body px-4 py-4 text-secondary">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> All bids are final and cannot be retracted.</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Items are sold "as is, where is" without warranties.</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Successful bidders will receive an invoice via email after the auction closes.</li>
                                    <li><i class="fas fa-check-circle text-primary me-2"></i> Full payment is required within 48 hours of auction closing.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Bid Increments -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBid">
                                Bid Increments
                            </button>
                        </h2>
                        <div id="collapseBid" class="accordion-collapse collapse" data-bs-parent="#auctionAccordion">
                            <div class="accordion-body px-4 py-4 text-secondary">
                                <p class="mb-3">Our bidding system follows strict increment rules to ensure fair play:</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 text-center">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Minimum Increment</th>
                                                <th>Maximum Allowable Jump</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-primary">₹{{ number_format($auction->min_increment, 2) }}</td>
                                                <td class="fw-bold text-danger">₹{{ number_format(\App\Models\Auction::MAX_INCREMENT_ALLOWED, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Payment Information -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment">
                                Payment Information
                            </button>
                        </h2>
                        <div id="collapsePayment" class="accordion-collapse collapse" data-bs-parent="#auctionAccordion">
                            <div class="accordion-body px-4 py-4 text-secondary">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fab fa-cc-visa fa-2x text-muted"></i>
                                        <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                                        <i class="fas fa-university fa-2x text-muted"></i>
                                    </div>
                                    <p class="mb-0">We accept Credit Cards, Debit Cards, and Direct Bank Transfers. Invoices include detailed payment instructions.</p>
                                </div>
                            </div>
                        </div>
                    </div>




                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if(isset($relatedAuctions) && $relatedAuctions->count() > 0)
        <div class="row mt-4 pt-2">
            <div class="col-12 mb-4 border-bottom pb-3 d-flex align-items-center justify-content-between">
                <h3 class="h4 fw-bold mb-0 text-dark">
                    <i class="fas fa-layer-group me-2 text-primary"></i>Related Products
                </h3>
                <a href="{{ route('auctions.index', ['category' => $auction->category->slug ?? '']) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold">View All</a>
            </div>
            <div class="row g-4">
                @foreach($relatedAuctions as $related)
                <div class="col-lg-3 col-md-6">
                    <div class="card card-elite h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white hover-shadow-lg transition-all">
                        <a href="{{ route('auctions.show', $related->id) }}" class="stretched-link"></a>
                        <!-- Image Section -->
                        <div class="position-relative overflow-hidden" style="height: 160px;">
                            <div class="d-block w-100 h-100">
                                @if($related->image)
                                    <img src="{{ str_starts_with($related->image, 'http') ? $related->image : asset('storage/' . $related->image) }}" class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $related->title }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                                        class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $related->title }}">
                                @endif
                            </div>
                            <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                                <span class="badge bg-gold text-dark shadow-sm fw-bold" style="font-size: 0.65rem;">{{ $related->category->name ?? 'Uncategorized' }}</span>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                            @php
                                $rNow = \Carbon\Carbon::now();
                                $rEnd = \Carbon\Carbon::parse($related->end_time);
                                $rDiff = $rNow->diff($rEnd);
                                $rIsClosed = $rNow->greaterThan($rEnd);
                            @endphp
                            
                            @if($rIsClosed)
                                <div class="alert alert-light border py-1 mb-2 text-center small text-secondary fw-bold" style="font-size: 0.65rem;">
                                    <i class="fas fa-lock me-1"></i> Auction Closed
                                </div>
                            @else
                            <div class="glass-timer text-center py-1 timer-val mb-2 shadow-none border {{ ($rDiff->d == 0 && $rDiff->h == 0) ? 'urgent-timer' : '' }}" 
                                data-days="{{ $rDiff->d }}" 
                                data-hours="{{ $rDiff->h }}" 
                                data-min="{{ $rDiff->i }}" 
                                data-sec="{{ $rDiff->s }}">
                                <div class="row g-0 px-2">
                                    <div class="col border-end border-light">
                                        <div class="fw-bold" style="font-size: 0.8rem;" data-days>{{ sprintf('%02d', $rDiff->d) }}</div>
                                        <small class="opacity-50 text-uppercase d-block" style="font-size: 0.45rem;">D</small>
                                    </div>
                                    <div class="col border-end border-light">
                                        <div class="fw-bold" style="font-size: 0.8rem;" data-hours>{{ sprintf('%02d', $rDiff->h) }}</div>
                                        <small class="opacity-50 text-uppercase d-block" style="font-size: 0.45rem;">H</small>
                                    </div>
                                    <div class="col border-end border-light">
                                        <div class="fw-bold" style="font-size: 0.8rem;" data-min>{{ sprintf('%02d', $rDiff->i) }}</div>
                                        <small class="opacity-50 text-uppercase d-block" style="font-size: 0.45rem;">M</small>
                                    </div>
                                    <div class="col">
                                        <div class="fw-bold text-primary" style="font-size: 0.8rem;" data-sec>{{ sprintf('%02d', $rDiff->s) }}</div>
                                        <small class="opacity-50 text-uppercase d-block" style="font-size: 0.45rem;">S</small>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <h3 class="h6 mb-1 fw-bold text-dark text-truncate title-hover">
                                {{ $related->title }}
                            </h3>
                            <div class="mb-2">
                                <span class="copy-id text-muted extra-small" style="font-size: 0.65rem;" onclick="event.preventDefault(); event.stopPropagation(); copyToClipboard('{{ $related->id }}', this)" title="Click to copy ID">ID: #{{ str_pad($related->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1"></i></span>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $related->user->avatar_url }}" class="rounded-circle me-1 border" width="18" height="18" style="object-fit: cover;" alt="p">
                                    <span class="text-xs text-muted text-truncate" style="max-width: 60px;">{{ $related->user->name ?? 'Seller' }}</span>
                                </div>
                                <span class="badge bg-light text-secondary border fw-normal text-xs px-2 py-1">
                                    {{ $related->bids->count() }} Bids
                                </span>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                                    <span class="text-xs text-secondary fw-bold text-uppercase">Current Bid</span>
                                    <span class="h6 mb-0 text-primary fw-bold">₹{{ number_format($related->current_price, 2) }}</span>
                                </div>
                                <div class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm transition-all btn-hover-effect" style="font-size: 0.75rem;">
                                    BID NOW <i class="fas fa-gavel ms-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Lightbox Modal -->
<div id="imageLightbox" class="lightbox-modal">
    <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
    
    <div class="lightbox-controls">
        <span class="lightbox-counter" id="lightboxCounter">1 / 1</span>
        <button class="close-lightbox" onclick="closeLightbox()" title="Close (Esc)">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <button class="lightbox-nav-btn prev" onclick="changeLightboxImage(-1)" title="Previous (Left Arrow)">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <div class="lightbox-content-wrapper">
        <img class="lightbox-image" id="lightboxImage">
        <div id="lightboxCaption" class="lightbox-caption"></div>
    </div>
    
    <button class="lightbox-nav-btn next" onclick="changeLightboxImage(1)" title="Next (Right Arrow)">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auction-gallery.css') }}">
<style>
    /* ===== HIBID-STYLE AUCTION PAGE ===== */
    .hibid-auction-section { background: transparent; }


    /* --- LEFT: Image Gallery --- */
    .hibid-main-image-wrap {
        position: relative;
        background: #fff;
        border: 1px solid #e0e4ea;
        border-radius: 8px;
        overflow: hidden;
        cursor: zoom-in;
    }
    .hibid-main-img {
        width: 100%;
        height: 420px;
        object-fit: contain;
        background: #fafafa;
        display: block;
        transition: transform 0.3s ease;
    }
    .hibid-main-image-wrap:hover .hibid-main-img { transform: scale(1.02); }
    .hibid-zoom-hint {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.45);
        color: #fff;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 4px;
        pointer-events: none;
    }

    /* Vertical Tabs Wrapper */
    .vertical-tabs-wrapper {
        background: #fff;
        border: 2px solid #cbd5e0;
        border-radius: 12px;
        overflow: hidden;
    }
    .hibid-gallery-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f8f9fc;
        border-bottom: 1px solid #e9ecef;
    }
    .hibid-gallery-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .hibid-photo-badge {
        font-size: 0.7rem;
        background: #e8f0fe;
        color: #4e73df;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .hibid-thumb-row {
        display: flex;
        gap: 8px;
        padding: 12px 14px;
        overflow-x: auto;
        flex-wrap: nowrap;
    }
    .hibid-thumb-row::-webkit-scrollbar { height: 4px; }
    .hibid-thumb-row::-webkit-scrollbar-track { background: #f1f1f1; }
    .hibid-thumb-row::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 999px; }
    .hibid-thumb {
        flex-shrink: 0;
        width: 68px;
        height: 68px;
        border-radius: 6px;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.2s, transform 0.2s;
    }
    .hibid-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .hibid-thumb:hover { border-color: #4e73df; transform: translateY(-2px); }
    .hibid-thumb.active { border-color: #4e73df; box-shadow: 0 2px 8px rgba(78,115,223,0.3); }
    .hibid-no-photos {
        text-align: center;
        padding: 24px;
        color: #adb5bd;
        font-size: 0.85rem;
    }
    .hibid-no-photos i { font-size: 2rem; display: block; margin-bottom: 8px; }

    /* --- RIGHT: Bid Card --- */
    .hibid-bid-card {
        background: #fff;
        border: 1px solid #e0e4ea;
        border-radius: 10px;
        padding: 22px 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .hibid-auction-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a1d23;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    .hibid-category-link {
        display: inline-block;
        background: #eef2ff;
        color: #4e73df;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 3px 14px;
        border-radius: 20px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .hibid-category-link:hover { background: #dde6fc; color: #3a5bbf; }

    /* Registration Prompt Styles */
    .registration-prompt {
        background: #f8f9fa;
        border: 2px dashed #dee2e6 !important;
        transition: all 0.3s ease;
    }
    .registration-prompt:hover {
        border-color: #4e73df !important;
        background: #fff;
    }
    .registration-icon-wrap {
        width: 60px;
        height: 60px;
        background: #e8f0fe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    /* Timer */
    .hibid-timer {
        display: flex;
        gap: 6px;
        padding: 12px;
        background: #f8f9fc;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    .hibid-timer-unit {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 8px 4px;
    }
    .hibid-timer-num {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1d23;
        line-height: 1;
    }
    .hibid-timer-label {
        font-size: 0.6rem;
        font-weight: 600;
        color: #9ea5b0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 3px;
    }
    .urgent-timer .hibid-timer-num { color: #dc3545 !important; }
    .hibid-closed-badge {
        background: #fdecea;
        color: #c0392b;
        text-align: center;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #f5c6cb;
    }

    /* Bid Info Rows */
    .hibid-bid-info {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 18px;
    }
    .hibid-bid-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 14px;
        border-bottom: 1px solid #f0f2f5;
        background: #fff;
    }
    .hibid-bid-row:last-child { border-bottom: none; }
    .hibid-bid-row--current { background: #f8f9fc; }
    .hibid-bid-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
    }
    .hibid-bid-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: #343a40;
    }
    .hibid-bid-value--primary { font-size: 1.35rem; color: #1557f0; }
    .hibid-bid-value--danger { color: #dc3545; }

    /* Increment Section */
    .hibid-increment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .hibid-increment-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #343a40;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .hibid-min-badge {
        font-size: 0.7rem;
        background: #e8f0fe;
        color: #4e73df;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .hibid-input-wrap {
        display: flex;
        align-items: center;
        border: 1.5px solid #ced4da;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
        margin-bottom: 10px;
        transition: border-color 0.2s;
    }
    .hibid-input-wrap:focus-within { border-color: #4e73df; }
    .hibid-input-prefix {
        padding: 0 12px;
        font-size: 1rem;
        font-weight: 700;
        color: #4e73df;
        background: #f8f9fc;
        border-right: 1px solid #dee2e6;
        height: 42px;
        display: flex;
        align-items: center;
    }
    .hibid-bid-input {
        border: none;
        outline: none;
        flex: 1;
        padding: 0 12px;
        font-size: 0.95rem;
        font-weight: 600;
        height: 42px;
        background: transparent;
    }
    .hibid-bid-input::-webkit-inner-spin-button,
    .hibid-bid-input::-webkit-outer-spin-button { opacity: 1; }

    /* Shortcut Buttons */
    .hibid-shortcuts {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 10px;
    }
    .hibid-shortcut-btn {
        flex: 1;
        min-width: calc(20% - 6px);
        border: 2px solid #4e73df;
        color: #4e73df;
        background: #fff;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 6px 2px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .hibid-shortcut-btn:hover, .hibid-shortcut-btn.active {
        background: #4e73df;
        color: #fff;
    }
    .hibid-max-jump {
        display: block;
        font-size: 0.72rem;
        color: #adb5bd;
        text-align: center;
        margin-bottom: 12px;
    }

    /* Place Bid Button */
    .hibid-place-bid-btn {
        width: 100%;
        background: #1557f0;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 13px;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        margin-bottom: 12px;
    }
    .hibid-place-bid-btn:hover { background: #0f43c2; }
    .hibid-place-bid-btn:active { transform: scale(0.99); }

    /* Login Prompt */
    .hibid-login-prompt {
        background: #f0f4ff;
        border: 2px solid #cbd5e0;
        border-radius: 6px;
        padding: 12px 16px;
        font-size: 0.85rem;
        color: #4a5568;
        margin-bottom: 12px;
        text-align: center;
    }
    .hibid-login-prompt a { color: #1557f0; font-weight: 700; text-decoration: none; }

    /* Watchlist & Share Row */
    .hibid-action-row {
        display: flex;
        gap: 10px;
        align-items: stretch;
    }
    .watchlist-toggle-form {
        flex: 1;
        display: flex;
    }
    .hibid-watchlist-btn {
        flex: 1;
        width: 100%;
        border: 2px solid #cbd5e0;
        background: #fff;
        color: #495057;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 16px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        line-height: 1;
    }
    .hibid-watchlist-btn:hover { 
        border-color: #4e73df; 
        color: #4e73df;
        background: #f8faff;
    }
    .hibid-share-btn {
        width: 46px;
        border: 2px solid #cbd5e0;
        background: #fff;
        color: #495057;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.2s ease;
        padding: 0;
    }
    .hibid-share-btn:hover { 
        border-color: #4e73df; 
        color: #4e73df;
        background: #f8faff;
    }
    
    /* Bidding Tabs Styles */
    .hibid-bid-tabs {
        border-bottom: none;
        background: #f8f9fc;
        padding: 5px;
        border-radius: 8px;
    }
    .hibid-bid-tabs .nav-link {
        font-size: 0.8rem;
        font-weight: 700;
        color: #6c757d;
        padding: 8px 10px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .hibid-bid-tabs .nav-link.active {
        background: #fff !important;
        color: #4e73df !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }

    /* Winning Badge Pulse */
    .hibid-pulse {
        animation: hibid-pulse-glow 2s infinite;
    }
    @keyframes hibid-pulse-glow {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.02); }
        100% { opacity: 1; transform: scale(1); }
    }

    /* Seller Card */
    .hibid-seller-card {
        background: #fff;
        border: 2px solid #cbd5e0;
        border-radius: 10px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .hibid-seller-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }
    .hibid-seller-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #cbd5e0;
    }
    .hibid-seller-info { flex: 1; }
    .hibid-seller-name {
        display: block;
        font-weight: 700;
        font-size: 0.9rem;
        color: #1a1d23;
        text-decoration: none;
        margin-bottom: 3px;
    }
    .hibid-seller-name:hover { color: #4e73df; }
    .hibid-seller-meta { display: flex; gap: 10px; align-items: center; }
    .hibid-verified { font-size: 0.68rem; color: #27ae60; font-weight: 700; }
    .hibid-rating { font-size: 0.68rem; color: #f39c12; font-weight: 700; }
    .hibid-contact-btn {
        border: 1.5px solid #ced4da;
        background: #fff;
        color: #495057;
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .hibid-contact-btn:hover { border-color: #4e73df; color: #4e73df; }

    /* === Keep old related/accordion styles === */
    .premium-accordion .accordion-item {
        border-radius: 1rem !important;
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
    }
    .premium-accordion .accordion-header { border: 1px solid rgba(0,0,0,0.05); }
    .premium-accordion .accordion-button {
        color: #4e73df !important;
        border-radius: 1rem !important;
        box-shadow: none !important;
    }
    .premium-accordion .accordion-button:not(.collapsed) {
        background: #f8f9fc !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .info-table th {
        font-weight: 600; color: #4e73df;
        font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .last-child-border-0:last-child { border-bottom: none !important; }
    .card-elite { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(0,0,0,0.03) !important; }
    .card-elite:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .bg-gold { background-color: #d4af37; }
    .text-xs { font-size: 0.75rem; }
    .glass-timer { background: rgba(248, 249, 250, 0.8); backdrop-filter: blur(4px); border-radius: 8px; }
    .urgent-timer { background: rgba(220, 53, 69, 0.08) !important; border-color: rgba(220, 53, 69, 0.3) !important; }
    .title-hover:hover { color: var(--bs-primary) !important; }
    .btn-hover-effect:active { transform: scale(0.98); }

    /* Description Content Styling */
    .description-content ul, .description-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }
    .description-content li {
        margin-bottom: 0.5rem;
    }
    .description-content h2, .description-content h3, .description-content h4 {
        color: #343a40;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 700;
    }
    .description-content p {
        margin-bottom: 1rem;
    }
    .description-content strong {
        color: #212529;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/auction-gallery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = [
            @foreach($galleryImages as $imgUrl)
                "{{ $imgUrl }}",
            @endforeach
        ];
        initGallery(images, "{{ str_replace('"', '\"', $auction->title) }}");

        // Bid Increment Real-time Feedback
        const bidInput = document.getElementById('bid-increment');
        const maxBidInput = document.getElementById('max-bid-amount');
        const totalFeedback = document.querySelector('.bid-feedback-total');
        const errorFeedback = document.getElementById('bid-feedback-area');
        const placeBidForm = document.getElementById('place-bid-form');
        let currentPrice = {{ $auction->current_price }};
        let minIncrement = {{ $auction->min_increment ?? 0.01 }};
        const maxIncrement = {{ \App\Models\Auction::MAX_INCREMENT_ALLOWED }};

        function syncAuctionUI(data) {
            if (!data) return;

            // 1. Update Current Price
            if (typeof data.current_price !== 'undefined') {
                currentPrice = parseFloat(data.current_price);
                const display = document.getElementById('current-bid-display');
                if (display) {
                    display.innerText = '₹' + currentPrice.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                document.querySelectorAll('.hibid-bid-value--primary, .h6.mb-0.text-primary.fw-bold').forEach(el => {
                   if (el.innerText.includes('₹')) {
                        el.innerText = '₹' + currentPrice.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                });
            }
            
            // 1.5 Update Bid Counts
            if (typeof data.total_bids !== 'undefined') {
                const countBox = document.getElementById('total-bids-count-box');
                const countCard = document.getElementById('total-bids-count-card');
                if (countBox) countBox.innerText = data.total_bids;
                if (countCard) countCard.innerText = data.total_bids;
            }

            // 2. Update Min Increment
            if (typeof data.min_increment !== 'undefined') {
                minIncrement = parseFloat(data.min_increment);
                const incInput = document.getElementById('bid-increment');
                if(incInput) {
                    incInput.placeholder = minIncrement.toFixed(2);
                    incInput.min = minIncrement;
                }
                const maxInp = document.getElementById('max-bid-amount');
                if(maxInp) {
                    maxInp.min = (currentPrice + minIncrement).toFixed(2);
                }
                document.querySelectorAll('.hibid-min-badge').forEach(badge => {
                    badge.innerText = 'Min: ₹' + minIncrement.toFixed(2);
                });
            }
            
            // 2.5 Update End Time & Timer
            if (data.end_time && data.end_time_formatted) {
                const endTimeDisplay = document.getElementById('auction-end-time-display');
                if (endTimeDisplay) endTimeDisplay.innerText = data.end_time_formatted;
                
                const mainTimer = document.querySelector('.hibid-timer.timer-val');
                if (mainTimer && data.end_time) {
                    const newEndDate = new Date(data.end_time);
                    const now = new Date();
                    let diff = Math.floor((newEndDate - now) / 1000);
                    
                    if (diff > 0) {
                        const days = Math.floor(diff / (24 * 3600));
                        diff %= (24 * 3600);
                        const hours = Math.floor(diff / 3600);
                        diff %= 3600;
                        const mins = Math.floor(diff / 60);
                        const secs = diff % 60;
                        
                        mainTimer.dataset.days = days;
                        mainTimer.dataset.hours = hours;
                        mainTimer.dataset.min = mins;
                        mainTimer.dataset.sec = secs;
                    }
                }
            }

            // 3. Update Status Badge
            const statusBadge = document.getElementById('winner-status-badge');
            if (statusBadge) {
                const authUserId = parseInt(statusBadge.dataset.userId);
                let isWinning = data.is_winning;
                if (typeof isWinning === 'undefined' && data.winner_id) {
                    isWinning = (authUserId === parseInt(data.winner_id));
                }

                if (typeof isWinning !== 'undefined') {
                    if (isWinning) {
                        statusBadge.innerHTML = `<div class="badge bg-success py-2 px-3 rounded-pill w-100 hibid-pulse mb-2">
                            <i class="fas fa-check-circle me-1"></i> You are the highest bidder!
                        </div>`;
                    } else if (data.winner_id || data.winner_username) {
                        statusBadge.innerHTML = `<div class="badge bg-danger py-2 px-3 rounded-pill w-100 mb-2">
                            <i class="fas fa-times-circle me-1"></i> Someone has outbid you!
                        </div>`;
                    }
                }
            }

            // 4. Update History Table
            const historyTable = document.getElementById('bidding-history-table');
            if (historyTable && data.winner_username) {
                const tbody = historyTable.querySelector('tbody');
                const noBidsRow = document.getElementById('no-bids-row');
                if (noBidsRow) noBidsRow.remove();

                tbody.querySelectorAll('.badge.bg-success-subtle').forEach(b => b.remove());

                const amount = data.bid_amount || data.current_price;
                const newRow = document.createElement('tr');
                newRow.className = 'animate__animated animate__fadeInDown';
                newRow.innerHTML = `
                    <td class="ps-4 fw-medium text-dark">
                        @_${data.winner_username}
                        <span class="badge bg-success-subtle text-success ms-1 small">Highest</span>
                    </td>
                    <td class="fw-bold text-primary">₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-secondary small pe-4">just now</td>
                `;
                tbody.insertBefore(newRow, tbody.firstChild);
            }

            updateBidFeedback();
        }

        function updateBidFeedback() {
            const activeTab = document.querySelector('#bidTab .nav-link.active') ? document.querySelector('#bidTab .nav-link.active').id : 'quick-bid-tab';
            const currentInputField = (activeTab === 'quick-bid-tab' ? bidInput : maxBidInput);
            if (!currentInputField) return;

            const inputGroup = currentInputField.closest('.hibid-input-wrap');
            
            errorFeedback.innerHTML = '';
            totalFeedback.innerHTML = '';
            if (inputGroup) inputGroup.classList.remove('border', 'border-danger', 'border-success');

            if (activeTab === 'quick-bid-tab') {
                const val = parseFloat(bidInput.value);
                if (!isNaN(val) && val > 0) {
                    const newTotal = (currentPrice + val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (inputGroup) { // Check if inputGroup exists before adding classes
                        if (val < minIncrement) {
                            inputGroup.classList.add('border', 'border-danger');
                            errorFeedback.innerHTML = `<div class="alert alert-danger py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>Min increment is ₹${minIncrement.toFixed(2)}</div>`;
                        } else if (val > maxIncrement) {
                            inputGroup.classList.add('border', 'border-danger');
                            errorFeedback.innerHTML = `<div class="alert alert-danger py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>Max jump is ₹${maxIncrement.toFixed(2)}</div>`;
                        } else {
                            inputGroup.classList.add('border', 'border-success');
                            totalFeedback.innerHTML = `<div class="alert alert-info py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm animate__animated animate__fadeIn"><i class="fas fa-calculator me-2 text-primary"></i><strong>New Total Bid:</strong> ₹${newTotal}</div>`;
                        }
                    }
                }
            } else {
                const val = parseFloat(maxBidInput.value);
                if (!isNaN(val) && val > 0) {
                    if (inputGroup) { // Check if inputGroup exists before adding classes
                        if (val < (currentPrice + minIncrement)) {
                            inputGroup.classList.add('border', 'border-danger');
                            errorFeedback.innerHTML = `<div class="alert alert-danger py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>Min auto-bid is ₹${(currentPrice + minIncrement).toFixed(2)}</div>`;
                        } else {
                            inputGroup.classList.add('border', 'border-success');
                            totalFeedback.innerHTML = `<div class="alert alert-success py-2 px-3 mt-2 rounded-3 small border-0 shadow-sm animate__animated animate__fadeIn"><i class="fas fa-robot me-2"></i><strong>Max Limit:</strong> ₹${val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>`;
                        }
                    }
                }
            }
        }

        if (bidInput) bidInput.addEventListener('input', updateBidFeedback);
        if (maxBidInput) maxBidInput.addEventListener('input', updateBidFeedback);
        
        // Tab switch listener
        document.querySelectorAll('#bidTab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                updateBidFeedback();
            });
        });

        document.querySelectorAll('.bid-shortcut').forEach(btn => {
            btn.addEventListener('click', function() {
                const amount = this.getAttribute('data-amount');
                if (bidInput) {
                    bidInput.value = amount;
                    updateBidFeedback();
                }
                this.classList.add('active');
                setTimeout(() => this.classList.remove('active'), 200);
            });
        });

        // Removed frontend SweetAlert validation. Handled completely by Axios and backend now.
    const form = document.getElementById('place-bid-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing...';
            
            const activeTab = document.querySelector('#bidTab .nav-link.active') ? document.querySelector('#bidTab .nav-link.active').id : 'quick-bid-tab';
            
            const formData = new FormData(form);
            
            // Do not send the field from the inactive tab to avoid backend confusion
            if (activeTab === 'quick-bid-tab') {
                formData.delete('max_bid_amount');
            } else {
                formData.delete('increment');
            }

            const data = {};
            for (let [key, value] of formData.entries()) {
                if (value !== "") {
                    data[key] = value;
                }
            }
            
            axios.post(form.action, data, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => {
                const res = response.data;
                const statusContainer = document.getElementById('bid-status-container');
                
                if (res.status === 'success') {
                    statusContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show py-2 px-3 rounded-3 small border-0 shadow-sm mb-3">
                        <i class="fas fa-check-circle me-2"></i>${res.message}
                        <button type="button" class="btn-close small" data-bs-dismiss="alert" style="padding: 0.75rem;"></button>
                    </div>`;
                    
                    const incInput = document.getElementById('bid-increment');
                    if(incInput) incInput.value = '';
                    const maxInput = document.getElementById('max-bid-amount');
                    if(maxInput) maxInput.value = '';
                    
                } else if (res.status === 'warning') {
                    statusContainer.innerHTML = `<div class="alert alert-warning alert-dismissible fade show py-2 px-3 rounded-3 small border-0 shadow-sm mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>${res.message}
                        <button type="button" class="btn-close small" data-bs-dismiss="alert" style="padding: 0.75rem;"></button>
                    </div>`;
                }

                syncAuctionUI(res);
            }).catch(error => {
                console.error("Bid Placement Error:", error);
                const statusContainer = document.getElementById('bid-status-container');
                let errorMsg = 'An error occurred while placing your bid.';
                
                if (error.response && error.response.data) {
                    if (error.response.data.message) {
                        errorMsg = error.response.data.message;
                    }
                    if (error.response.data.errors) {
                        const errors = error.response.data.errors;
                        errorMsg = Object.values(errors)[0][0];
                    }
                    if (typeof error.response.data === 'string' && error.response.data.includes('<!DOCTYPE html>')) {
                        errorMsg = `Server Error (${error.response.status}).`;
                    }
                } else if (error.message) {
                    errorMsg = error.message;
                }
                
                statusContainer.innerHTML = `<div class="alert alert-danger alert-dismissible fade show py-2 px-3 rounded-3 small border-0 shadow-sm mb-3">
                    <i class="fas fa-exclamation-circle me-2"></i>${errorMsg}
                    <button type="button" class="btn-close small" data-bs-dismiss="alert" style="padding: 0.75rem;"></button>
                </div>`;
            }).finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        });
    }

    try {
        if('{{ env('BROADCAST_CONNECTION') }}' === 'reverb') {
            Pusher.logToConsole = false;
            var pusher = new Pusher('{{ env("REVERB_APP_KEY") }}', {
                wsHost: window.location.hostname,
                wsPort: {{ env("REVERB_PORT", 8080) }},
                wssPort: {{ env("REVERB_PORT", 8080) }},
                forceTLS: {{ env("REVERB_SCHEME", "http") === "https" ? "true" : "false" }},
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
            });

            var channel = pusher.subscribe('auction.{{ $auction->id }}');
            channel.bind('bid.placed', function(data) {
                console.log("New Bid Received:", data);
                syncAuctionUI(data);
            });
        }
    } catch(err) {
        console.error("Pusher setup error:", err);
    }
});
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: {!! json_encode(session('success')) !!},
            confirmButtonColor: '#4e73df',
            confirmButtonText: 'OK',
            timer: 5000,
            timerProgressBar: true
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: {!! json_encode(session('error')) !!},
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@endpush

@endsection



