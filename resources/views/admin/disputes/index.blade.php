@extends('admin.layouts.admin')

@section('title', 'Reputation & Dispute Management | Admin')

@section('content')
<div class="container-fluid pf-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-balance-scale text-primary me-2"></i> Reputation & Dispute Management</h2>
            <p class="text-muted">Review Buyer appeals and Seller misconduct reports.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter Status</label>
                    <select id="filter-status" class="form-select border-0 bg-light">
                        <option value="all" selected>All Statuses</option>
                        <option value="active">Active Strikes</option>
                        <option value="appealed">Pending Appeals/Reports</option>
                        <option value="dismissed">Dismissed</option>
                        <option value="resolved">Resolved/Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter Type</label>
                    <select id="filter-type" class="form-select border-0 bg-light">
                        <option value="all">All Types</option>
                        <option value="buyer_non_payment">Buyer Non-Payment</option>
                        <option value="seller_misconduct">Seller Misconduct</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="disputes-table" class="table table-hover align-middle w-100">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Target User</th>
                            <th>Type</th>
                            <th>Reason / Dispute</th>
                            <th>Reported By</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
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
                {data: 'type', name: 'type'},
                {data: 'reason_short', name: 'reason', orderable: false},
                {data: 'reporter_info', name: 'reported_by'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                search: "",
                searchPlaceholder: "Search disputes..."
            }
        });

        $('#filter-status, #filter-type').on('change', function() {
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
                    <p class="mb-1"><strong>Type:</strong> ${rowData.type}</p>
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
                toastr.success(response.message);
            }
        });
    }
</script>
@endpush
