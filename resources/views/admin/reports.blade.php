@extends('admin.layouts.admin')

@section('title', 'Reports & Analytics - LaraBids')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports & Analytics</li>
                </ol>
            </nav>
        </div>
    </div>
    <p class="mb-4 text-muted small">Visualize platform performance, bidding trends, and distribution based on real-time data.</p>

    <!-- Top Summary Metrics Card Row -->
    <div class="row mb-4 text-center">
        @php
            $totalCommission = \App\Models\Payment::where('status', 'success')->sum('commission_amount');
            if($totalCommission == 0) $totalCommission = \App\Models\Payment::where('status', 'success')->sum('amount') * 0.05;
        @endphp
        <div class="col-md-3">
             <div class="card shadow py-3 border-bottom-primary">
                 <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Lifetime GMV</div>
                 <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format(\App\Models\Payment::where('status', 'success')->sum('amount'), 2) }}</div>
             </div>
        </div>
        <div class="col-md-3">
             <div class="card shadow py-3 border-bottom-success">
                 <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Platform Revenue</div>
                 <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($totalCommission, 2) }}</div>
             </div>
        </div>
        <div class="col-md-3">
             <div class="card shadow py-3 border-bottom-info">
                 <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Auctions Listed</div>
                 <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format(\App\Models\Auction::count()) }}</div>
             </div>
        </div>
        <div class="col-md-3">
             <div class="card shadow py-3 border-bottom-warning">
                 <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total User Bids</div>
                 <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format(\App\Models\Bid::count()) }}</div>
             </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">

        <div class="col-xl-8 col-lg-7">

            <!-- Area Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line mr-2"></i>Monthly Revenue Growth (₹)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                    <hr>
                    <div class="small text-muted italic">Tracking successful transaction volume over the rolling 12-month period.</div>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Auctions Listings Growth</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 320px;">
                        <canvas id="myBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table Row -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Top Sellers</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr><th>User</th><th class="text-right">Listings</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($topSellers as $user)
                                        <tr>
                                            <td class="small font-weight-bold">{{ $user->name }}</td>
                                            <td class="text-right small">{{ $user->auctions_count }} items</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Most Active Bidders</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr><th>User</th><th class="text-right">Bids Placed</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($topBidders as $user)
                                        <tr>
                                            <td class="small font-weight-bold">{{ $user->name }}</td>
                                            <td class="text-right small">{{ $user->bids_count }} bids</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side: Pie Chart & Funnels -->
        <div class="col-xl-4 col-lg-5">
            <!-- Pie Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Listing Distribution</h6>
                </div>
                <div class="card-body" style="min-height: 400px;">
                    <div class="chart-pie pt-4 pb-2" style="height: 300px;">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($pieLabels as $index => $label)
                            <span class="mr-2 d-inline-block mb-1">
                                <i class="fas fa-circle" style="color: {{ ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'][$index % 5] }}"></i> {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Payment Success Rate Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Success Health</h6>
                </div>
                <div class="card-body">
                    @php
                        $totalP = array_sum($paymentStats);
                        $successRate = $totalP > 0 ? round(($paymentStats['success'] / $totalP) * 100, 1) : 0;
                    @endphp
                    <h4 class="small font-weight-bold">Gateway Conversion Rate <span class="float-right">{{ $successRate }}%</span></h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successRate }}%"></div>
                    </div>
                    
                    <div class="row text-center mt-3">
                        <div class="col-4 border-right">
                             <div class="text-xs text-uppercase text-success font-weight-bold">Success</div>
                             <div class="h6 mb-0 font-weight-bold">{{ $paymentStats['success'] }}</div>
                        </div>
                        <div class="col-4 border-right">
                             <div class="text-xs text-uppercase text-warning font-weight-bold">Pending</div>
                             <div class="h6 mb-0 font-weight-bold">{{ $paymentStats['pending'] }}</div>
                        </div>
                        <div class="col-4">
                             <div class="text-xs text-uppercase text-danger font-weight-bold">Failed</div>
                             <div class="h6 mb-0 font-weight-bold">{{ $paymentStats['failed'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Link Card -->
            <div class="card shadow mb-4 bg-gradient-primary text-white">
                 <div class="card-body text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x mb-3 opacity-50"></i>
                    <h5 class="font-weight-bold">Detailed Ledger</h5>
                    <p class="small mb-4">Need to audit specific transaction details?</p>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-4">See Detailed Log</a>
                 </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-assets/vendor/chart.js/Chart.min.js') }}"></script>

    <script>
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec); return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) { s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep); }
            if ((s[1] || '').length < prec) { s[1] = s[1] || ''; s[1] += new Array(prec - s[1].length + 1).join('0'); }
            return s.join(dec);
        }

        // 1. Revenue Chart
        new Chart(document.getElementById("myAreaChart"), {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueLabels) !!},
                datasets: [{
                    label: "Transactions", lineTension: 0.3, backgroundColor: "rgba(78, 115, 223, 0.05)", borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3, pointBackgroundColor: "rgba(78, 115, 223, 1)", pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3, pointHoverBackgroundColor: "rgba(78, 115, 223, 1)", pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10, pointBorderWidth: 2, data: {!! json_encode($revenueData) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } }],
                    yAxes: [{
                        ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return '₹' + number_format(value); } },
                        gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                    }],
                },
                legend: { display: false },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", titleFontColor: '#6e707e', titleFontSize: 14,
                    borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, intersect: false, mode: 'index', caretPadding: 10,
                    callbacks: { label: function(tooltipItem, chart) { return 'Revenue: ₹' + number_format(tooltipItem.yLabel); } }
                }
            }
        });

        // 2. Bar Chart
        new Chart(document.getElementById("myBarChart"), {
            type: 'bar',
            data: {
                labels: {!! json_encode($auctionYearsLabels) !!},
                datasets: [{
                    label: "Auctions", backgroundColor: "#4e73df", hoverBackgroundColor: "#2e59d9", borderColor: "#4e73df",
                    data: {!! json_encode($auctionYearsData) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{ gridLines: { display: false, drawBorder: false }, maxBarThickness: 50 }],
                    yAxes: [{ ticks: { min: 0, maxTicksLimit: 5, padding: 10 }, gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }],
                },
                legend: { display: false },
                tooltips: { marginTitleBottom: 10, titleFontColor: '#6e707e', titleFontSize: 14, backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10 },
            }
        });

        // 3. Pie Chart
        new Chart(document.getElementById("myPieChart"), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($pieLabels) !!},
                datasets: [{
                    data: {!! json_encode($pieData) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: { backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10, },
                legend: { display: false },
                cutoutPercentage: 80,
            },
        });
    </script>
@endpush
