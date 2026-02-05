@extends('website.layouts.dashboard')

@section('title', 'My Dashboard | LaraBids')

@section('content')

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">Overview</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="bg-white p-2 px-3 small border rounded shadow-sm text-dark fw-bold">
        <i class="fas fa-wallet text-primary me-2"></i> Balance: <span class="text-primary">$0.00</span>
    </div>
</div>


<!-- Stats Grid -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow h-100 py-2 border-start-primary border-0 border-4 border-start">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2 ps-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 small">Active Bids</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_bids'] }}</div>
                    </div>
                    <div class="col-auto pe-3">
                        <i class="fas fa-gavel fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow h-100 py-2 border-start-success border-0 border-4 border-start">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2 ps-3">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1 small">Total Wins</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_wins'] }}</div>
                    </div>
                    <div class="col-auto pe-3">
                        <i class="fas fa-trophy fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow h-100 py-2 border-start-info border-0 border-4 border-start">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2 ps-3">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1 small">Watchlist</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['watchlist_count'] }}</div>
                    </div>
                    <div class="col-auto pe-3">
                        <i class="fas fa-heart fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow h-100 py-2 border-start-warning border-0 border-4 border-start">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2 ps-3">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1 small">Messages</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['messages_count'] }}</div>
                    </div>
                    <div class="col-auto pe-3">
                        <i class="fas fa-comments fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Recent Activity -->
<div class="card card-elite p-0 overflow-hidden shadow-sm">
    <div class="card-header bg-white py-3 border-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-primary fw-bold h6 mb-0">Active Bids Monitoring</h5>
            <a href="{{ route('user.my-bids') }}" class="btn btn-primary btn-sm rounded-pill px-3">View All</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-light py-3 ps-4 text-xs font-weight-bold text-gray-600 text-uppercase small">AUCTION ITEM</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">STATUS</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">CURRENT BID</th>
                    <th class="border-light py-3 pe-4 text-end text-xs font-weight-bold text-gray-600 text-uppercase small">ACTION</th>
                </tr>
            </thead>
            <tbody class="align-middle bg-white">
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="fas fa-inbox fs-1 mb-3 d-block text-gray-300 opacity-25"></i>
                        <span class="text-secondary small">No active bids yet.</span>
                        <div class="mt-2">
                             <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary btn-sm px-4">Browse Auctions</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


@endsection
