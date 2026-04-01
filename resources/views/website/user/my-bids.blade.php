@extends('website.layouts.dashboard')

@section('title', 'My Bids | LaraBids')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h1 class="h3 text-dark fw-bold mb-0">My Active Bids</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Bids</li>
            </ol>
        </nav>
    </div>
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
                    <option value="live">Live</option>
                    <option value="ended">Ended</option>
                </select>
            </div>

            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label for="sortFilter" class="filter-label"><i class="fas fa-sort-amount-down-alt me-2"></i>Sort By</label>
                <select id="sortFilter" class="form-select filter-control">
                    <option value="latest" selected>Latest Bid</option>
                    <option value="price_desc">Highest Amount</option>
                    <option value="price_asc">Lowest Amount</option>
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

<!-- Bids Table -->
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header py-3 bg-white border-bottom d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-ul me-2 text-primary"></i>My Bids Directory</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-3 py-4">
            <table class="table table-hover border-bottom w-100" id="myBidsTable" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-nowrap" style="min-width: 320px;">Auction Item</th>
                        <th class="text-nowrap text-center">My Bid</th>
                        <th class="text-nowrap text-center">Current Price</th>
                        <th class="text-nowrap text-center">Status</th>
                        <th class="text-nowrap text-center">Time Left</th>
                        <th width="80" class="text-center text-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var currentStatus = 'all';
    var currentCategory = '';
    var currentSort = 'latest';
    var currentStartDate = '';
    var currentEndDate = '';

    var table = $('#myBidsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('user.my-bids.data') }}",
            data: function (d) {
                d.status = currentStatus;
                d.category = currentCategory;
                d.sort = currentSort;
                d.start_date = currentStartDate;
                d.end_date = currentEndDate;
            }
        },
        columns: [
            { data: 'item', name: 'item' },
            { data: 'my_bid', name: 'my_bid', className: 'text-center' },
            { data: 'current_price', name: 'current_price', className: 'text-center' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'time_left', name: 'time_left', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [], 
        language: {
            searchPlaceholder: "Search by auction title...",
            lengthMenu: "Entries per page: _MENU_",
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next: '<i class="fas fa-chevron-right"></i>'
            },
            emptyTable: `<div class="text-center py-5">
                            <i class="fas fa-gavel fs-1 text-gray-300 opacity-25 mb-3 d-block"></i>
                            <span class="text-secondary fw-bold fs-6">No active bids yet.</span>
                            <span class="d-block text-muted small mt-1">Explore live auctions and place your first bid!</span>
                        </div>`,
            zeroRecords: `<div class="text-center py-5">
                            <i class="fas fa-search fs-1 text-gray-300 opacity-25 mb-3 d-block"></i>
                            <span class="text-secondary fw-bold fs-6">No matching bids found.</span>
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
});
</script>
@endpush

@endsection



