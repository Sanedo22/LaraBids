@extends('admin.layouts.admin')

@section('title', 'View Contact Message - LaraBids')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Contact Message Details</h1>
    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Back to List
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i> {{ session('info') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Message Details -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Message Information</h6>
                @if($contact->trashed())
                    <span class="badge badge-secondary"><i class="fas fa-trash"></i> Deleted</span>
                @elseif($contact->status === 'unread')
                    <span class="badge badge-warning">Unread</span>
                @elseif($contact->status === 'read')
                    <span class="badge badge-info">Read</span>
                @else
                    <span class="badge badge-success">Replied</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong class="text-gray-800">From:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong class="text-gray-800">Email:</strong>
                    </div>
                    <div class="col-sm-9">
                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong class="text-gray-800">Subject:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->subject }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong class="text-gray-800">Received:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                @if($contact->trashed())
                <div class="row mb-3 bg-danger text-white p-2 rounded">
                    <div class="col-sm-3">
                        <strong>Deleted At:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->deleted_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                <div class="row mb-3 bg-danger text-white p-2 rounded">
                    <div class="col-sm-3">
                        <strong>Deleted By:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->deletedBy ? $contact->deletedBy->name : 'Unknown' }}
                    </div>
                </div>
                @endif
                
                @if($contact->status === 'replied' && $contact->repliedBy)
                <div class="row mb-3 bg-success text-white p-2 rounded">
                    <div class="col-sm-3">
                        <strong>Replied By:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $contact->repliedBy->name }}
                    </div>
                </div>
                @endif
                
                <hr>
                <div class="mb-3">
                    <strong class="text-gray-800 d-block mb-2">Message:</strong>
                    <div class="p-3 bg-light rounded border">
                        {{ $contact->message }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Actions -->
    <div class="col-lg-4">
        <!-- Update Status -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Update Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.contacts.update', $contact->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Status</label>
                        <select name="status" id="status" class="form-control" readonly disabled>
                            <option value="unread" {{ $contact->status === 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ $contact->status === 'read' ? 'selected' : '' }}>Read</option>
                            <option value="replied" {{ $contact->status === 'replied' ? 'selected' : '' }}>Replied</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Status updates automatically
                        </small>
                        <!-- Hidden input to submit status -->
                        <input type="hidden" name="status" id="status_hidden" value="{{ $contact->status }}">
                    </div>

                    <div class="form-group">
                        <label for="admin_notes" class="font-weight-bold">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="4" class="form-control" 
                            placeholder="Add internal notes...">{{ $contact->admin_notes ?: 'We received your query regarding "' . \Illuminate\Support\Str::limit($contact->subject, 20) . '". Our team has reviewed it and will contact you shortly.' }}</textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb"></i> Adding notes will mark as "Replied"
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($contact->trashed())
                    <!-- Restore Button -->
                    <button type="button" class="btn btn-success btn-block mb-2 restore-contact" 
                        data-url="{{ route('admin.contacts.restore', $contact->id) }}">
                        <i class="fas fa-trash-restore"></i> Restore Message
                    </button>
                @else
                    <!-- Reply via Email -->
                    <a href="mailto:{{ $contact->email }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-reply"></i> Reply via Email
                    </a>
                    <!-- Soft Delete Button -->
                    <button type="button" class="btn btn-danger btn-block delete-contact" 
                        data-url="{{ route('admin.contacts.destroy', $contact->id) }}">
                        <i class="fas fa-trash"></i> Soft Delete Message
                    </button>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> Soft deleted messages can be restored later
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Reply via Email - Auto update status to 'replied'
        $('a[href^="mailto:"]').on('click', function(e) {
            // Update status to replied via AJAX
            $.ajax({
                type: "PUT",
                url: "{{ route('admin.contacts.update', $contact->id) }}",
                data: {
                    status: 'replied',
                    admin_notes: $('#admin_notes').val()
                },
                success: function(data) {
                    // Status updated silently
                    $('#status').val('replied');
                    $('#status_hidden').val('replied');
                    // Update badge
                    $('.badge').removeClass('badge-warning badge-info').addClass('badge-success').text('Replied');
                }
            });
        });

        // Form submission - Auto set status based on notes
        $('form').on('submit', function(e) {
            var currentStatus = $('#status_hidden').val();
            var adminNotes = $('#admin_notes').val().trim();
            
            // If admin has written notes, set status to 'replied'
            if (adminNotes !== '' && currentStatus !== 'replied') {
                $('#status').val('replied');
                $('#status_hidden').val('replied');
            }
            // If no notes and status is unread, set to 'read'
            else if (adminNotes === '' && currentStatus === 'unread') {
                $('#status').val('read');
                $('#status_hidden').val('read');
            }
        });

        // Delete Contact (Soft Delete)
        $('.delete-contact').on('click', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Soft Delete Message?',
                text: "This message will be soft deleted and can be restored later.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, soft delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        success: function (data) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Soft Deleted!',
                                text: 'Contact message has been soft deleted. You can restore it later if needed.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route("admin.contacts.index") }}';
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

        // Restore Contact
        $('.restore-contact').on('click', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Restore Message?',
                text: "This message will be restored and visible again.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: data.message || 'Contact message has been restored successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route("admin.contacts.index") }}';
                            });
                        },
                        error: function (xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong: ' + error,
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
