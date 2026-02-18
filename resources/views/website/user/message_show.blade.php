@extends('website.layouts.dashboard')

@section('title', 'View Message | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">Message Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Message</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <!-- Message Card -->
    <div class="col-lg-10">
        <div class="card card-elite border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $contact->status == 'replied' ? 'success' : 'primary' }} rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        {{ ucfirst($contact->status) }}
                    </span>
                    <span class="text-muted small">
                        <i class="far fa-clock me-1"></i> {{ $contact->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                
                <!-- Subject -->
                <div class="mb-4">
                    <label class="text-uppercase text-muted small fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">Subject</label>
                    <h4 class="text-dark fw-bold">{{ $contact->subject }}</h4>
                </div>

                <!-- Your Message -->
                <div class="mb-5">
                    <label class="text-uppercase text-muted small fw-bold mb-2 pb-1 border-bottom d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Your Message</label>
                    <div class="bg-light p-3 rounded text-dark lh-lg">
                        {{ $contact->message }}
                    </div>
                </div>

                <!-- Admin Reply -->
                @if($contact->status == 'replied' && $contact->admin_notes)
                <div class="mb-2">
                    <label class="text-uppercase text-primary small fw-bold mb-2 pb-1 border-bottom border-primary d-inline-block" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="fas fa-reply me-1"></i> Admin Reply
                    </label>
                    <div class="alert alert-info border-0 shadow-sm rounded-3 p-4">
                        <div class="d-flex">
                            <i class="fas fa-info-circle text-info fs-4 me-3 mt-1"></i>
                            <div class="text-dark lh-base">
                                {{ $contact->admin_notes }}
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <small class="text-muted fst-italic">Replied {{ $contact->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5 bg-light rounded border border-dashed">
                    <i class="fas fa-hourglass-half text-secondary fs-2 mb-3 opacity-50"></i>
                    <p class="text-muted mb-0">Our team is reviewing your query. You will be notified once we reply.</p>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection
