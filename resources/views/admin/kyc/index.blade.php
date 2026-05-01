@extends('admin.layouts.admin')

@section('title', 'KYC Verifications - LaraBids')

@section('content')
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">KYC Verifications</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">KYC Verifications</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <form id="filter-form">
                <div class="row align-items-end">
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
                        <label for="status-filter" class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Verification Status</label>
                        <select id="status-filter" class="custom-select filter-control w-100">
                            <option value="all">All Submissions</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="resubmitted">Re-submitted</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
                        <label for="id-type-filter" class="filter-label"><i class="fas fa-id-card mr-1"></i> ID Document Type</label>
                        <select id="id-type-filter" class="custom-select filter-control w-100">
                            <option value="all">All Doc Types</option>
                            <option value="aadhaar">Aadhaar Card</option>
                            <option value="pan">PAN Card</option>
                            <option value="passport">Passport</option>
                            <option value="driving_license">Driving License</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                        <label for="start-date" class="filter-label"><i class="far fa-calendar-alt mr-1"></i> From Date</label>
                        <input type="date" id="start-date" class="form-control filter-control w-100">
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 mb-3">
                        <label for="end-date" class="filter-label"><i class="far fa-calendar-alt mr-1"></i> To Date</label>
                        <input type="date" id="end-date" class="form-control filter-control w-100">
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 mb-3">
                        <button type="button" class="btn btn-light border w-100 font-weight-bold" id="reset-filters" style="height: calc(1.5em + .75rem + 2px);">
                            <i class="fas fa-sync-alt mr-1 text-primary"></i> <span class="text-primary">Reset</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-lg mt-4">
        <div class="card-header py-3 px-4 bg-white border-bottom d-flex flex-row align-items-center justify-content-between" style="min-height: 60px;">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-list-ul mr-2 text-primary"></i>KYC Submissions Monitoring
            </h6>
            

        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="kyc-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th width="50" class="text-center text-nowrap">Id</th>
                            <th>User</th>
                            <th>Full Name</th>
                            <th>ID Type</th>
                            <th>Gender</th>
                            <th>Submitted At</th>
                            <th class="text-center">Status</th>
                            <th width="100" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

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
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#kyc-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.kyc.data') }}",
                    data: function (d) {
                        d.status = $('#status-filter').val();
                        d.id_type = $('#id-type-filter').val();
                        d.start_date = $('#start-date').val();
                        d.end_date = $('#end-date').val();
                    }
                },
                language: {
                    search: "Search:",
                    searchPlaceholder: "Name, Status or Type...",
                    lengthMenu: "Entries per page: _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ submissions"
                },
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columns: [

                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted text-center'},
                    { data: 'user', name: 'user' },
                    { data: 'full_name', name: 'full_name', className: 'font-weight-bold text-dark' },
                    { data: 'id_type', name: 'id_type' },
                    { data: 'gender', name: 'gender', className: 'text-capitalize' },
                    { data: 'created_at', name: 'created_at', className: 'text-muted small' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' }
                ],
                order: [[4, 'desc']], // Sort by "Submitted At" (index 4) by default
                pageLength: 10,
                drawCallback: function() {
                    $('.btn-info').removeClass('btn-info').addClass('btn-outline-info').html('<i class="fas fa-eye"></i>');
                    $('.btn-sm').addClass('btn-action mx-1');
                }
            });

            // Trigger filters
            $('#status-filter, #id-type-filter, #start-date, #end-date').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#reset-filters').on('click', function() {
                $('#filter-form')[0].reset();
                table.draw();
            });


        });
    </script>
@endpush



