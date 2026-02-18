@extends('admin.layouts.admin')

@section('title', 'Manage Auctions - LaraBids')

@push('styles')
    <link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Auctions</h1>
    </div>



    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Auctions ({{ $total_auctions }})</h6>
            <div class="form-inline">
                <label for="statusFilter" class="mr-2 mb-0">Filter by Status:</label>
                <select id="statusFilter" class="form-control form-control-sm">
                    <option value="all" selected>All</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-2 text-muted small">
                <i class="fas fa-info-circle"></i> Search by Title, Cancel Reason, Category, or User Name.
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="auctions-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="30">#</th>
                            <th width="60">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>User</th>
                            <th class="text-nowrap">Current Price</th>
                            <th>Status</th>
                            <th>End Time</th>
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
    <!-- Page level plugins -->
    <script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function () {
            // Setup CSRF token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var currentStatus = 'all';

            var table = $('#auctions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.auctions.index') }}",
                    data: function (d) {
                        d.status = currentStatus;
                    }
                },
                language: {
                    searchPlaceholder: "Search Title, User, Category..."
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                    {data: 'title', name: 'title'},
                    {data: 'category', name: 'category.name'},
                    {data: 'user', name: 'user.name'}, // Ensure this matches controller
                    {data: 'current_price', name: 'current_price'},
                    {data: 'status', name: 'status'},
                    {data: 'end_time', name: 'end_time'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            // Status Filter Dropdown Change Handler
            $('#statusFilter').on('change', function() {
                currentStatus = $(this).val();
                table.draw();
            });

            // Cancel Auction Removed - Only in View now

            // Delete Auction
            $('body').on('click', '.delete-auction', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Move to trash?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            success: function (data) {
                                table.draw();
                                Swal.fire(
                                    'Deleted!',
                                    'Auction has been moved to trash.',
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

            // Restore Auction
            $('body').on('click', '.restore-auction', function () {
                var url = $(this).data('url');
                $.ajax({
                    type: "POST",
                    url: url,
                    success: function (data) {
                        table.draw();
                        Swal.fire(
                            'Restored!',
                            'Auction has been restored.',
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
            });

            // Force Delete Auction
            $('body').on('click', '.force-delete-auction', function () {
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
                            success: function (data) {
                                table.draw();
                                Swal.fire(
                                    'Deleted!',
                                    'Auction has been permanently deleted.',
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
        });
    </script>
@endpush
