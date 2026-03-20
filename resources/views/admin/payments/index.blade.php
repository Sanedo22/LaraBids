@extends('admin.layouts.admin')

@section('title', 'Payments & Transactions - LaraBids')

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
        .stat-card {
            transition: transform 0.2s;
            border-radius: 0.75rem;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Payments & Transactions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payments</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-inline-block">
            <span class="badge badge-success shadow-sm px-3 py-2" style="border-radius: 50px;">
                <i class="fas fa-check-circle mr-1"></i> PayU Gateway Active
            </span>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sales Volume</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format(\App\Models\Payment::where('status', 'success')->sum('amount'), 2) }}</div>
                            <div class="small text-muted font-italic mt-1">Total PayU collected</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Commission (5%)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format(\App\Models\Payment::where('status', 'success')->sum('amount') * 0.05, 2) }}</div>
                            <div class="small text-muted font-italic mt-1">Platform earnings</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Successful Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Payment::where('status', 'success')->count() }}</div>
                            <div class="small text-muted font-italic mt-1">PayU success records</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <div class="row align-items-end">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
                    <label class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Payment Status</label>
                    <select id="statusFilter" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
                    <label class="filter-label"><i class="far fa-calendar-alt mr-1"></i> From Date</label>
                    <input type="date" id="fromDateFilter" class="form-control filter-control w-100 placeholder-muted">
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
                    <label class="filter-label"><i class="far fa-calendar-alt mr-1"></i> To Date</label>
                    <input type="date" id="toDateFilter" class="form-control filter-control w-100">
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3 text-right">
                    <button type="button" class="btn btn-light border w-100 font-weight-bold" id="resetFilters" style="height: calc(1.5em + .75rem + 2px);">
                        <i class="fas fa-sync-alt mr-1 text-primary"></i> <span class="text-primary">Reset Filters</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Directory Card -->
    <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
        <div class="card-header py-3 bg-white border-bottom d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-primary"></i>Detailed Payment Directory</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="payment-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center text-nowrap">Id</th>
                            <th class="text-nowrap" style="min-width: 250px;">Transaction Details</th>
                            <th class="text-nowrap">Buyer Info</th>
                            <th class="text-nowrap" style="min-width: 250px;">Auction Item</th>
                            <th class="text-right text-nowrap">Sale Amount</th>
                            <th class="text-right text-nowrap">Fee (5%)</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-nowrap">Timestamp</th>
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
            var currentStatus = 'all';
            var currentFromDate = '';
            var currentToDate = '';

            var table = $('#payment-table').DataTable({
                processing: true,
                serverSide: true,
                order: [[7, 'desc']], // Default order by timestamp
                ajax: {
                    url: "{{ route('admin.payments.index') }}",
                    data: function (d) {
                        d.status = currentStatus;
                        d.from_date = currentFromDate;
                        d.to_date = currentToDate;
                    }
                },
                language: {
                    searchPlaceholder: "Search ID, buyer or auction...",
                    lengthMenu: "Entries: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div> Processing...'
                },
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3 text-muted small'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted'},
                    {data: 'txnid', name: 'txnid'},
                    {data: 'user', name: 'user.name'},
                    {data: 'auction', name: 'auction.title'},
                    {data: 'amount', name: 'amount', className: 'text-right'},
                    {data: 'fee', name: 'fee', className: 'text-right', orderable: false},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'created_at', name: 'created_at'},
                ],
                drawCallback: function() {
                    $('.dataTables_paginate .paginate_button').addClass('btn btn-sm btn-light mx-1 border-0');
                    $('.dataTables_paginate .paginate_button.current').addClass('btn-primary text-white border-0').removeClass('btn-light');
                }
            });

            // Filter Change Handlers
            $('#statusFilter, #fromDateFilter, #toDateFilter').on('change', function() {
                currentStatus = $('#statusFilter').val();
                currentFromDate = $('#fromDateFilter').val();
                currentToDate = $('#toDateFilter').val();
                table.draw();
            });

            // Reset Filters
            $('#resetFilters').on('click', function() {
                $('#statusFilter').val('all');
                $('#fromDateFilter').val('');
                $('#toDateFilter').val('');
                
                currentStatus = 'all';
                currentFromDate = '';
                currentToDate = '';
                
                table.search('').draw();
            });
        });
    </script>
@endpush
