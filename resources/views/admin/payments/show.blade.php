@extends('admin.layouts.admin')

@section('title', 'Payment Detail - ' . $payment->txnid)

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Payment Details</h1>
            <div class="d-none d-sm-block">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm mr-2 px-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Receipt
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Summary Column -->
            <div class="col-lg-8">
                <!-- Status & IDs -->
                <div class="card shadow-sm border-0 mb-4 px-4 py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mr-4 mb-2">
                            <small class="text-uppercase text-muted font-weight-bold d-block mb-1">Status</small>
                            <span class="badge badge-{{ $payment->status === 'success' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }} px-3 py-1 text-uppercase">
                                {{ $payment->status }}
                            </span>
                        </div>
                        <div class="mr-4 mb-2 border-left pl-4">
                            <small class="text-uppercase text-muted font-weight-bold d-block mb-1">Transaction ID</small>
                            <span class="h6 font-weight-bold text-dark">{{ $payment->txnid }}</span>
                        </div>
                        <div class="mb-2 border-left pl-4 text-right">
                            <small class="text-uppercase text-muted font-weight-bold d-block mb-1">Date</small>
                            <span class="h6 font-weight-bold text-dark">{{ $payment->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financials -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-primary">Winning Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="py-3">Auction Win Bid #{{ $payment->auction_id }}</td>
                                        <td class="py-3 text-right text-dark">₹{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light-success">
                                        <td class="py-3 font-weight-bold text-success">Platform Fee</td>
                                        <td class="py-3 text-right font-weight-bold text-success">₹{{ number_format($payment->commission_amount ?? ($payment->amount * 0.05), 2) }}</td>
                                    </tr>
                                    <tr class="h5 font-weight-bold">
                                        <td class="py-4 text-dark border-0">Total Sale Amount</td>
                                        <td class="py-4 text-right text-primary border-0">₹{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>

                <!-- Auction Insight -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-dark mb-3">Item Details</h6>
                        <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                            <div class="bg-light rounded p-2 mr-3 border" style="width: 80px; height: 80px; flex-shrink: 0;">
                                @if($payment->auction && $payment->auction->image_url)
                                    <img src="{{ asset('storage/'.$payment->auction->image_url) }}" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100"><i class="fas fa-image text-gray-300"></i></div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="font-weight-bold text-primary mb-1">{{ $payment->auction->title ?? 'Untitled Item' }}</h6>
                                <div class="d-flex font-weight-bold small">
                                    <span class="text-muted mr-3"><i class="fas fa-gavel mr-1"></i> Bids: {{ $totalBids }}</span>
                                    <span class="text-muted"><i class="fas fa-link mr-1"></i> ID: #{{ $payment->auction_id }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.auctions.show', $payment->auction_id) }}" class="btn btn-outline-primary btn-sm ml-3">
                                View Full Auction
                            </a>
                        </div>

                        <!-- Bid History Timeline -->
                        <h6 class="font-weight-bold text-dark mb-3">Full Bid History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-2 px-3 border-0 small text-uppercase font-weight-bold">Bidder</th>
                                        <th class="py-2 px-3 border-0 small text-uppercase font-weight-bold">Amount</th>
                                        <th class="py-2 px-3 border-0 small text-uppercase font-weight-bold text-right">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $bids = optional($payment->auction)->bids ? $payment->auction->bids->sortByDesc('amount')->values() : collect();
                                    @endphp
                                    @forelse($bids as $index => $bid)
                                    <tr class="{{ $index === 0 ? 'bg-light-primary-border' : '' }}">
                                        <td class="py-2 px-3 border-0 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-gray-200 rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold text-muted" style="width: 25px; height: 25px; font-size: 0.75rem;">
                                                    {{ substr($bid->user->name ?? '?', 0, 1) }}
                                                </div>
                                                <span class="font-weight-bold {{ $index === 0 ? 'text-primary' : 'text-dark' }}">
                                                    {{ $bid->user->name ?? 'Unknown' }}
                                                    @if($index === 0) <i class="fas fa-crown text-warning ml-1"></i> @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 border-0 align-middle font-weight-bold text-dark">
                                            ₹{{ number_format($bid->amount, 2) }}
                                        </td>
                                        <td class="py-2 px-3 border-0 align-middle text-right text-muted small">
                                            {{ $bid->created_at->format('d M, h:i A') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No bids found for this auction.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Column -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4 bg-white">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-dark mb-3">Buyer Information</h6>
                        @if($payment->user)
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                    {{ substr($payment->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">{{ $payment->user->name }}</h6>
                                    <small class="text-muted">Winner Bidder</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-uppercase x-small font-weight-bold text-muted mb-1 d-block">Email</label>
                                <span class="font-weight-bold text-dark d-block mb-3 border-bottom pb-2">{{ $payment->user->email }}</span>

                                <label class="text-uppercase x-small font-weight-bold text-muted mb-1 d-block">Phone</label>
                                <span class="font-weight-bold text-dark d-block mb-3 border-bottom pb-2">{{ $payment->user->phone ?? '---' }}</span>

                                <label class="text-uppercase x-small font-weight-bold text-muted mb-1 d-block">Address</label>
                                <span class="font-weight-bold text-dark d-block">{{ $payment->user->address ?? 'No Address Provided' }}</span>
                            </div>
                            
                            <a href="{{ route('admin.users.show', $payment->user->id) }}" class="btn btn-block btn-light border btn-sm font-weight-bold mt-4">
                                Buyer Profile
                            </a>
                        @else
                            <div class="text-center py-4 text-muted"><i class="fas fa-user-slash fa-2x mb-2"></i><br>User Info Hidden</div>
                        @endif
                    </div>
                </div>
                
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <h6 class="font-weight-bold text-dark small text-uppercase">Financial Note</h6>
                        <p class="small text-muted mb-0">The platform fee covers administrative costs and service charges for this auction.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .x-small { font-size: 0.65rem; }
    .bg-light-success { background-color: #f0fff4 !important; }
    @media print {
        .btn, .breadcrumb, nav, footer, .d-none-print { display: none !important; }
        .container-fluid { width: 100%; padding: 0; margin: 0; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; page-break-inside: avoid; }
    }
</style>
@endpush
