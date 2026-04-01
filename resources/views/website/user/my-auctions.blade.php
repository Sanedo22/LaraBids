@extends('website.layouts.dashboard')

@section('title', 'My Auctions | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">My Auctions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Auctions</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('auctions.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> List New Item
    </a>
</div>


<!-- Filters Section -->
<div class="card shadow-sm border-0 mb-4 rounded-lg overflow-hidden">
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="categoryFilter" class="filter-label"><i class="fas fa-tags me-2"></i>Category</label>
                <select id="categoryFilter" class="form-select filter-control">
                    <option value="" selected>All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" class="fw-bold">{{ $cat->name }}</option>
                        @foreach($cat->children as $child)
                            <option value="{{ $child->slug }}">&nbsp;&nbsp;&bull; {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="statusFilter" class="filter-label"><i class="fas fa-circle-notch me-2"></i>Status</label>
                <select id="statusFilter" class="form-select filter-control">
                    <option value="all" selected>All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="live">Live</option>
                    <option value="ended">Ended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="sortFilter" class="filter-label"><i class="fas fa-sort-amount-down-alt me-2"></i>Sort By</label>
                <select id="sortFilter" class="form-select filter-control">
                    <option value="latest" selected>Latest Listings</option>
                    <option value="ending_soon">Ending Soon</option>
                    <option value="price_desc">Highest Price</option>
                    <option value="price_asc">Lowest Price</option>
                </select>
            </div>
            
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="startDateFilter" class="filter-label"><i class="far fa-calendar-alt me-2"></i>From Date</label>
                <input type="date" id="startDateFilter" class="form-control filter-control">
            </div>
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="endDateFilter" class="filter-label"><i class="far fa-calendar-alt me-2"></i>To Date</label>
                <input type="date" id="endDateFilter" class="form-control filter-control">
            </div>
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 text-end">
                <button type="button" class="btn btn-light border w-100" id="resetFilters" style="height: 42px;" title="Reset Filters">
                    <i class="fas fa-sync-alt text-primary"></i> <span class="ms-1 text-primary fw-bold">Reset</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Auctions List -->
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header py-3 bg-white border-bottom d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-ul me-2 text-primary"></i>My Auctions Directory</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-3 py-4">
            <table class="table table-hover border-bottom w-100" id="myAuctionsTable" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-nowrap" style="min-width: 320px;">Item Details</th>
                        <th class="text-nowrap text-center">Status</th>
                        <th class="text-nowrap">Price</th>
                        <th class="text-nowrap">Winner</th>
                        <th class="text-center text-nowrap">Stats</th>
                        <th width="100" class="text-center text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Hidden Delete Form --}}
<form id="deleteAuctionForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var currentStatus = 'all';
    var currentCategory = '';
    var currentSort = 'latest';
    var currentStartDate = '';
    var currentEndDate = '';

    var table = $('#myAuctionsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('user.my-auctions.data') }}",
            data: function (d) {
                d.status = currentStatus;
                d.category = currentCategory;
                d.sort = currentSort;
                d.start_date = currentStartDate;
                d.end_date = currentEndDate;
            }
        },
        columns: [
            { data: 'item', name: 'title' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'price', name: 'current_price' },
            { data: 'winner', name: 'winner', orderable: false, searchable: false },
            { data: 'bids', name: 'bids', orderable: false, searchable: false, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [], 
        language: {
            searchPlaceholder: "Search by title or details...",
            lengthMenu: "Entries per page: _MENU_",
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next: '<i class="fas fa-chevron-right"></i>'
            },
            emptyTable: `<div class="text-center py-5">
                            <i class="fas fa-bullhorn fs-1 text-gray-300 opacity-25 mb-3 d-block"></i>
                            <span class="text-secondary fw-bold fs-6">No auctions listed yet.</span>
                            <span class="d-block text-muted small mt-1">Start selling by listing your first auction item!</span>
                        </div>`,
            zeroRecords: `<div class="text-center py-5">
                            <i class="fas fa-search fs-1 text-gray-300 opacity-25 mb-3 d-block"></i>
                            <span class="text-secondary fw-bold fs-6">No matching auctions found.</span>
                            <span class="d-block text-muted small mt-1">Try adjusting your filters or search query.</span>
                        </div>`
        },
        dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    $('#statusFilter, #categoryFilter, #sortFilter, #startDateFilter, #endDateFilter').on('change', function() {
        currentStatus = $('#statusFilter').val();
        currentCategory = $('#categoryFilter').val();
        currentSort = $('#sortFilter').val();
        currentStartDate = $('#startDateFilter').val();
        currentEndDate = $('#endDateFilter').val();
        table.draw();
    });

    // Initialize tooltips on table draw
    table.on('draw', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

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
        
        table.search('').draw();
    });

    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Delete Auction?',
            text: "This action cannot be undone. All bids will be lost!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary ms-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('deleteAuctionForm');
                form.action = "{{ url('auctions') }}/" + id;
                form.submit();
            }
        });
    }
});
</script>
@endpush

@endsection



