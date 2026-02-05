@extends('website.layouts.dashboard')

@section('title', 'Winning Items | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">Won Items</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Won Items</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card card-elite p-0 overflow-hidden shadow-sm border-light">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-light py-3 ps-4 text-xs font-weight-bold text-gray-600 text-uppercase small">ITEM</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">WINNING BID</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">WON DATE</th>
                    <th class="border-light py-3 text-xs font-weight-bold text-gray-600 text-uppercase small">PAYMENT STATUS</th>
                    <th class="border-light py-3 pe-4 text-end text-xs font-weight-bold text-gray-600 text-uppercase small">ACTION</th>
                </tr>
            </thead>
            <tbody class="align-middle bg-white">
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="fas fa-trophy fs-1 mb-3 d-block text-gray-300 opacity-25"></i>
                        <span class="text-secondary small">No winning items yet. Keep bidding to win amazing items!</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


@endsection
