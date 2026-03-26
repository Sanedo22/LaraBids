@extends('admin.layouts.admin')

@section('title', 'User Details - LaraBids')

@push('styles')
<style>
    .avatar-lg {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid #fff;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid #f1f1f1;
        padding: 1.25rem;
    }
    .label-muted {
        color: #888;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 2px;
    }
    .info-value {
        color: #333;
        font-weight: 600;
        font-size: 1rem;
    }
    .stat-box {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: all 0.2s;
        border: 1px solid #edf2f9;
        height: 100%;
    }
    .stat-box:hover {
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-color: #4e73df;
    }
    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
    }
    .stat-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #4e73df;
        display: block;
    }
    .badge-status {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">User Details: {{ $user->name }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Details</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.users.index') }}" class="btn-premium-back text-decoration-none mr-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to List
                </a>
                @if($user->trashed())
                   <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success shadow-sm rounded-pill px-4 font-weight-bold">
                            <i class="fas fa-undo mr-1"></i> Restore
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary shadow-sm rounded-pill px-4 font-weight-bold">
                        <i class="fas fa-edit mr-1"></i> Edit User
                    </a>
                @endif
            </div>
        </div>

        @php $stats = $user->getStatistics(); @endphp

        <div class="row">
            <!-- Sidebar / Profile Info -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $user->avatar_url }}" class="rounded-circle shadow-sm avatar-lg" alt="{{ $user->name }}">
                            @if(!$user->trashed())
                                <span class="position-absolute border border-white rounded-circle bg-success shadow-sm" style="bottom: 10px; right: 10px; width: 18px; height: 18px;"></span>
                            @else
                                <span class="position-absolute border border-white rounded-circle bg-danger shadow-sm" style="bottom: 10px; right: 10px; width: 18px; height: 18px;"></span>
                            @endif
                        </div>
                        <h4 class="font-weight-bold text-dark mb-1">{{ $user->name }}</h4>
                        <p class="text-primary font-weight-bold small">@_{{ $user->username }}</p>
                        
                        <div class="d-flex justify-content-center flex-wrap mt-3">
                            @foreach($user->roles as $role)
                                <span class="badge badge-light border text-dark font-weight-bold px-3 py-2 mx-1 mb-2" style="border-radius: 8px;">
                                    <i class="fas fa-shield-alt mr-1 text-primary"></i> {{ strtoupper($role->name) }}
                                </span>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <div class="row">
                                <div class="col-6 border-right">
                                    <span class="label-muted">Joined</span>
                                    <span class="info-value" style="font-size: 0.9rem;">{{ $user->created_at->format('M Y') }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="label-muted">Status</span>
                                    @if($user->trashed())
                                        <span class="badge border border-danger text-danger badge-status">SUSPENDED</span>
                                    @else
                                        <span class="badge border border-success text-success badge-status">ACTIVE</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Box -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-dark">Activity Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 mb-3">
                                <div class="stat-box">
                                    <span class="stat-label">Auctions</span>
                                    <span class="stat-value">{{ $stats['auctions_created'] }}</span>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stat-box">
                                    <span class="stat-label">Bids</span>
                                    <span class="stat-value">{{ $stats['total_bids'] }}</span>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stat-box">
                                    <span class="stat-label">Wins</span>
                                    <span class="stat-value text-success">{{ $stats['items_won'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">Account Information</h6>
                        @if($user->isKycApproved())
                            <span class="badge badge-pill badge-success-soft text-success px-3 py-2 small font-weight-bold" style="background: rgba(28, 200, 138, 0.1);">
                                <i class="fas fa-check-circle mr-1"></i> VERIFIED
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">Full Display Name</span>
                                <span class="info-value">{{ $user->name }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">Email Address</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">System Username</span>
                                <span class="info-value text-primary">@_{{ $user->username }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">Phone Number</span>
                                <span class="info-value">{{ $user->phone ?? 'Not Linked' }}</span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">Primary Location</span>
                                <span class="info-value">
                                    <i class="fas fa-map-marker-alt text-muted mr-1"></i> 
                                    {{ $user->location ?? 'Not specified' }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="label-muted">Registration Date</span>
                                <span class="info-value">{{ $user->created_at->format('F d, Y') }} at {{ $user->created_at->format('h:i A') }}</span>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12">
                                <span class="label-muted">Biography / About</span>
                                <p class="mt-2 text-dark font-italic" style="background-color: #f8f9fc; padding: 15px; border-radius: 8px; border-left: 3px solid #e3e6f0;">
                                    {!! !empty($user->bio) ? nl2br(e($user->bio)) : '<span class="text-muted">No biography provided by user.</span>' !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->trashed())
                <div class="card border-0 shadow-sm bg-danger text-white mb-4">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 font-weight-bold"><i class="fas fa-trash-alt mr-2"></i> ACCOUNT SUSPENDED</h6>
                                <p class="mb-0 small opacity-75">Deleted on {{ $user->deleted_at->format('M d, Y') }} by {{ $user->deletedBy ? $user->deletedBy->name : 'Administrator' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-dark">Recent Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Watchlist Count</td>
                                        <td class="text-right font-weight-bold">{{ $stats['watchlist_count'] }} items</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Active Auctions</td>
                                        <td class="text-right font-weight-bold">{{ $stats['active_auctions'] }} active</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Last Profile Update</td>
                                        <td class="text-right font-weight-bold">{{ $user->updated_at->diffForHumans() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Strikes Card -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">Unpaid Item Strikes</h6>
                        <span class="badge badge-pill {{ $user->unpaid_strikes_count > 0 ? 'badge-danger' : 'badge-success' }} px-3 py-2 small font-weight-bold">
                            {{ $user->unpaid_strikes_count }} Strikes
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if($user->strikes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-top-0 px-4 py-3 text-muted font-weight-bold text-uppercase" style="font-size: 0.75rem;">Auction</th>
                                            <th class="border-top-0 px-4 py-3 text-muted font-weight-bold text-uppercase" style="font-size: 0.75rem;">Date</th>
                                            <th class="border-top-0 px-4 py-3 text-muted font-weight-bold text-uppercase text-right" style="font-size: 0.75rem;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->strikes as $strike)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark">
                                                            @if($strike->auction)
                                                                <a href="{{ route('admin.auctions.show', $strike->auction->id) }}" class="text-dark">{{ Str::limit($strike->auction->title, 40) }}</a>
                                                            @else
                                                                Auction Deleted
                                                            @endif
                                                        </h6>
                                                        <small class="text-muted">Reported by: {{ $strike->reporter->name ?? 'System' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-dark font-weight-bold d-block" style="font-size: 0.9rem;">{{ $strike->created_at->format('M d, Y') }}</span>
                                                <span class="text-muted small">{{ $strike->created_at->format('h:i A') }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form action="{{ route('admin.users.remove_strike', ['user' => $user->id, 'strike' => $strike->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this strike? Only remove it if it was issued in error or resolved.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold px-3 py-1 rounded-pill" title="Remove Strike">
                                                        <i class="fas fa-times mr-1"></i> Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-5 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-shield-check text-success" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Clean Record</h6>
                                <p class="text-muted small mb-0">This user does not have any unpaid item strikes.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection





