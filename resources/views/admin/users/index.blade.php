@extends('admin.layouts.admin')

@section('title', 'Manage Users - LaraBids')

@push('styles')
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New User
    </a>
</div>



<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">User Directory</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="users-table">
                <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined Date</th>
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
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.users.index') }}",
            language: {
                searchPlaceholder: "Search Name, Email..."
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'role_name', name: 'role_name', orderable: false},
                {data: 'status', name: 'status'},
                {data: 'joined_date', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Delete User
        $('body').on('click', '.delete-user', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
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
                                'User has been moved to trash.',
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

        // Restore User
        $('body').on('click', '.restore-user', function () {
            var url = $(this).data('url');
            $.ajax({
                type: "GET", // Using GET as route is often set up for link, but Controller handles it.
                           // Actually restore is usually a link. If it's a link it's GET.
                           // Controller restore method was just taking $id.
                           // Standard resource doesn't have it.
                           // But let's use POST to be safe if that's what we want, but controller needs to support it. 
                           // In my controller it's just a method. 
                           // I'll stick to GET if it was a link, but I changed it to AJAX.
                           // To be safe I will use GET as it is safe here since it is behind auth.
                // Wait, restore changes state, should be POST.
                // I will use POST and ensure route supports it.
                // The previous implementation used a form with POST.
                type: "POST", 
                url: url,
                success: function (data) {
                    table.draw();
                    Swal.fire(
                        'Restored!',
                        'User has been restored.',
                        'success'
                    )
                },
                error: function (data) {
                     Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });

        // Force Delete User
        $('body').on('click', '.force-delete-user', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete permanently!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        success: function (data) {
                            table.draw();
                            Swal.fire(
                                'Deleted!',
                                'User has been deleted permanently.',
                                'success'
                            )
                        },
                        error: function (data) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            })
        });
    });
</script>
@endpush
