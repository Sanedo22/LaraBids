@extends('admin.layouts.admin')

@section('title', 'Payments - LaraBids')

@push('styles')
    <link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Payments & Transactions</h1>
    <p class="mb-4">Track all successful auction sales, commissions, and payouts.</p>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transaction History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Auction Item</th>
                            <th>Buyer</th>
                            <th>Amount</th>
                            <th>Commission (10%)</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#TXN-49021</td>
                            <td>Vintage Rolex Submariner</td>
                            <td>Tiger Nixon</td>
                            <td>₹12,500.00</td>
                            <td>₹1,250.00</td>
                            <td>Stripe</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td>2024/01/20</td>
                        </tr>
                        <tr>
                            <td>#TXN-49020</td>
                            <td>MacBook Pro M2</td>
                            <td>Garrett Winters</td>
                            <td>₹3,200.00</td>
                            <td>₹320.00</td>
                            <td>PayPal</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td>2024/01/19</td>
                        </tr>
                        <tr>
                            <td>#TXN-49019</td>
                            <td>1964 Ferrari GTO Model</td>
                            <td>Ashton Cox</td>
                            <td>₹2,400.00</td>
                            <td>₹240.00</td>
                            <td>Credit Card</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td>2024/01/18</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Page level plugins -->
    <script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
@endpush
