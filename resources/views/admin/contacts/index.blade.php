@extends('admin.layouts.admin')

@section('title', 'Contact Messages - LaraBids')

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
        }
        #contacts-table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            border-top: none;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
        }
        #contacts-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            line-height: 32px;
            text-align: center;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        .badge-pill-custom {
            font-size: 0.78rem;
            padding: 0.35em 0.75em;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

    </style>
@endpush

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Contact Messages</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact Messages</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <!-- Total -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Messages</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_contacts }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-inbox fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unread -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unread</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $unread_contacts }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-envelope fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Read -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Read</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $read_contacts }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-envelope-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replied -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Replied</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $replied_contacts }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-reply fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body py-3 px-4">
            <div class="row align-items-end">
                <!-- Status Filter -->
                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-6 mb-3">
                    <label for="statusFilter" class="filter-label">
                        <i class="fas fa-circle-notch mr-1 text-primary"></i> Status
                    </label>
                    <select id="statusFilter" class="form-control filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-6 mb-3">
                    <label for="sortFilter" class="filter-label">
                        <i class="fas fa-sort-amount-down-alt mr-1 text-primary"></i> Sort By
                    </label>
                    <select id="sortFilter" class="form-control filter-control w-100">
                        <option value="latest" selected>Latest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="col-xl-2 col-lg-2 col-md-5 col-sm-6 mb-3">
                    <label for="startDateFilter" class="filter-label">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> Start Date
                    </label>
                    <input type="date" id="startDateFilter" class="form-control filter-control w-100">
                </div>
                
                <div class="col-xl-2 col-lg-2 col-md-5 col-sm-6 mb-3">
                    <label for="endDateFilter" class="filter-label">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> End Date
                    </label>
                    <input type="date" id="endDateFilter" class="form-control filter-control w-100">
                </div>

                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 mb-3">
                    <button type="button" class="btn btn-light border w-100 font-weight-bold" id="resetFilters" style="height: calc(1.5em + .75rem + 2px);">
                        <i class="fas fa-sync-alt mr-1 text-primary"></i> <span class="text-primary">Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-list mr-2 text-primary"></i>All Messages
            </h6>
            @if($unread_contacts > 0)
                <span class="badge badge-warning badge-pill">{{ $unread_contacts }} Unread</span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="px-3 py-4">
                <table class="table table-hover" id="contacts-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th class="text-center">Status</th>
                            <th>Received</th>
                            <th width="100" class="text-center">Actions</th>
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
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            var currentStatus = 'all';
            var currentSort   = 'latest';
            var currentStartDate = '';
            var currentEndDate = '';

            var table = $('#contacts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.contacts.index') }}",
                    data: function (d) {
                        d.status = currentStatus;
                        d.sort   = currentSort;
                        d.start_date = currentStartDate;
                        d.end_date   = currentEndDate;
                    }
                },
                language: {
                    searchPlaceholder: "Name, Email or Subject...",
                    lengthMenu: "Entries per page: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ messages"
                },
                columns: [
                    {data: 'DT_RowIndex',          name: 'DT_RowIndex',          orderable: false, searchable: false, className: 'text-muted text-center'},
                    {data: 'name',                  name: 'name',                  className: 'font-weight-bold text-dark'},
                    {data: 'email',                 name: 'email',                 className: 'text-muted small'},
                    {data: 'subject',               name: 'subject'},
                    {data: 'status_badge',          name: 'status',                orderable: false, searchable: false, className: 'text-center'},
                    {data: 'created_at_formatted',  name: 'created_at',            className: 'text-muted small'},
                    {data: 'action',                name: 'action',                orderable: false, searchable: false, className: 'text-center text-nowrap'},
                ],
                drawCallback: function () {
                    // Style View button
                    $('#contacts-table .btn-info')
                        .removeClass('btn-info')
                        .addClass('btn-outline-primary btn-action mx-1');

                    // Style Delete button
                    $('#contacts-table .btn-danger.delete-contact')
                        .removeClass('btn-danger')
                        .addClass('btn-outline-danger btn-action mx-1');

                    // Style Restore button
                    $('#contacts-table .btn-success.restore-contact')
                        .removeClass('btn-success')
                        .addClass('btn-outline-success btn-action mx-1');


                }
            });

            // Filters
            $('#statusFilter, #sortFilter, #startDateFilter, #endDateFilter').on('change', function () {
                currentStatus = $('#statusFilter').val();
                currentSort   = $('#sortFilter').val();
                currentStartDate = $('#startDateFilter').val();
                currentEndDate = $('#endDateFilter').val();
                table.draw();
            });

            // Reset
            $('#resetFilters').on('click', function () {
                $('#statusFilter').val('all');
                $('#sortFilter').val('latest');
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                
                currentStatus = 'all';
                currentSort   = 'latest';
                currentStartDate = '';
                currentEndDate = '';
                table.search('').draw();
            });

            // Delete Contact (Soft)
            $('body').on('click', '.delete-contact', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Move to Trash?',
                    text: "This message will be soft deleted and can be restored later.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Yes, trash it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            success: function () {
                                table.draw();
                                Swal.fire('Trashed!', 'Message moved to trash.', 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });

            // Restore Contact
            $('body').on('click', '.restore-contact', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Restore Message?',
                    text: "This message will be made visible again.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1cc88a',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Yes, restore it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            success: function (data) {
                                table.draw();
                                Swal.fire('Restored!', data.message || 'Message restored successfully.', 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush



