@extends('admin.layouts.admin')

@section('title', 'Contact Messages - LaraBids')

@push('styles')
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Contact Messages</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Contact Messages</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="contacts-table">
                <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th width="150">Actions</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>
    $(function () {
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable
        var table = $('#contacts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.contacts.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'subject', name: 'subject'},
                {data: 'status_badge', name: 'status'},
                {data: 'created_at_formatted', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[5, 'desc']]
        });


        // Delete Contact (Soft Delete)
        $('body').on('click', '.delete-contact', function () {
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
                            table.draw();
                            Swal.fire(
                                'Soft Deleted!',
                                'Contact message has been soft deleted.',
                                'success'
                            )
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
        $('body').on('click', '.restore-contact', function (e) {
            e.preventDefault(); // Prevent default action
            var url = $(this).data('url');
            
            console.log('Restore button clicked, URL:', url); // Debug log
            
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
                            console.log('Restore success:', data); // Debug log
                            table.draw();
                            Swal.fire(
                                'Restored!',
                                data.message || 'Contact message has been restored.',
                                'success'
                            )
                        },
                        error: function (xhr, status, error) {
                            console.error('Restore error:', xhr.responseText); // Debug log
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
