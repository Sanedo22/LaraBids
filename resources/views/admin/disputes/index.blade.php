@extends('admin.layouts.admin')

@section('title', 'Reputation & Dispute Management | Admin')

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
        #disputes-table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            border-top: none;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
        }
        #disputes-table td {
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
            <h1 class="h3 mb-0 text-gray-800">Reputation & Dispute Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Disputes & Reports</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg" style="border-left: 4px solid #4e73df !important;">
        <div class="card-body p-4">
            <div class="row align-items-end">
                <div class="col-xl-5 col-lg-5 col-md-5 col-sm-6 mb-3">
                    <label class="filter-label"><i class="fas fa-circle-notch mr-1"></i> Filter Status</label>
                    <select id="filter-status" class="custom-select filter-control w-100">
                        <option value="all" selected>All Statuses</option>
                        <option value="active">Active Strikes</option>
                        <option value="appealed">Pending Appeals/Reports</option>
                        <option value="dismissed">Dismissed</option>
                        <option value="resolved">Resolved/Closed</option>
                    </select>
                </div>
                <div class="col-xl-5 col-lg-5 col-md-5 col-sm-6 mb-3">
                    <label class="filter-label"><i class="fas fa-filter mr-1"></i> Filter Type</label>
                    <select id="filter-type" class="custom-select filter-control w-100">
                        <option value="all">All Types</option>
                        <option value="buyer_non_payment">Buyer Non-Payment</option>
                        <option value="seller_misconduct">Seller Misconduct</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-12 mb-3">
                    <label class="filter-label d-none d-md-block">&nbsp;</label>
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
                <i class="fas fa-balance-scale mr-2 text-primary"></i>Disputes Directory
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-3 py-4">
                <table class="table table-hover border-bottom" id="disputes-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Target User</th>
                            <th>Type</th>
                            <th>Reason / Dispute</th>
                            <th>Reported By</th>
                            <th class="text-center">Status</th>
                            <th>Date</th>
                            <th class="text-center text-nowrap">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

<!-- Dispute Detail Modal -->
<div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header px-4 pt-4 border-0">
                <h5 class="modal-title fw-bold">Dispute Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="dispute-details-content">
                    <!-- Loaded via JS -->
                </div>
                <hr class="my-4">
                <div class="mt-3">
                    <label class="form-label fw-bold small text-uppercase">Admin Resolution Note</label>
                    <textarea id="admin-note" class="form-control" rows="3" placeholder="Explain the reasoning for this decision..."></textarea>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4 border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success rounded-pill px-4" onclick="submitResolution('dismissed')">Dismiss/Remove Strike</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" onclick="submitResolution('resolved')">Keep/Uphold Strike</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>
    let disputesTable;
    let currentDisputeId = null;

    $(function() {
        disputesTable = $('#disputes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.disputes.data') }}",
                data: function(d) {
                    d.status = $('#filter-status').val();
                    d.type = $('#filter-type').val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'user_info', name: 'user_id'},
                {data: 'type_badge', name: 'type'},
                {data: 'reason_short', name: 'reason', orderable: false},
                {data: 'reporter_info', name: 'reported_by'},
                {data: 'status_badge', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[0, 'desc']],
            language: {
                search: "Search:",
                searchPlaceholder: "User, Type or Reason...",
                lengthMenu: "Entries per page: _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ disputes"
            },
            dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });

        $('#filter-status, #filter-type').on('change', function() {
            disputesTable.ajax.reload();
        });

        $('#resetFilters').on('click', function() {
            $('#filter-status').val('all');
            $('#filter-type').val('all');
            disputesTable.search('');
            disputesTable.ajax.reload();
        });
    });

    function viewDispute(id) {
        currentDisputeId = id;
        const rowData = disputesTable.row(function(idx, data, node) {
            return data.id === id;
        }).data();

        let reasonBox = '';
        if (rowData.type === 'seller_misconduct') {
            reasonBox = `
                <div class="p-3 bg-light rounded-3 border-start border-4 border-danger">
                    <h6 class="fw-bold mb-2 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Report Description (Misconduct Details):</h6>
                    <p class="mb-0 italic" style="white-space: pre-wrap;">"${rowData.reason}"</p>
                </div>
            `;
        } else {
            reasonBox = `
                <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
                    <h6 class="fw-bold mb-2"><i class="fas fa-comment-dots me-2"></i>User Dispute/Appeal Reason:</h6>
                    <p class="mb-0 italic" style="white-space: pre-wrap;">"${rowData.appeal_reason || 'No appeal message provided.'}"</p>
                </div>
            `;
        }

        let html = `
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Case Information</h6>
                    <p class="mb-1"><strong>Auction:</strong> ${rowData.auction ? rowData.auction.title : 'N/A'}</p>
                    ${rowData.type !== 'seller_misconduct' ? `<p class="mb-1"><strong>Strike Reason:</strong> ${rowData.reason}</p>` : ''}
                    <p class="mb-1"><strong>Type:</strong> ${rowData.type_badge}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Target User</h6>
                    ${rowData.user_info}
                </div>
                <div class="col-12 mt-3">
                    ${reasonBox}
                </div>
            </div>
        `;

        $('#dispute-details-content').html(html);
        $('#admin-note').val(rowData.admin_note || '');
        $('#disputeModal').modal('show');
    }

    function resolveDispute(id, status) {
        currentDisputeId = id;
        let actionTxt = status === 'dismissed' ? 'DISMISS and remove' : 'UPHOLD and keep';
        if(confirm(`Are you sure you want to ${actionTxt} this strike?`)) {
            submitResolution(status);
        }
    }

    function submitResolution(status) {
        if(!currentDisputeId) return;
        const note = $('#admin-note').val();

        $.post(`{{ url('admin/disputes') }}/${currentDisputeId}/resolve`, {
            _token: "{{ csrf_token() }}",
            status: status,
            admin_note: note
        }, function(response) {
            if(response.status) {
                $('#disputeModal').modal('hide');
                disputesTable.ajax.reload(null, false);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    }
</script>
@endpush
