@extends('admin.layouts.admin')

@section('title', 'View Auction - ' . $auction->title)

@push('styles')
<style>
    .image-gallery-item {
        overflow: hidden;
        border-radius: 0.35rem;
        transition: all 0.3s ease;
    }
    
    .image-gallery-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .image-gallery-item img {
        transition: transform 0.3s ease;
    }
    
    .image-gallery-item:hover img {
        transform: scale(1.05);
    }

    /* Lightbox Modal */
    .lightbox-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
    }

    .lightbox-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
    }

    /* Main Image Hover Effect */
    #mainAuctionImage:hover ~ #zoomOverlay {
        background: rgba(0, 0, 0, 0.3) !important;
    }

    #mainAuctionImage:hover ~ #zoomOverlay i {
        opacity: 1 !important;
    }
</style>
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Auction Details</h1>
        <a href="{{ route('admin.auctions.index') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $auction->title }}</h6>
                </div>
                <div class="card-body">
                    {{-- Main Image Display --}}
                    @if($auction->images && $auction->images->count() > 0)
                        @php
                            $primaryImage = $auction->images->where('is_primary', true)->first() ?? $auction->images->first();
                        @endphp
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-images mr-2"></i>Auction Images ({{ $auction->images->count() }})
                            </h6>
                            
                            {{-- Main Image Container --}}
                            <div class="position-relative overflow-hidden rounded mb-3" style="height: 400px; cursor: zoom-in; background: #f8f9fc;">
                                <img src="{{ asset('storage/' . $primaryImage->image_path) }}" 
                                     class="w-100 h-100" 
                                     style="object-fit: contain;"
                                     alt="{{ $auction->title }}"
                                     id="mainAuctionImage"
                                     onclick="openImageLightbox()">
                                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                                     style="top: 0; left: 0; background: rgba(0,0,0,0); transition: all 0.3s; pointer-events: none;"
                                     id="zoomOverlay">
                                    <i class="fas fa-search-plus fa-3x text-white" style="opacity: 0; transition: opacity 0.3s;"></i>
                                </div>
                            </div>

                            {{-- Thumbnail Slider --}}
                            @if($auction->images->count() > 1)
                                <div class="row g-2">
                                    @foreach($auction->images as $index => $image)
                                        <div class="col-2">
                                            <div class="position-relative rounded overflow-hidden border {{ $image->is_primary ? 'border-primary border-2' : 'border-light' }} shadow-sm" 
                                                 style="height: 70px; cursor: pointer; transition: all 0.2s;"
                                                 onclick="changeMainImage('{{ asset('storage/' . $image->image_path) }}', this, {{ $index }})">
                                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                     class="w-100 h-100" 
                                                     style="object-fit: cover;" 
                                                     alt="Thumbnail {{ $index + 1 }}">
                                                @if($image->is_primary)
                                                    <span class="badge badge-success position-absolute" style="top: 5px; right: 5px; font-size: 0.65rem;">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <div class="bg-light p-5 rounded">
                                <i class="fas fa-image fa-4x text-gray-300"></i>
                                <p class="text-gray-500 mt-2">No Images Uploaded</p>
                            </div>
                        </div>
                    @endif
                    <hr>
                    <h5>Description</h5>
                    <p>{{ $auction->description }}</p>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Starting Price:</strong> ${{ number_format($auction->starting_price, 2) }}</p>
                            <p><strong>Current Price:</strong> ${{ number_format($auction->current_price, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Time:</strong> {{ $auction->start_time->format('M d, Y H:i') }}</p>
                            <p><strong>End Time:</strong> {{ $auction->end_time->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bid History --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>Bid History
                    </h6>
                    <span class="badge badge-primary badge-pill">{{ $auction->bids->count() }} Bids</span>
                </div>
                <div class="card-body">
                    @if($auction->bids->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40%">Bidder</th>
                                        <th style="width: 30%">Amount</th>
                                        <th style="width: 30%">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auction->bids as $bid)
                                        <tr class="{{ $loop->first ? 'table-success' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle {{ $loop->first ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 35px; height: 35px; min-width: 35px;">
                                                        {{ strtoupper(substr($bid->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="text-truncate">
                                                        <div class="font-weight-bold text-dark">{{ $bid->user->name ?? 'Unknown User' }}</div>
                                                        <div class="small text-muted">{{ $bid->user->email ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="font-weight-bold text-dark h5 mb-0">${{ number_format($bid->amount, 2) }}</span>
                                                @if($loop->first)
                                                    <div class="small text-success font-weight-bold">
                                                        <i class="fas fa-trophy mr-1"></i> Highest Bid
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <div class="text-dark">{{ $bid->created_at->format('M d, Y h:i A') }}</div>
                                                <div class="small text-muted">{{ $bid->created_at->diffForHumans() }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-circle fa-stack-2x text-gray-200"></i>
                                    <i class="fas fa-gavel fa-stack-1x text-gray-400"></i>
                                </span>
                            </div>
                            <h5 class="text-gray-500 font-weight-bold">No Bids Yet</h5>
                            <p class="text-gray-400 mb-0">Be the first to see the action!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Auction Info</h6>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> #{{ $auction->id }}</p>
                    <p><strong>Seller:</strong> {{ $auction->user->name }} ({{ $auction->user->email }})</p>
                    <p><strong>Status:</strong> 
                        @php
                            $displayStatus = $auction->status;
                            // If auction is marked as 'active' but time has expired, show as 'closed'
                            if ($auction->status === 'active' && $auction->end_time && $auction->end_time->isPast()) {
                                $displayStatus = 'closed';
                            }
                            
                            $badgeClass = match($displayStatus) {
                                'active' => 'success',
                                'pending' => 'info',
                                'closed' => 'secondary',
                                'cancelled' => 'danger',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge badge-{{ $badgeClass }}">
                            {{ ucfirst($displayStatus) }}
                        </span>
                    </p>
                    <p><strong>Created At:</strong> {{ $auction->created_at->format('M d, Y H:i') }}</p>
                    
                    {{-- Display Cancellation Reason if Cancelled --}}
                    @if($auction->status == 'cancelled' && $auction->cancellation_reason)
                        <div class="alert alert-danger mt-3">
                            <strong>Cancellation Reason:</strong><br>
                            {{ $auction->cancellation_reason }}
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex flex-column">
                        @if($auction->trashed())
                            {{-- Restore --}}
                            <form action="{{ route('admin.auctions.restore', $auction->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-trash-restore mr-2"></i> Restore Auction
                                </button>
                            </form>
                            {{-- Permanent Delete --}}
                            <button type="button" class="btn btn-danger btn-block mb-2 trigger-force-delete" data-url="{{ route('admin.auctions.force_delete', $auction->id) }}">
                                <i class="fas fa-times mr-2"></i> Permanently Delete
                            </button>
                        @elseif($auction->status == 'closed')
                             <div class="alert alert-secondary text-center">
                                <i class="fas fa-lock mr-1"></i> Auction Closed
                             </div>
                        @else
                            {{-- Actions for Pending, Active, or Cancelled --}}
                            
                            {{-- Approve/Re-Activate Button --}}
                            {{-- Show for Pending or Cancelled --}}
                            @if($auction->status == 'pending' || $auction->status == 'cancelled')
                                <form action="{{ route('admin.auctions.approve', $auction->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check mr-2"></i> {{ $auction->status == 'cancelled' ? 'Re-Approve (Activate)' : 'Approve & Activate' }}
                                    </button>
                                </form>
                            @endif

                            {{-- Cancel Button --}}
                            {{-- Show for Pending or Active --}}
                            @if($auction->status == 'pending' || $auction->status == 'active')
                                <button type="button" class="btn btn-warning btn-block mb-2 trigger-cancel" data-url="{{ route('admin.auctions.cancel', $auction->id) }}">
                                    <i class="fas fa-ban mr-2"></i> Cancel Auction
                                </button>
                            @endif
                        @endif
                    </div>
                    <div class="alert alert-info mt-3 small">
                        <i class="fas fa-info-circle mr-1"></i> Admins cannot edit auction details created by users, but can cancel or delete them if necessary.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lightbox Modal --}}
    <div id="imageLightbox" class="lightbox-modal" style="display: none;">
        <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
        
        <div class="lightbox-controls" style="position: fixed; top: 20px; right: 20px; z-index: 10002; display: flex; gap: 15px; align-items: center;">
            <span class="lightbox-counter badge badge-dark px-3 py-2" id="lightboxCounter">1 / 1</span>
            <button class="btn btn-danger btn-sm" onclick="closeLightbox()" title="Close (Esc)">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <button class="btn btn-light" style="position: fixed; left: 20px; top: 50%; transform: translateY(-50%); z-index: 10002; width: 50px; height: 50px; border-radius: 50%;" onclick="changeLightboxImage(-1)" title="Previous" id="prevBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10001; max-width: 90%; max-height: 90%;">
            <img class="img-fluid" id="lightboxImage" style="max-height: 90vh; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
        </div>
        
        <button class="btn btn-light" style="position: fixed; right: 20px; top: 50%; transform: translateY(-50%); z-index: 10002; width: 50px; height: 50px; border-radius: 50%;" onclick="changeLightboxImage(1)" title="Next" id="nextBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Image Gallery Variables
        let currentImageIndex = 0;
        const images = [
            @if($auction->images && $auction->images->count() > 0)
                @foreach($auction->images as $index => $image)
                    "{{ asset('storage/' . $image->image_path) }}"{{ $loop->last ? '' : ',' }}
                @endforeach
            @endif
        ];

        // Initialize
        @if($auction->images && $auction->images->count() > 0)
            @php
                $primaryIndex = 0;
                foreach($auction->images as $idx => $img) {
                    if($img->is_primary) {
                        $primaryIndex = $idx;
                        break;
                    }
                }
            @endphp
            currentImageIndex = {{ $primaryIndex }};
        @endif

        // Change Main Image Function
        window.changeMainImage = function(imageSrc, element, index) {
            $('#mainAuctionImage').attr('src', imageSrc);
            currentImageIndex = index;
            
            // Update thumbnail borders
            $('.col-2 .border-primary').removeClass('border-primary border-2').addClass('border-light');
            $(element).removeClass('border-light').addClass('border-primary border-2');
        };

        // Open Lightbox
        window.openImageLightbox = function() {
            if (images.length === 0) return;
            
            $('#imageLightbox').fadeIn(300);
            $('#lightboxImage').attr('src', images[currentImageIndex]);
            updateLightboxCounter();
            $('body').css('overflow', 'hidden');
        };

        // Close Lightbox
        window.closeLightbox = function() {
            $('#imageLightbox').fadeOut(300);
            $('body').css('overflow', 'auto');
        };

        // Change Lightbox Image
        window.changeLightboxImage = function(direction) {
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            }
            
            $('#lightboxImage').attr('src', images[currentImageIndex]);
            updateLightboxCounter();
        };

        // Update Counter
        function updateLightboxCounter() {
            $('#lightboxCounter').text((currentImageIndex + 1) + ' / ' + images.length);
        }

        // Keyboard Navigation
        $(document).keydown(function(e) {
            if ($('#imageLightbox').is(':visible')) {
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowLeft') {
                    changeLightboxImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeLightboxImage(1);
                }
            }
        });

        // Cancel Confirm with Reason
        $('.trigger-cancel').click(function() {
            var url = $(this).data('url');
            
            Swal.fire({
                title: 'Cancel Auction',
                input: 'textarea',
                inputLabel: 'Reason for cancellation',
                inputPlaceholder: 'Type your reason here...',
                inputAttributes: {
                    'aria-label': 'Type your reason here'
                },
                showCancelButton: true,
                confirmButtonText: 'Submit Cancellation',
                confirmButtonColor: '#f6c23e',
                showLoaderOnConfirm: true,
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('You need to provide a reason')
                    }
                    return $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: reason
                        }
                    })
                    .then(response => {
                        return response; // Success, pass response to result
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Cancelled!',
                        text: 'Auction has been cancelled.',
                        icon: 'success'
                    }).then(() => {
                        location.reload(); // Reload to show updated status
                    });
                }
            })
        });

        // Force Delete Confirm
        $('.trigger-force-delete').click(function() {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Are you sure?',
                text: "You will not be able to recover this auction!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, permanently delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (data) {
                            Swal.fire(
                                'Deleted!',
                                'Auction has been permanently deleted.',
                                'success'
                            ).then(() => {
                                window.location.href = "{{ route('admin.auctions.index') }}";
                            });
                        },
                        error: function (data) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            )
                        }
                    });
                }
            })
        });
    });
</script>
@endpush
