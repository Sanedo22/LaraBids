@extends('website.layouts.dashboard')

@section('title', 'Watchlist | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">My Watchlist</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Watchlist</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('auctions.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="fas fa-search me-1"></i> Browse Auctions
    </a>
</div>

<!-- Watchlist Table -->
<div class="card card-elite p-0 overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-light py-3 ps-4 text-xs font-weight-bold text-gray-600 text-uppercase small">ITEM</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CATEGORY</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CURRENT PRICE</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">END TIME</th>
                    <th class="border-light py-3 pe-4 text-end text-xs font-weight-bold text-gray-600 text-uppercase small">ACTION</th>
                </tr>
            </thead>
            <tbody class="align-middle bg-white">
                @forelse($watchlists as $item)
                @php $auction = $item->auction; @endphp
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
                                <small class="text-muted" style="font-size: 0.7rem;">Seller: {{ $auction->user->name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="small text-secondary fw-bold">{{ $auction->category->name ?? 'N/A' }}</span>
                    </td>
                    <td class="py-3 fw-bold text-primary">
                        ₹{{ number_format($auction->current_price, 2) }}
                    </td>
                    <td class="py-3">
                        <span class="small text-muted">{{ $auction->end_time->format('M d, Y H:i') }}</span>
                    </td>
                    <td class="py-3 pe-4 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('auctions.show', $auction->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <form action="{{ route('user.watchlist.toggle', $auction->id) }}" method="POST" class="d-inline watchlist-toggle-form">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Remove from Watchlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="opacity-50">
                            <i class="fas fa-heart fs-1 mb-3 d-block text-gray-300"></i>
                            <h6 class="text-secondary small">Your watchlist is empty.</h6>
                            <p class="text-muted small mb-4">Start adding items you love!</p>
                            <div class="mt-3">
                                <a href="{{ route('auctions.index') }}" class="btn btn-primary btn-sm px-4">Browse Auctions</a>
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
    {{ $watchlists->links('pagination::bootstrap-5') }}
</div>

@endsection
