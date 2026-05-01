@extends('admin.layouts.admin')

@section('title', 'Manage Auctions - LaraBids')

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
        .auction-img-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.35rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
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
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manage Auctions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Auctions</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <div class="row align-items-end">
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="categoryFilter" class="filter-label"><i class="fas fa-tags mr-1"></i> Category</label>
                    <select id="categoryFilter" class="custom-select filter-control w-100">
                        <option value="" selected>All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" class="font-weight-bold" style="font-weight: bold;">{{ $cat->name }}</option>
                            @foreach($cat->children as $child)
                                <option value="{{ $child->slug }}">&nbsp;&nbsp;&bull; {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="statusFilter" class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Status</label>
                    <select id="statusFilter" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active/Live</option>
                        <option value="closed">Closed / Past</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="resubmitted">Re-submitted</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <label for="sortFilter" class="filter-label"><i class="fas fa-sort-amount-down-alt mr-1"></i> Sort By</label>
                    <select id="sortFilter" class="custom-select filter-control w-100">
                        <option value="latest" selected>Latest</option>
                        <option value="ending_soon">Ending Soon</option>
                        <option value="price_desc">High Price</option>
                        <option value="price_asc">Low Price</option>
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

    <!-- DataTales Example -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-3 px-4 bg-white d-flex align-items-center justify-content-between" style="min-height: 60px;">
            <h6 class="m-0 font-weight-bold text-secondary">
                <i class="fas fa-list-ul mr-2 text-primary"></i>Auction Directory
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
                <table class="table table-hover border-bottom" id="auctions-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="30" class="text-center pr-1"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="selectAll"><label class="custom-control-label" for="selectAll"></label></div></th>
                            <th width="40" class="text-center text-nowrap px-1">Id</th>
                            <th width="50" class="text-center text-nowrap px-1">Image</th>
                            <th class="text-nowrap" style="min-width: 200px; max-width: 250px;">Title & Details</th>
                            <th class="text-nowrap" style="max-width: 120px;">Category</th>
                            <th class="text-nowrap" style="max-width: 120px;">Seller</th>
                            <th class="text-right text-nowrap">Current Bid</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-nowrap">End Time</th>
                            <th width="100" class="text-center text-nowrap">Action</th>
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

            var currentStatus = 'all';
            var currentCategory = '';
            var currentSort = 'latest';
            var currentStartDate = '';
            var currentEndDate = '';

            var table = $('#auctions-table').DataTable({
                processing: true,
                serverSide: true,
                order: [], // Disable default sorting
                ajax: {
                    url: "{{ route('admin.auctions.index') }}",
                    data: function (d) {
                        d.status = currentStatus;
                        d.category = currentCategory;
                        d.sort = currentSort;
                        d.start_date = currentStartDate;
                        d.end_date = currentEndDate;
                    }
                },
                language: {
                    search: "Search:",
                    searchPlaceholder: "Title, Category or Seller...",
                    lengthMenu: "Entries per page: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ auctions"
                },
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columns: [
                    {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'id', name: 'id', className: 'text-center font-weight-bold px-1'},
                    {data: 'image', name: 'image', orderable: false, searchable: false, className: 'text-center px-1'},
                    {data: 'title', name: 'title'},
                    {data: 'category', name: 'category.name'},
                    {data: 'user', name: 'user.name'},
                    {data: 'current_price', name: 'current_price', className: 'text-right'},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'end_time', name: 'end_time', className: 'text-muted small'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap'},
                ],
                drawCallback: function() {
                    $('.btn-sm').addClass('btn-action');
                    
                    $('#auctions-table img').addClass('auction-img-thumbnail');
                }
            });

            // Filters Change Handler
            $('#statusFilter, #categoryFilter, #sortFilter, #startDateFilter, #endDateFilter').on('change', function() {
                currentStatus = $('#statusFilter').val();
                currentCategory = $('#categoryFilter').val();
                currentSort = $('#sortFilter').val();
                currentStartDate = $('#startDateFilter').val();
                currentEndDate = $('#endDateFilter').val();
                table.draw();
            });

            // Reset Filters
            $('#resetFilters').on('click', function() {
                $('#statusFilter').val('all');
                $('#categoryFilter').val('');
                $('#sortFilter').val('latest');
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                
                currentStatus = 'all';
                currentCategory = '';
                currentSort = 'latest';
                currentStartDate = '';
                currentEndDate = '';
                
                // Clear datatables search
                table.search('').draw();
            });

            // Delete Auction
            $('body').on('click', '.delete-auction', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Move to Trash?',
                    text: "You can restore this auction later.",
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
                            success: function (data) {
                                table.draw();
                                Swal.fire('Trashed!', 'Auction moved to trash.', 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
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
                        Swal.fire('Restored!', 'Auction has been restored.', 'success');
                    },
                    error: function () {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            });

            // Force Delete Auction
            $('body').on('click', '.force-delete-auction', function () {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Permanent Delete!',
                    text: "You will not be able to recover this auction!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#5a5c69',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Permanently Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            success: function (data) {
                                table.draw();
                                Swal.fire('Deleted!', 'Auction permanently deleted.', 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                })
            });

            // Clear selection button
            $(document).on('click', '#clearSelection', function() {
                $('.auction-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                toggleBulkActions();
            });

            // Bulk Actions Logic
            $(document).on('change', '#selectAll', function() {
                $('.auction-checkbox').prop('checked', this.checked);
                toggleBulkActions();
            });

            $(document).on('change', '.auction-checkbox', function() {
                if ($('.auction-checkbox:checked').length == $('.auction-checkbox').length && $('.auction-checkbox').length > 0) {
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
                var checkedCheckboxes = $('.auction-checkbox:checked');
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
                        // All selected are trashed
                        options += '<option value="restore">Restore Selected</option>';
                        options += '<option value="force_delete">Delete Permanently</option>';
                    } else if (!anyTrashed && anyActive) {
                        // All selected are NOT trashed
                        options += '<option value="approve">Approve Selected</option>';
                        options += '<option value="delete">Move to Trash</option>';
                    } else {
                        // Mixed selection
                        options += '<option value="" disabled>Cannot mix Active & Trashed</option>';
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
                $('.auction-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to perform this action on ' + selectedIds.length + ' auction(s).',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.auctions.bulk_action') }}",
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
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush



