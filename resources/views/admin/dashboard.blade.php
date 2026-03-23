@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard - LaraBids')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 10px;
        border-right: none;
        border-top: none;
        border-bottom: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .quick-action-btn {
        transition: all 0.2s ease-in-out;
        border-radius: 10px;
        padding: 1.5rem 1rem;
        background-color: #fff;
        border: 1px solid #e3e6f0;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none !important;
        color: #5a5c69 !important;
    }
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.05);
        border-color: #d1d3e2;
        color: #4e73df !important;
        background-color: #f8f9fc;
    }
    .quick-action-btn i {
        color: #858796;
        transition: color 0.2s ease-in-out;
    }
    .quick-action-btn:hover i {
        color: #4e73df;
    }
    /* Simple clean card style for dashboard */
    .dashboard-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.08);
    }
    .dashboard-card .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1f2f6;
        border-radius: 12px 12px 0 0 !important;
    }
</style>
@endpush

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Overview</h1>
            <p class="mb-0 text-muted mt-1">Welcome back, <span class="font-weight-bold">{{ auth()->user()->name ?? 'Admin' }}</span>. Here's what's happening today.</p>
        </div>
    </div>

    <!-- Key Metrics Overview -->
    <div class="row">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                            <a href="{{ route('admin.users.index') }}" class="small text-info">View Directory <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending KYC (Requires Action) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Pending KYC Actions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['pending_kycs'] ?? 0) }}</div>
                            <a href="{{ route('admin.kyc.index') ?? '#' }}" class="small text-danger">Review Documents <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sales Volume -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Sales (PayU)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($stats['total_sales'], 2) }}</div>
                            <span class="small text-muted">All Time Closed</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Platform Fee (5%)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($stats['platform_fee'], 2) }}</div>
                            <span class="small text-muted">Estimated Platform Revenue</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row 2 - Engagement & Support -->
    <div class="row">
        <!-- Live Categories -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Live Categories
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_categories']) }}</div>
                            <a href="{{ route('admin.categories.index') }}" class="small text-warning">Manage <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Engagements (Bids) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Engagement (Total Bids)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_bids']) }}</div>
                            <span class="small text-muted">Overall Activity</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gavel fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bids Today -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Activity Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['bids_today']) }} Bids</div>
                            <span class="small text-muted">Daily Engagement</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unread Support Tickets -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Unread Support Msg
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unread_contacts']) }}</div>
                            <a href="{{ route('admin.contacts.index') }}" class="small text-danger">Reply Now <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Auction Status Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie mr-2"></i>Auctions by Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="auctionStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Auctions Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary" id="trendChartTitle">
                        <i class="fas fa-chart-line mr-2"></i>Auctions Trend (Last 6 Months)
                    </h6>
                    <select id="trendMonths" class="custom-select custom-select-sm border-0 bg-light text-primary font-weight-bold shadow-sm" style="width: auto; cursor: pointer; font-size: 0.85rem;" onchange="updateTrendChart(this.value)">
                        <option value="3">Last 3 Months</option>
                        <option value="6" selected>Last 6 Months</option>
                        <option value="9">Last 9 Months</option>
                        <option value="12">Last 12 Months</option>
                    </select>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyAuctionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Auctions -->
        <div class="col-xl-6 col-lg-6">
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list mr-2"></i>Recent Auctions
                    </h6>
                    <a href="{{ route('admin.auctions.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recent_auctions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_auctions as $auction)
                                        <tr>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $auction->title }}">
                                                    {{ $auction->title }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-secondary" style="font-weight: 500; font-size: 0.85rem;">
                                                    <i class="fas fa-tag fa-sm text-gray-400 mr-1"></i>{{ $auction->category->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $displayStatus = $auction->status;
                                                    if ($auction->status === 'active') {
                                                        if ($auction->end_time && $auction->end_time->isPast()) {
                                                            $displayStatus = 'closed';
                                                        } elseif ($auction->start_time && $auction->start_time->isFuture()) {
                                                            $displayStatus = 'upcoming';
                                                        } else {
                                                            $displayStatus = 'live';
                                                        }
                                                    }
                                                    $badgeClass = match($displayStatus) {
                                                        'live' => 'success',
                                                        'upcoming' => 'warning',
                                                        'pending' => 'info',
                                                        'closed' => 'secondary',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($displayStatus) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.auctions.show', $auction->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted py-4">No auctions yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending KYC Requests -->
        <div class="col-xl-6 col-lg-6">
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-id-card mr-2"></i>Pending KYC Reviews
                    </h6>
                    <a href="{{ route('admin.kyc.index') ?? '#' }}" class="btn btn-sm btn-danger">Manage KYC</a>
                </div>
                <div class="card-body">
                    @if(isset($recent_kycs) && $recent_kycs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>User</th>
                                        <th>ID Type</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_kycs as $kyc)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold">{{ $kyc->user->name ?? 'Unknown' }}</div>
                                                <small class="text-muted">{{ $kyc->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ strtoupper($kyc->id_type) }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $kyc->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/kyc/' . $kyc->id) }}" class="btn btn-sm btn-info">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                            <p class="text-muted mb-0">All clear! No pending KYC requests.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-auto col-lg mb-3">
                            <a href="{{ route('admin.auctions.index') }}" class="btn btn-block quick-action-btn shadow-sm h-100">
                                <i class="fas fa-gavel fa-2x mb-2"></i>
                                <div class="font-weight-bold">Manage Auctions</div>
                            </a>
                        </div>
                        <div class="col-md-auto col-lg mb-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-block quick-action-btn shadow-sm h-100">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <div class="font-weight-bold">Manage Users</div>
                            </a>
                        </div>
                        <div class="col-md-auto col-lg mb-3">
                            <a href="{{ route('admin.kyc.index') ?? '#' }}" class="btn btn-block quick-action-btn shadow-sm position-relative h-100">
                                <i class="fas fa-id-card fa-2x mb-2"></i>
                                <div class="font-weight-bold">Manage KYC</div>
                                @if(isset($stats['pending_kycs']) && $stats['pending_kycs'] > 0)
                                    <span class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border: 2px solid #fff;">{{ $stats['pending_kycs'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-auto col-lg mb-3">
                            <a href="#" class="btn btn-block quick-action-btn shadow-sm h-100" title="Coming Soon: PayU Integration">
                                <i class="fas fa-wallet fa-2x mb-2"></i>
                                <div class="font-weight-bold">Manage Payments</div>
                                <span class="badge badge-warning mt-1">PayU Ready</span>
                            </a>
                        </div>
                        <div class="col-md-auto col-lg mb-3">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-block quick-action-btn shadow-sm h-100">
                                <i class="fas fa-tags fa-2x mb-2"></i>
                                <div class="font-weight-bold">Manage Categories</div>
                            </a>
                        </div>
                        <div class="col-md-auto col-lg mb-3">
                            <a href="{{ route('admin.contacts.index') }}" class="btn btn-block quick-action-btn shadow-sm position-relative h-100">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <div class="font-weight-bold">View Contacts</div>
                                @if($stats['unread_contacts'] > 0)
                                    <span class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border: 2px solid #fff;">{{ $stats['unread_contacts'] }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Auction Status Pie Chart
    const auctionStatusCtx = document.getElementById('auctionStatusChart').getContext('2d');
    const auctionStatusChart = new Chart(auctionStatusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($auction_chart_data['labels']) !!},
            datasets: [{
                data: {!! json_encode($auction_chart_data['data']) !!},
                backgroundColor: [
                    '#1cc88a', // Live - Green
                    '#f6c23e', // Upcoming - Yellow
                    '#36b9cc', // Pending - Blue
                    '#858796', // Closed - Gray
                    '#e74a3b'  // Cancelled - Red
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' auctions';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Monthly Auctions Line Chart
    const monthlyAuctionsCtx = document.getElementById('monthlyAuctionsChart').getContext('2d');
    let monthlyAuctionsChart = new Chart(monthlyAuctionsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthly_chart_data['labels']) !!},
            datasets: [{
                label: 'Auctions Created',
                data: {!! json_encode($monthly_chart_data['data']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' auctions';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Math.floor(value);
                        }
                    }
                }
            }
        }
    });

    function updateTrendChart(months) {
        document.getElementById('trendChartTitle').innerHTML = '<i class="fas fa-chart-line mr-2"></i>Auctions Trend (Last ' + months + ' Months)';
        
        document.getElementById('monthlyAuctionsChart').style.opacity = '0.5';

        fetch("{{ route('admin.dashboard.chart_data') }}?months=" + months)
            .then(response => response.json())
            .then(data => {
                monthlyAuctionsChart.data.labels = data.labels;
                monthlyAuctionsChart.data.datasets[0].data = data.data;
                monthlyAuctionsChart.update();
                document.getElementById('monthlyAuctionsChart').style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
                document.getElementById('monthlyAuctionsChart').style.opacity = '1';
            });
    }
</script>
@endpush



