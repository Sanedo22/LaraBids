@extends('website.layouts.dashboard')

@section('title', 'Notifications | LaraBids')

@section('content')

<!-- Notifications Header -->
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <span class="text-primary fw-bold small text-uppercase mb-1 d-block">Notifications</span>
        <h1 class="h3 text-dark fw-bold mb-0">All Alerts</h1>
    </div>
    @if($notifications->total() > 0)
        <form action="{{ route('user.notifications.read_all') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </button>
        </form>
    @endif
</div>

<!-- Notifications List -->
<div class="card card-elite p-0 overflow-hidden shadow-sm">
    <div class="card-header bg-white py-3 border-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-primary fw-bold h6 mb-0">Recent Notifications</h5>
            <span class="badge bg-primary rounded-pill">{{ $notifications->total() }} Total</span>
        </div>
    </div>

    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            <div class="list-group-item list-group-item-action {{ $notification->read_at ? 'bg-light bg-opacity-25' : 'bg-primary bg-opacity-5' }}">
                <div class="d-flex w-100 align-items-start justify-content-between">
                    <div class="d-flex align-items-start flex-grow-1">
                        <!-- Icon -->
                        <div class="me-3 mt-1">
                            <div class="bg-{{ isset($notification->data['type']) && $notification->data['type'] === 'auction_cancelled' ? 'danger' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px;">
                                <i class="fas fa-{{ isset($notification->data['type']) && $notification->data['type'] === 'auction_cancelled' ? 'ban' : 'bell' }} fa-lg"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-1 fw-bold">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                    @if(!$notification->read_at)
                                        <span class="badge bg-primary badge-sm ms-2">New</span>
                                    @endif
                                </h6>
                            </div>
                            <p class="mb-1 text-secondary">{{ $notification->data['message'] ?? '' }}</p>
                            @if(isset($notification->data['reason']) && $notification->data['reason'])
                                <div class="alert alert-light border mt-1 mb-2 p-2 small">
                                    <strong>Reason:</strong> {{ $notification->data['reason'] }}
                                </div>
                            @endif
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="ms-3 d-flex gap-2 flex-nowrap">
                        @if(!$notification->read_at)
                            <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Read">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('user.notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete" 
                                    onclick="return confirm('Are you sure you want to delete this notification?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- View Auction Link (if applicable) -->
                @if(isset($notification->data['auction_id']))
                    <div class="mt-3 ms-5 ps-3">
                        <a href="{{ route('auctions.show', $notification->data['auction_id']) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> View Auction
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x mb-3 d-block text-gray-300 opacity-25"></i>
                <span class="text-secondary">No notifications yet.</span>
                <div class="mt-3">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary btn-sm px-4">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-light py-3">
            <div class="d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        </div>
    @endif
</div>

@endsection
