@extends('admin.layouts.admin')

@section('title', 'Manage Users - LaraBids')

@push('styles')
    <link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .filter-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .filter-control {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #1a202c;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
        }
        .filter-control:focus {
            background-color: #fff;
            border-color: #a3bffa;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
            outline: none;
        }
        #users-table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            border-top: none;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
        }
        #users-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 0.4rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #a3bffa;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }
        .btn-reset-filter {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            background-color: #fff;
            color: #4a5568;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-reset-filter:hover {
            background-color: #f1f5f9;
            color: #1a202c;
            border-color: #cbd5e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            line-height: 32px;
            text-align: center;
            border-radius: 0.35rem;
            display: inline-block;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary shadow-sm" style="border-radius: 50px;">
            <i class="fas fa-plus-circle mr-1"></i> Add New User
        </a>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <div class="row align-items-end">
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="roleFilter" class="filter-label"><i class="fas fa-user-shield mr-1"></i> Role</label>
                    <select id="roleFilter" class="custom-select filter-control w-100">
                        <option value="" selected>All Roles</option>
                        <option value="super-admin">Super Admin</option>
                        <option value="admin">Administrator</option>
                        <option value="user_only">Standard User</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="statusFilter" class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Status</label>
                    <select id="statusFilter" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="active">Active</option>
                        <option value="deleted">Suspended / Deleted</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="sortFilter" class="filter-label"><i class="fas fa-sort-amount-down-alt mr-1"></i> Sort By</label>
                    <select id="sortFilter" class="custom-select filter-control w-100">
                        <option value="latest" selected>Latest Joined</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
                
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="startDateFilter" class="filter-label"><i class="far fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" id="startDateFilter" class="form-control filter-control w-100">
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="endDateFilter" class="filter-label"><i class="far fa-calendar-alt mr-1"></i> End Date</label>
                    <input type="date" id="endDateFilter" class="form-control filter-control w-100">
                </div>
                
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 mb-3">
                    <button type="button" class="btn btn-light border w-100 font-weight-bold" id="resetFilters" style="height: calc(1.5em + .75rem + 2px);">
                        <i class="fas fa-sync-alt mr-1 text-primary"></i> <span class="text-primary">Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Directory Card -->
    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-header py-3 px-4 bg-white d-flex align-items-center justify-content-between" style="min-height: 60px;">
            <h6 class="m-0 font-weight-bold text-secondary">
                <i class="fas fa-id-badge mr-2 text-primary"></i>User Directory
            </h6>
            
            <!-- Bulk Actions (Injected inside Directory Header) -->
            <div id="bulkActionsContainer" style="display: none;">
                <div class="d-flex align-items-center">
                    
                    <div class="d-flex align-items-center mr-4">
                        <span class="text-primary font-weight-bold mr-2 text-nowrap" style="font-size: 0.9rem;">
                            <i class="fas fa-check-square mr-1"></i><span id="selectedCount">0</span> Selected
                        </span>
                        <button type="button" id="clearSelection" class="btn btn-outline-danger btn-action" title="Clear Selection">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <select id="bulkActionSelect" class="custom-select custom-select-sm border-primary text-primary mr-2 shadow-sm" style="width: 200px;">
                            <!-- Options populated via JS -->
                        </select>
                        <button type="button" id="applyBulkAction" class="btn btn-outline-primary btn-action" title="Apply Action">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="users-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="30" class="text-center pr-1"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="selectAll"><label class="custom-control-label" for="selectAll"></label></div></th>
                            <th width="30">Id</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                            <th>Joined Date</th>
                            <th width="150" class="text-center text-nowrap">Actions</th>
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
    $(document).ready(function () {
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Filter Variables
        var currentRole = '';
        var currentStatus = 'all';
        var currentSort = 'latest';
        var currentStartDate = '';
        var currentEndDate = '';

        // Initialize DataTable
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.users.index') }}",
                data: function (d) {
                    d.role = currentRole;
                    d.status = currentStatus;
                    d.sort = currentSort;
                    d.start_date = currentStartDate;
                    d.end_date = currentEndDate;
                }
            },
            language: {
                search: "Search:",
                searchPlaceholder: "Name, Email or Phone...",
                lengthMenu: "Entries per page: _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ users"
            },
            columns: [
                {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted text-center'},
                {data: 'name', name: 'name', className: 'font-weight-bold text-dark'},
                {data: 'email', name: 'email', className: 'text-muted'},
                {data: 'role_name', name: 'role_name', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center'},
                {data: 'joined_date', name: 'created_at', className: 'text-muted small'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap'},
            ],
            order: [], // Delegate default sorting to the backend filter
            drawCallback: function() {
                // Style table buttons on draw completion
                $('#users-table .btn-info').removeClass('btn-info').addClass('btn-outline-info').html('<i class="fas fa-eye"></i>');
                $('#users-table .btn-primary').removeClass('btn-primary').addClass('btn-outline-primary').html('<i class="fas fa-edit"></i>');
                $('#users-table .btn-success:not(.restore-user)').removeClass('btn-success').addClass('btn-outline-success').html('<i class="fas fa-check"></i>');
                $('#users-table .btn-danger:not(.force-delete-user)').removeClass('btn-danger').addClass('btn-outline-danger').html('<i class="fas fa-trash"></i>');
                $('#users-table .restore-user').removeClass('btn-success').addClass('btn-outline-success').html('<i class="fas fa-trash-restore"></i>');
                $('#users-table .force-delete-user').removeClass('btn-danger').addClass('btn-outline-danger').html('<i class="fas fa-times"></i>');

                $('#users-table .btn-sm').addClass('btn-action mx-1');
            }
        });

        // Trigger filters on change
        $('#roleFilter, #statusFilter, #sortFilter, #startDateFilter, #endDateFilter').on('change', function() {
            currentRole = $('#roleFilter').val();
            currentStatus = $('#statusFilter').val();
            currentSort = $('#sortFilter').val();
            currentStartDate = $('#startDateFilter').val();
            currentEndDate = $('#endDateFilter').val();
            table.draw();
        });

        // Reset Filters Button
        $('#resetFilters').on('click', function() {
            $('#roleFilter').val('');
            $('#statusFilter').val('all');
            $('#sortFilter').val('latest');
            $('#startDateFilter').val('');
            $('#endDateFilter').val('');
            
            // Also clear datatables search
            table.search('');
            
            currentRole = '';
            currentStatus = 'all';
            currentSort = 'latest';
            currentStartDate = '';
            currentEndDate = '';
            
            table.draw();
        });

        // Delete User
        $('body').on('click', '.delete-user', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Suspend User?',
                text: "User won't be able to login, but data will be kept.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, Suspend!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        success: function (data) {
                            table.draw();
                            Swal.fire('Suspended!', 'User has been moved to trash / suspended.', 'success');
                        },
                        error: function (data) {
                            Swal.fire('Error!', data.responseJSON?.error || 'Something went wrong.', 'error');
                        }
                    });
                }
            })
        });

        // Restore User
        $('body').on('click', '.restore-user', function () {
            var url = $(this).data('url');
            $.ajax({
                type: "POST", 
                url: url,
                success: function (data) {
                    table.draw();
                    Swal.fire('Restored!', 'User has been reactivated.', 'success');
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
                title: 'Permanent Warning',
                text: "This user will be permanently deleted from database!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, Destroy Completely!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        success: function (data) {
                            table.draw();
                            Swal.fire('Destroyed!', 'User has been deleted permanently.', 'success');
                        },
                        error: function (data) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            })
        });

        // Clear selection button
        $(document).on('click', '#clearSelection', function() {
            $('.user-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            toggleBulkActions();
        });

        // Bulk Actions Logic
        $(document).on('change', '#selectAll', function() {
            $('.user-checkbox').prop('checked', this.checked);
            toggleBulkActions();
        });

        $(document).on('change', '.user-checkbox', function() {
            if ($('.user-checkbox:checked').length == $('.user-checkbox').length && $('.user-checkbox').length > 0) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
            toggleBulkActions();
        });

        table.on('draw', function() {
            $('#selectAll').prop('checked', false);
            toggleBulkActions();
        });

        function toggleBulkActions() {
            var checkedCheckboxes = $('.user-checkbox:checked');
            var checkedCount = checkedCheckboxes.length;
            
            if (checkedCount > 0) {
                var anyTrashed = false;
                var anyActive = false;
                
                checkedCheckboxes.each(function() {
                    if ($(this).attr('data-is-trashed') == '1') {
                        anyTrashed = true;
                    } else {
                        anyActive = true;
                    }
                });
                
                var options = '<option value="" selected disabled hidden>Choose Action...</option>';
                
                if (anyTrashed && !anyActive) {
                    options += '<option value="restore">Restore Selected</option>';
                    options += '<option value="force_delete">Delete Permanently</option>';
                } else if (!anyTrashed && anyActive) {
                    options += '<option value="delete">Suspend / Block Selected</option>';
                } else {
                    options += '<option value="" disabled>Cannot mix Active & Suspended</option>';
                }
                
                $('#bulkActionSelect').html(options);
                $('#selectedCount').text(checkedCount);
                $('#bulkActionsContainer').fadeIn(200);
            } else {
                $('#bulkActionsContainer').fadeOut(200);
            }
        }

        $('#applyBulkAction').on('click', function() {
            var action = $('#bulkActionSelect').val();
            if (!action) {
                Swal.fire('Warning', 'Please select an action first.', 'warning');
                return;
            }

            var selectedIds = [];
            $('.user-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to perform this action on ' + selectedIds.length + ' user(s).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.users.bulk_action') }}",
                        type: 'POST',
                        data: {
                            ids: selectedIds,
                            action: action
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success');
                                table.draw();
                                $('#selectAll').prop('checked', false);
                                $('#bulkActionsContainer').fadeOut();
                                $('#bulkActionSelect').val('');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush



