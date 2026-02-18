@extends('website.layouts.dashboard')

@section('title', 'My Auctions | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">My Auctions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Auctions</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('auctions.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> List New Item
    </a>
</div>

<!-- Auctions List -->
<div class="card card-elite p-0 overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-light py-3 ps-4 text-xs font-weight-bold text-gray-600 text-uppercase small">ITEM</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CATEGORY</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">STATUS</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CURRENT PRICE</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">WINNER</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">BIDS</th>
                    <th class="border-light py-3 pe-4 text-end text-xs font-weight-bold text-gray-600 text-uppercase small">ACTION</th>
                </tr>
            </thead>
            <tbody class="align-middle bg-white">
                @forelse($auctions as $auction)
                <tr>
                    <td class="py-3 ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 overflow-hidden border" style="width: 50px; height: 50px;">
                                @if($auction->image)
                                    <img src="{{ str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image) }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <img src="https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120" class="w-100 h-100 object-fit-cover">
                                @endif
                            </div>
                            <div style="max-width: 250px;">
                                <h6 class="text-dark fw-bold mb-0 small text-truncate" title="{{ $auction->title }}">{{ $auction->title }}</h6>
                                <small class="text-muted" style="font-size: 0.7rem;">Created: {{ $auction->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="small text-secondary fw-bold">{{ $auction->category->name ?? 'N/A' }}</span>
                    </td>
                    <td class="py-3">
                        @php
                            $isExpired = \Carbon\Carbon::parse($auction->end_time)->isPast();
                        @endphp
                        @if($auction->status === 'pending')
                            <span class="badge bg-warning text-white rounded-pill px-3 py-2 small">Pending Approval</span>
                        @elseif($isExpired)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-2 small">Expired</span>
                        @else
                            <span class="badge bg-success text-white rounded-pill px-3 py-2 small">Active</span>
                        @endif
                    </td>
                    <td class="py-3 fw-bold text-primary">
                        ₹{{ number_format($auction->current_price, 2) }}
                    </td>
                    <td class="py-3">
                        @php
                            $highestBid = $auction->highestBid();
                        @endphp
                        @if($highestBid)
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $highestBid->user->avatar_url }}" class="rounded-circle border" width="24" height="24" alt="Winner Avatar">
                                <span class="small text-dark fw-bold text-truncate" style="max-width: 100px;" title="{{ $highestBid->user->name }}">
                                    {{ $highestBid->user->name }}
                                </span>
                            </div>
                        @else
                            <span class="text-muted small">No Bids</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-1 border small">
                            {{ $auction->bids->count() }} Bids
                        </span>
                    </td>
                    <td class="py-3 pe-4 text-end">
                        <a href="{{ route('auctions.show', $auction->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-eye me-1"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="opacity-50">
                            <i class="fas fa-inbox fs-1 mb-3 d-block text-gray-300"></i>
                            <h6 class="text-secondary small">You haven't listed any items yet.</h6>
                            <div class="mt-3">
                                <a href="{{ route('auctions.create') }}" class="btn btn-primary btn-sm px-4">Start Selling Today</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $auctions->links('pagination::bootstrap-5') }}
</div>

@endsection
