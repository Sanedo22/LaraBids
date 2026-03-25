@extends('admin.layouts.admin')

@section('title', 'KYC Details - LaraBids')

@section('content')
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">KYC Verification Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kyc.index') }}">KYC Verification</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Details</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.kyc.index') }}" class="btn-premium-back text-decoration-none">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            @if($kyc->is_resubmitted && $kyc->status === 'pending')
                <div class="alert alert-warning shadow-sm mb-4" style="border-left: 4px solid #f6c23e;">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Re-submitted KYC:</strong> This user updated their KYC details after being previously approved.
                </div>
            @endif

            <!-- User Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-profile rounded-circle mb-3" width="100" height="100" style="object-fit: cover;" src="{{ $kyc->user->avatar_url }}">
                        <h5 class="font-weight-bold">{{ $kyc->user->name }}</h5>
                        <p class="text-muted small">{{ $kyc->user->email }}</p>
                    </div>
                    <hr>
                    <div class="small">
                        <p><strong>Username:</strong> {{ $kyc->user->username }}</p>
                        <p><strong>Joined:</strong> {{ $kyc->user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Verification Action -->
            @if($kyc->status == 'pending')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Take Action</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check mr-2"></i> Approve KYC
                            </button>
                        </form>
                        
                        <hr>
                        
                        <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="admin_note" class="small font-weight-bold">Rejection Reason</label>
                                <textarea name="admin_note" id="admin_note" class="form-control" rows="3" placeholder="Explain why this was rejected..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times mr-2"></i> Reject KYC
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Verification Status</h6>
                    </div>
                    <div class="card-body text-center">
                        @if($kyc->status == 'approved')
                            <div class="h3 text-success mb-0"><i class="fas fa-check-circle"></i></div>
                            <div class="text-success font-weight-bold">Approved</div>
                        @else
                            <div class="h3 text-danger mb-0"><i class="fas fa-times-circle"></i></div>
                            <div class="text-danger font-weight-bold">Rejected</div>
                            @if($kyc->admin_note)
                                <p class="small text-muted mt-2"><strong>Note:</strong> {{ $kyc->admin_note }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Submitted KYC Details</h6>
                </div>
                <div class="card-body">
                    <!-- Section 1: Personal Details -->
                    <div class="mb-4">
                        <div class="bg-light p-2 mb-3 rounded">
                            <h6 class="m-0 font-weight-bold text-dark small text-uppercase">1. Personal Information</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light ps-3" style="width: 30%;">Full Name</th>
                                        <td class="ps-3 font-weight-bold text-dark">{{ $kyc->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light ps-3">Date of Birth</th>
                                        <td class="ps-3">{{ \Carbon\Carbon::parse($kyc->date_of_birth)->format('F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light ps-3">Gender</th>
                                        <td class="ps-3 text-capitalize">{{ $kyc->gender }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light ps-3">ID Type</th>
                                        <td class="ps-3 text-capitalize">{{ str_replace('_', ' ', $kyc->id_type) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light ps-3">ID Number</th>
                                        <td class="ps-3 font-weight-bold text-primary">{{ $kyc->id_number }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Section 2: Documents -->
                    <div class="bg-light p-2 mb-3 rounded">
                        <h6 class="m-0 font-weight-bold text-dark small text-uppercase">2. Verification Documents</h6>
                    </div>

                    <hr>

                    <div class="row">
                        <!-- Identity Document -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border">
                                <div class="card-header py-2 bg-white text-center">
                                    <span class="small font-weight-bold text-muted text-uppercase">ID Card</span>
                                </div>
                                <div class="card-body p-0 d-flex align-items-center justify-content-center bg-gray-100" style="height: 160px; overflow: hidden;">
                                    @if(Str::endsWith($kyc->id_document, '.pdf'))
                                        <div class="text-center p-3">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <p class="small mb-0">PDF File</p>
                                        </div>
                                    @else
                                        <img src="{{ asset('storage/' . $kyc->id_document) }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="ID">
                                    @endif
                                </div>
                                <div class="card-footer p-2 bg-white text-center">
                                    @if(Str::endsWith($kyc->id_document, '.pdf'))
                                        <a href="{{ asset('storage/' . $kyc->id_document) }}" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open PDF
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 view-doc-btn" 
                                                data-src="{{ asset('storage/' . $kyc->id_document) }}" 
                                                data-title="Identity Document">
                                            <i class="fas fa-search-plus mr-1"></i> View Full
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Selfie -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border">
                                <div class="card-header py-2 bg-white text-center">
                                    <span class="small font-weight-bold text-muted text-uppercase">Selfie with ID</span>
                                </div>
                                <div class="card-body p-0 d-flex align-items-center justify-content-center bg-gray-100" style="height: 160px; overflow: hidden;">
                                    @if(Str::endsWith($kyc->selfie_image, '.pdf'))
                                        <div class="text-center p-3">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <p class="small mb-0">PDF File</p>
                                        </div>
                                    @else
                                        <img src="{{ asset('storage/' . $kyc->selfie_image) }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="Selfie">
                                    @endif
                                </div>
                                <div class="card-footer p-2 bg-white text-center">
                                    @if(Str::endsWith($kyc->selfie_image, '.pdf'))
                                        <a href="{{ asset('storage/' . $kyc->selfie_image) }}" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open PDF
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 view-doc-btn" 
                                                data-src="{{ asset('storage/' . $kyc->selfie_image) }}" 
                                                data-title="Selfie with ID">
                                            <i class="fas fa-search-plus mr-1"></i> View Full
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Signature -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border">
                                <div class="card-header py-2 bg-white text-center">
                                    <span class="small font-weight-bold text-muted text-uppercase">Signature</span>
                                </div>
                                <div class="card-body p-0 d-flex align-items-center justify-content-center bg-gray-100" style="height: 160px; overflow: hidden;">
                                    @if(Str::endsWith($kyc->signature_image, '.pdf'))
                                        <div class="text-center p-3">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <p class="small mb-0">PDF File</p>
                                        </div>
                                    @else
                                        <img src="{{ asset('storage/' . $kyc->signature_image) }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="Signature">
                                    @endif
                                </div>
                                <div class="card-footer p-2 bg-white text-center">
                                    @if(Str::endsWith($kyc->signature_image, '.pdf'))
                                        <a href="{{ asset('storage/' . $kyc->signature_image) }}" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open PDF
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 view-doc-btn" 
                                                data-src="{{ asset('storage/' . $kyc->signature_image) }}" 
                                                data-title="Digital Signature">
                                            <i class="fas fa-search-plus mr-1"></i> View Full
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div class="modal fade" id="imageViewerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg bg-transparent">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-white font-weight-bold" id="modalImageTitle">KYC Document</h5>
                    <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="font-size: 2rem; outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-2">
                    <img src="" id="fullViewerImage" class="img-fluid rounded shadow" style="max-height: 85vh; border: 4px solid #fff;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-gray-100 { background-color: #f8f9fc; }
    .table th { font-weight: 600; color: #4e5e7a; }
    .card-footer .btn { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .modal-backdrop.show { opacity: 0.85; background-color: #000; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-doc-btn').on('click', function() {
        const src = $(this).data('src');
        const title = $(this).data('title');
        
        $('#fullViewerImage').attr('src', src);
        $('#modalImageTitle').text(title);
        $('#imageViewerModal').modal('show');
    });
});
</script>
@endpush



