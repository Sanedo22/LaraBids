<!-- Sorting Bar (Sticky under header) -->
<div class="sticky-top bg-white mb-4 shadow-sm rounded-4 px-4 py-3" style="top: 85px; z-index: 9; border: 1px solid rgba(0,0,0,0.06);">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span class="text-secondary small fw-bold text-uppercase me-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Showing:</span>
            <span class="small fw-bold text-dark px-3 py-1 bg-light rounded-pill">{{ $auctions->total() }} Results</span>
        </div>
        
        <div class="d-flex align-items-center ms-auto">
            <div class="d-flex align-items-center bg-light rounded-3 px-3 py-1 border border-light">
                <label class="small fw-bold text-secondary text-uppercase me-2 mb-0 d-none d-md-inline-block" for="sortSelect" style="font-size: 0.65rem; letter-spacing: 0.05em;">Sort By:</label>
                <select id="sortSelect" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark cursor-pointer shadow-none py-2 ps-2 pe-5 w-auto" 
                        style="font-size: 0.9rem; min-width: 180px; background-position: right 10px center;" 
                        onchange="applySort(this.value)">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newly Listed</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="ending_soon" {{ request('sort') == 'ending_soon' ? 'selected' : '' }}>Ending Soon</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row g-4" id="auction-grid">
    @forelse($auctions as $auction)
    <div class="col-md-6 col-xl-4 auction-item-wrapper">
        <div class="card card-elite h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white hover-shadow-lg transition-all">
            <a href="{{ route('auctions.show', $auction->id) }}" class="stretched-link"></a>
            <!-- Image Section -->
            <div class="position-relative overflow-hidden" style="height: 180px;">
                <div class="d-block w-100 h-100">
                    @if($auction->image)
                        <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $auction->title }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=1200"
                            class="card-img-top h-100 object-fit-cover shadow-sm transition-all" alt="{{ $auction->title }}">
                    @endif
                </div>
                <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                    <span class="badge bg-gold text-dark shadow-sm fw-bold" style="font-size: 0.7rem;">{{ $auction->category->name ?? 'Uncategorized' }}</span>
                </div>
                <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                    <form action="{{ route('user.watchlist.toggle', $auction->id) }}" method="POST" class="watchlist-toggle-form">
                        @csrf
                        <button type="submit" class="btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px; border: none; background: rgba(255,255,255,0.8); backdrop-filter: blur(4px);">
                            <i class="{{ $auction->watchlists->isNotEmpty() ? 'fas' : 'far' }} fa-heart text-danger" style="font-size: 0.8rem;"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Content Section -->
            <div class="card-body p-3 d-flex flex-column flex-grow-1">
                @php
                    $now = \Carbon\Carbon::now();
                    $end = \Carbon\Carbon::parse($auction->end_time);
                    $start = \Carbon\Carbon::parse($auction->start_time);
                    $diff = $now->diff($end);
                    $isClosed = $now->greaterThan($end);
                    $isUpcoming = $now->lessThan($start);
                @endphp
                
                @if($isClosed)
                    <div class="alert alert-danger alert-permanent border-0 py-1 mb-2 text-center small fw-bold" style="font-size: 0.7rem; background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-times-circle me-1"></i> Auction Closed
                    </div>
                @elseif($isUpcoming)
                    <div class="alert alert-info alert-permanent py-1 mb-2 text-center small border-0 fw-bold" style="font-size: 0.7rem;">
                        <i class="fas fa-clock me-1"></i> Starts {{ $start->format('M d, H:i') }}
                    </div>
                @else
                <div class="glass-timer text-center py-1 timer-val mb-2 shadow-none border {{ ($diff->d == 0 && $diff->h == 0) ? 'urgent-timer' : '' }}" 
                    data-days="{{ $diff->d }}" 
                    data-hours="{{ $diff->h }}" 
                    data-min="{{ $diff->i }}" 
                    data-sec="{{ $diff->s }}">
                    <div class="row g-0 px-2">
                        <div class="col border-end border-light">
                            <div class="fw-bold fs-7" data-days>{{ sprintf('%02d', $diff->d) }}</div>
                            <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">D</small>
                        </div>
                        <div class="col border-end border-light">
                            <div class="fw-bold fs-7" data-hours>{{ sprintf('%02d', $diff->h) }}</div>
                            <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">H</small>
                        </div>
                        <div class="col border-end border-light">
                            <div class="fw-bold fs-7" data-min>{{ sprintf('%02d', $diff->i) }}</div>
                            <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">M</small>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-7 text-primary" data-sec>{{ sprintf('%02d', $diff->s) }}</div>
                            <small class="opacity-50 text-uppercase d-block" style="font-size: 0.5rem;">S</small>
                        </div>
                    </div>
                </div>
                @endif

                <h3 class="h6 mb-1 fw-bold text-dark text-truncate title-hover">
                    {{ $auction->title }}
                </h3>
                <div class="mb-2">
                    <span class="copy-id text-muted extra-small" style="font-size: 0.65rem;" onclick="event.preventDefault(); event.stopPropagation(); copyToClipboard('{{ $auction->id }}', this)" title="Click to copy ID">ID: #{{ str_pad($auction->id, 5, '0', STR_PAD_LEFT) }} <i class="far fa-copy ms-1"></i></span>
                </div>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        @if($auction->user && $auction->user->avatar)
                            <img src="{{ str_starts_with($auction->user->avatar, 'http') ? $auction->user->avatar : asset('storage/' . $auction->user->avatar) }}" class="rounded-circle me-1 border" width="20" height="20" style="object-fit: cover;" alt="{{ $auction->user->name }}">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-1 border" style="width: 20px; height: 20px;">
                                <i class="fas fa-user text-secondary" style="font-size: 10px;"></i>
                            </div>
                        @endif
                        <span class="text-xs text-muted text-truncate" style="max-width: 80px;">{{ $auction->user->name ?? 'Seller' }}</span>
                    </div>
                    <span class="badge bg-light text-secondary border fw-normal text-xs px-2 py-1">
                        {{ $auction->bids->count() }} Bids
                    </span>
                </div>
                
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                        <span class="text-xs text-secondary fw-bold text-uppercase">{{ $isClosed ? 'Final Bid' : 'Current Bid' }}</span>
                        <span class="h6 mb-0 text-primary fw-bold">₹{{ number_format($auction->current_price, 2) }}</span>
                    </div>
                    <div class="btn {{ $isClosed ? 'btn-outline-secondary' : 'btn-primary' }} w-100 py-2 rounded-pill fw-bold shadow-sm transition-all btn-hover-effect" style="font-size: 0.8rem;">
                        @if($isClosed) CLOSED @elseif($isUpcoming) VIEW @else BID NOW @endif <i class="fas {{ $isClosed ? 'fa-lock' : 'fa-gavel' }} ms-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 py-5">
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-search fa-2x text-muted opacity-25"></i>
            </div>
            <h6 class="fw-bold text-secondary mb-1">No Auctions Found</h6>
            <a href="{{ route('auctions.index') }}" class="small text-decoration-none fw-bold" id="reset-filters-btn">Reset Filters</a>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination Footer -->
@if($auctions->hasPages())
<div class="mt-5 pt-4 border-top" id="pagination-container">
    {{ $auctions->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>
@endif
