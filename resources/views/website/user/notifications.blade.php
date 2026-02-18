@extends('website.layouts.dashboard')

@section('title', 'Notifications - LaraBids')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <div class="mb-3 mb-md-0">
                <h1 class="h3 fw-bold text-dark mb-1">Notifications</h1>
                <p class="text-muted mb-0 small">Stay updated with your latest auction activities and alerts.</p>
            </div>
            
            @if($notifications->total() > 0)
                <div class="d-flex gap-2">
                    <form action="{{ route('user.notifications.read_all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-white border shadow-sm btn-sm rounded-pill px-3 fw-bold text-primary hover-scale">
                            <i class="fas fa-check-double me-2"></i>Mark all read
                        </button>
                    </form>

                    <form action="{{ route('user.notifications.clear_all') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-white border shadow-sm btn-sm rounded-pill px-3 fw-bold text-danger hover-scale">
                            <i class="fas fa-trash-alt me-2"></i>Clear all
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="d-flex flex-column gap-3">
            @forelse($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? 'default';
                    $isRead = !is_null($notification->read_at);
                    
                    // Determine Icon and Color based on Type
                    $icon = 'bell';
                    $colorClass = 'primary';
                    
                    if(str_contains($type, 'auction')) {
                        $icon = 'gavel';
                        $colorClass = 'indigo'; // Custom color class or use primary
                    }
                    if(str_contains($type, 'bid')) {
                        $icon = 'hand-holding-usd';
                        $colorClass = 'success';
                    }
                    if(str_contains($type, 'outbid')) {
                        $icon = 'arrow-down';
                        $colorClass = 'warning';
                    }
                    if(str_contains($type, 'win')) {
                        $icon = 'trophy';
                        $colorClass = 'warning';
                    }
                    if(str_contains($type, 'payment')) {
                        $icon = 'credit-card';
                        $colorClass = 'info';
                    }
                    if(str_contains($type, 'cancelled') || str_contains($type, 'error')) {
                        $icon = 'exclamation-circle';
                        $colorClass = 'danger';
                    }
                @endphp

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-hover {{ $isRead ? 'bg-white opacity-75' : 'bg-white border-start border-4 border-' . $colorClass }}">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            
                            <!-- Icon Column -->
                            <div class="col-auto">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                     style="width: 50px; height: 50px; background-color: var(--bs-{{ $colorClass }}-bg-subtle, #f8f9fa); color: var(--bs-{{ $colorClass }}, #0d6efd);">
                                    <i class="fas fa-{{ $icon }} fa-lg"></i>
                                </div>
                            </div>

                            <!-- Content Column -->
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        {{ $notification->data['title'] ?? 'New Notification' }}
                                        @if(!$isRead)
                                            <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.6rem; vertical-align: middle;">NEW</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted fw-medium" style="font-size: 0.8rem;">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                
                                <p class="text-muted mb-2 small text-truncate-2">{{ $notification->data['message'] ?? 'You have a new update.' }}</p>

                                @if(isset($notification->data['reason']))
                                    <div class="bg-light p-2 rounded-3 border mb-2">
                                        <small class="text-secondary d-block">
                                            <i class="fas fa-info-circle me-1"></i> {{ $notification->data['reason'] }}
                                        </small>
                                    </div>
                                @endif

                                @if(isset($notification->data['auction_id']))
                                    <a href="{{ route('auctions.show', $notification->data['auction_id']) }}" class="btn btn-link p-0 text-decoration-none small fw-bold text-primary">
                                        View Auction <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                @endif
                                
                                @if(isset($notification->data['action_url']) && !isset($notification->data['auction_id']))
                                     <a href="{{ $notification->data['action_url'] }}" class="btn btn-link p-0 text-decoration-none small fw-bold text-primary">
                                        View Details <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                @endif
                            </div>

                            <!-- Actions Column -->
                            <div class="col-auto border-start ps-3 d-flex flex-column gap-2 justify-content-center">
                                @if(!$isRead)
                                    <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-light btn-sm rounded-circle text-success" data-bs-toggle="tooltip" title="Mark as Read" style="width: 32px; height: 32px;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('user.notifications.destroy', $notification->id) }}" method="POST" class="delete-notification-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-circle text-muted hover-danger" data-bs-toggle="tooltip" title="Delete" style="width: 32px; height: 32px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 100px; height: 100px;">
                            <i class="far fa-bell fa-3x text-secondary opacity-25"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark">No Notifications</h4>
                    <p class="text-muted text-center mx-auto" style="max-width: 300px;">
                        You're all caught up! We'll notify you when something important happens.
                    </p>
                    <a href="{{ route('auctions.index') }}" class="btn btn-primary rounded-pill px-4 mt-3 fw-bold shadow-sm">
                        Browse Auctions
                    </a>
                </div>
            @endforelse
        </div>

    @if($notifications->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Clear All Confirmation
    const clearAllForm = document.querySelector('form[action="{{ route('user.notifications.clear_all') }}"]');
    if (clearAllForm) {
        clearAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! All notifications will be deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, clear all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    }

    // Single Delete Confirmation
    const deleteForms = document.querySelectorAll('.delete-notification-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete Notification?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                width: '350px' // Smaller width for single item
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

});
</script>
@endpush

<style>
.hover-scale:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}
.transition-hover {
    transition: all 0.2s ease-in-out;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
}
.hover-danger:hover {
    color: #dc3545 !important;
    background-color: #f8d7da !important;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

@endsection
