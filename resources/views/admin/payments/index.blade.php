@extends('admin.layouts.admin')

@section('title', 'Payments & Transactions - LaraBids')

@push('styles')
    <link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            padding: 0.3rem 0.75rem;
            margin-left: 0.5rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            padding: 0.3rem 0.5rem;
        }
        .filter-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #5a5c69;
            margin-bottom: 0.2rem;
            display: block;
        }
        .filter-control {
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            font-size: 0.85rem;
            height: 38px !important;
        }
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            border-bottom-width: 2px !important;
            background-color: #f8fafc;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
            color: #475569;
            padding: 0.75rem;
            border-bottom: 1px solid #e3e6f0;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            line-height: 32px;
            text-align: center;
            border-radius: 4px;
            display: inline-block;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-action:hover {
            background-color: #4e73df;
            color: white !important;
        }
        .stat-card {
            border-radius: 4px;
            border: 1px solid #e3e6f0;
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
                            <div class="small text-muted font-italic mt-1">Total revenue processed</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success shadow-sm">
                                <i class="fas fa-wallet fa-lg text-white"></i>
                            </div>
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
                            @php
                                $totalCommission = \App\Models\Payment::where('status', 'success')->sum('commission_amount');
                                if($totalCommission == 0) $totalCommission = \App\Models\Payment::where('status', 'success')->sum('amount') * 0.05;
                            @endphp
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($totalCommission, 2) }}</div>
                            <div class="small text-muted font-italic mt-1">Platform earnings from all methods</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary shadow-sm">
                                <i class="fas fa-percent fa-lg text-white"></i>
                            </div>
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
                            <div class="small text-muted mb-0">Processed through PayU Secure Gateway</div>
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
                <div class="col-xl-3 col-lg-3 col-md-6 mb-3">
                    <label class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Payment Status</label>
                    <select id="statusFilter" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                
                <div class="col-xl-2 col-lg-2 col-md-4 mb-3">
                    <label class="filter-label"><i class="far fa-calendar-alt mr-1"></i> From Date</label>
                    <input type="date" id="fromDateFilter" class="form-control filter-control w-100">
                </div>
                
                <div class="col-xl-2 col-lg-2 col-md-4 mb-3">
                    <label class="filter-label"><i class="far fa-calendar-alt mr-1"></i> To Date</label>
                    <input type="date" id="toDateFilter" class="form-control filter-control w-100">
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 mb-3">
                    <button type="button" class="btn btn-light border w-100 font-weight-bold shadow-sm" id="resetFilters" style="height: 38px;">
                        <i class="fas fa-sync-alt mr-1 text-primary"></i> <span class="text-primary">Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Directory Card -->
    <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
        <div class="card-header py-3 bg-white border-bottom d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-ul mr-2 text-primary"></i>Detailed Payment Directory</h6>
            <button id="exportCsv" class="btn btn-outline-success btn-sm font-weight-bold px-3">
                <i class="fas fa-file-csv mr-1"></i> Export Excel
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="payment-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center text-nowrap">Id</th>
                            <th class="text-nowrap" style="min-width: 200px;">Transaction Details</th>
                            <th class="text-nowrap">Buyer Info</th>
                            <th class="text-nowrap" style="min-width: 200px;">Auction Item</th>
                            <th class="text-right text-nowrap">Sale Amount</th>
                            <th class="text-right text-nowrap">Commission (5%)</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-nowrap">Timestamp</th>
                            <th width="80" class="text-center">Actions</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    $(document).ready(function () {
            var currentStatus = 'all';
            var currentMethod = 'all';
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
                        d.payment_method = currentMethod;
                        d.from_date = currentFromDate;
                        d.to_date = currentToDate;
                    }
                },
                language: {
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Entries per page: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ payments"
                },
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted text-center'},
                    {data: 'txnid', name: 'txnid'},
                    {data: 'user', name: 'user.name'},
                    {data: 'auction', name: 'auction.title', className: 'font-weight-bold text-dark'},
                    {data: 'amount', name: 'amount', className: 'text-right'},
                    {data: 'fee', name: 'fee', className: 'text-right', orderable: false},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'created_at', name: 'created_at', className: 'text-muted small'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                ]
            });

            // Filter Change Handlers
            $('#statusFilter, #methodFilter, #fromDateFilter, #toDateFilter').on('change', function() {
                currentStatus = $('#statusFilter').val();
                currentMethod = $('#methodFilter').val();
                currentFromDate = $('#fromDateFilter').val();
                currentToDate = $('#toDateFilter').val();
                table.draw();
            });

            // Export CSV Link Handler
            $('#exportCsv').click(function() {
                var params = {
                    status: currentStatus,
                    payment_method: currentMethod,
                    from_date: currentFromDate,
                    to_date: currentToDate
                };
                var url = "{{ route('admin.payments.export') }}?" + $.param(params);
                window.location.href = url;
            });

            // Reset Filters
            $('#resetFilters').on('click', function() {
                $('#statusFilter').val('all');
                $('#methodFilter').val('all');
                $('#fromDateFilter').val('');
                $('#toDateFilter').val('');
                
                currentStatus = 'all';
                currentMethod = 'all';
                currentFromDate = '';
                currentToDate = '';
                
                table.search('').draw();
            });
        });
    </script>
@endpush
