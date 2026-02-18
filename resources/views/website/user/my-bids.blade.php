@extends('website.layouts.dashboard')

@section('title', 'My Bids | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">My Active Bids</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Bids</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card card-elite p-0 overflow-hidden shadow-sm border-light">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-light py-3 ps-4 text-xs font-weight-bold text-gray-600 text-uppercase small">AUCTION ITEM</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">MY BID</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CURRENT BID</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">STATUS</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">TIME LEFT</th>
                    <th class="border-light py-3 pe-4 text-end text-xs font-weight-bold text-gray-600 text-uppercase small">ACTION</th>
                </tr>
            </thead>
            <tbody class="align-middle bg-white">
                @forelse($bids as $bid)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $bid->auction->image ? asset('storage/' . $bid->auction->image) : asset('assets/images/product/default.png') }}" 
                                class="rounded-3" height="50" width="50" style="object-fit: cover;" alt="{{ $bid->auction->title }}">
                            <div>
                                <h6 class="mb-0 text-dark fw-bold small">{{ $bid->auction->title }}</h6>
                                <small class="text-muted">ID: #{{ str_pad($bid->auction->id, 5, '0', STR_PAD_LEFT) }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="fw-bold text-dark">₹{{ number_format($bid->amount, 2) }}</span>
                    </td>
                    <td class="py-3">
                        <span class="text-primary fw-bold">₹{{ number_format($bid->auction->current_price, 2) }}</span>
                    </td>
                    <td class="py-3">
                        <span class="badge bg-{{ $bid->auction->getStatusBadgeClass() }} rounded-pill small">
                            {{ ucfirst($bid->auction->status) }}
                        </span>
                    </td>
                    <td class="py-3">
                        <span class="text-secondary small">
                            @if($bid->auction->status === 'active')
                                {{ $bid->auction->end_time->diffForHumans(null, true) }} left
                            @else
                                Ended
                            @endif
                        </span>
                    </td>
                    <td class="pe-4 text-end">
                        <a href="{{ route('auctions.show', $bid->auction->id) }}" class="btn btn-outline-gold btn-sm rounded-pill px-3">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-gavel fs-1 mb-3 d-block text-gray-300 opacity-25"></i>
                        <span class="text-secondary small">You haven't placed any bids yet.</span>
                        <div class="mt-2">
                             <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary btn-sm px-4">Start Bidding</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
