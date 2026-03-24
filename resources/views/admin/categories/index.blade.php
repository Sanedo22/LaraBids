@extends('admin.layouts.admin')

@section('title', 'Categories - LaraBids')

@push('styles')
    <link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .filter-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.4rem;
            color: #4a5568;
            font-weight: 700;
        }
        .filter-control {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #4a5568;
            background-color: #f8fafc;
            transition: all 0.2s ease-in-out;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.025);
        }
        .filter-control:focus {
            border-color: #a3bffa;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
            outline: none;
        }
        .table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            border-bottom-width: 2px !important;
            background-color: #f8fafc;
        }
        .table td {
            vertical-align: middle;
            color: #475569;
            font-size: 0.9rem;
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
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.75rem;
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
        .category-icon-box {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4e73df;
            font-size: 1.05rem;
            box-shadow: 0 1px 3px rgba(78, 115, 223, 0.1);
        }
    </style>
@endpush

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manage Categories</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Category
        </a>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <div class="row align-items-end">

                <!-- Parent Category -->
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="parentFilter" class="filter-label"><i class="fas fa-folder mr-1"></i> Parent Category</label>
                    <select id="parentFilter" class="custom-select filter-control w-100">
                        <option value="">All Categories</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="statusFilter" class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Status</label>
                    <select id="statusFilter" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="active">Active</option>
                        <option value="trashed">In Trash</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="sortFilter" class="filter-label"><i class="fas fa-sort-amount-down-alt mr-1"></i> Sort By</label>
                    <select id="sortFilter" class="custom-select filter-control w-100">
                        <option value="latest" selected>Latest Added</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name_asc">Name A → Z</option>
                        <option value="name_desc">Name Z → A</option>
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

    <!-- Table Card -->
    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-header py-3 bg-white border-bottom d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-ul mr-2 text-primary"></i>Category Directory</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="categories-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="30">Id</th>
                            <th width="60" class="text-center">Icon</th>
                            <th>Category Name</th>
                            <th>Parent</th>
                            <th>Slug</th>
                            <th class="text-center">Items</th>
                            <th width="130" class="text-center text-nowrap">Action</th>
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

            var currentParent = '';
            var currentStatus = 'all';
            var currentSort   = 'latest';
            var currentStartDate = '';
            var currentEndDate = '';

            var table = $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.categories.index') }}",
                    data: function (d) {
                        d.parent = currentParent;
                        d.status = currentStatus;
                        d.sort   = currentSort;
                        d.start_date = currentStartDate;
                        d.end_date = currentEndDate;
                    }
                },
                language: {
                    searchPlaceholder: "Name or Slug...",
                    lengthMenu: "Entries per page: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ categories"
                },
                pageLength: -1,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex',   orderable: false, searchable: false, className: 'text-muted text-center'},
                    {data: 'icon',        name: 'icon',           orderable: false, searchable: false, className: 'text-center'},
                    {data: 'name',        name: 'name',           className: 'font-weight-bold text-dark'},
                    {data: 'parent',      name: 'parent',         orderable: false},
                    {data: 'slug',        name: 'slug',           className: 'text-muted small'},
                    {data: 'count',       name: 'auctions_count', searchable: false, className: 'text-center'},
                    {data: 'action',      name: 'action',         orderable: false, searchable: false, className: 'text-center text-nowrap'},
                ],
                drawCallback: function () {
                    // Edit (btn-primary → outline-primary)
                    $('#categories-table .btn-primary:not(.btn-action-done)')
                        .addClass('btn-outline-primary btn-action btn-action-done mx-1')
                        .removeClass('btn-primary btn-circle');

                    // Delete → outline-danger
                    $('#categories-table .delete-category')
                        .addClass('btn-outline-danger btn-action mx-1')
                        .removeClass('btn-danger btn-circle');

                    // Restore → outline-success
                    $('#categories-table .restore-category')
                        .addClass('btn-outline-success btn-action mx-1')
                        .removeClass('btn-success btn-circle');

                    // Force delete → outline-danger
                    $('#categories-table .force-delete-category')
                        .addClass('btn-outline-danger btn-action mx-1')
                        .removeClass('btn-danger btn-circle');

                    // Disabled force delete
                    $('#categories-table .btn-secondary[disabled]')
                        .addClass('btn-action mx-1')
                        .removeClass('btn-circle');

                    // Wrap icons in styled box
                    $('#categories-table tbody td:nth-child(2)').each(function () {
                        var $td = $(this);
                        if ($td.find('.category-icon-box').length === 0) {
                            $td.html('<div class="category-icon-box">' + $td.html() + '</div>');
                        }
                    });
                }
            });

            // Filters change
            $('#parentFilter, #statusFilter, #sortFilter, #startDateFilter, #endDateFilter').on('change', function () {
                currentParent = $('#parentFilter').val();
                currentStatus = $('#statusFilter').val();
                currentSort   = $('#sortFilter').val();
                currentStartDate = $('#startDateFilter').val();
                currentEndDate = $('#endDateFilter').val();
                table.draw();
            });

            // Reset
            $('#resetFilters').on('click', function () {
                $('#parentFilter').val('');
                $('#statusFilter').val('all');
                $('#sortFilter').val('latest');
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                
                currentParent = '';
                currentStatus = 'all';
                currentSort   = 'latest';
                currentStartDate = '';
                currentEndDate = '';
                table.search('').draw();
            });

            // Delete Category (Move to Trash)
            $('body').on('click', '.delete-category', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Move to Trash?',
                    text: "You can restore this category later.",
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
                                Swal.fire('Trashed!', 'Category moved to trash.', 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });

            // Restore Category
            $('body').on('click', '.restore-category', function () {
                var url = $(this).data('url');
                $.ajax({
                    type: "POST",
                    url: url,
                    success: function () {
                        table.draw();
                        Swal.fire('Restored!', 'Category has been restored.', 'success');
                    },
                    error: function () {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            });

            // Force Delete Category
            $('body').on('click', '.force-delete-category', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Permanent Delete!',
                    text: "This category will be permanently deleted and cannot be recovered!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Yes, delete permanently!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            success: function () {
                                table.draw();
                                Swal.fire('Deleted!', 'Category permanently deleted.', 'success');
                            },
                            error: function (xhr) {
                                var msg = 'Something went wrong.';
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    msg = xhr.responseJSON.error;
                                }
                                Swal.fire('Error!', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush



