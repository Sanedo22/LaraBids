@extends('admin.layouts.admin')

@section('title', 'Settings - LaraBids')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">System Settings</h1>

    <div class="row">

        <div class="col-lg-8">

            <!-- General Configuration -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Configuration</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label class="font-weight-bold">Site Title</label>
                                <input type="text" class="form-control" placeholder="Auction Admin"
                                    value="Auction Admin">
                            </div>
                            <div class="col-sm-6">
                                <label class="font-weight-bold">Site Email</label>
                                <input type="email" class="form-control" placeholder="admin@auction.com"
                                    value="admin@auction.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Default Currency</label>
                            <select class="form-control">
                                <option selected>INR (₹)</option>
                                <option>USD ($)</option>
                                <option>EUR (€)</option>
                                <option>GBP (£)</option>
                                <option>INR (₹)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Auction Commission (%)</label>
                            <input type="number" class="form-control" value="10">
                            <small class="text-muted">Percentage taken from each successful
                                sale.</small>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary shadow-sm">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Payment Gateways -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Gateways</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold">Stripe Integration</h6>
                            <small>Process credit card payments securely.</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="stripeSwitch"
                                checked>
                            <label class="custom-control-label" for="stripeSwitch"></label>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold">PayPal Payments</h6>
                            <small>Accept PayPal and Venmo.</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="paypalSwitch">
                            <label class="custom-control-label" for="paypalSwitch"></label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">

            <!-- Maintenance Mode -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center" style="cursor: default;">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Maintenance Mode</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Inactive</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <button class="btn btn-warning btn-sm btn-block mt-3">Enable Maintenance</button>
                </div>
            </div>

            <!-- Admin Security -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Admin Security</h6>
                </div>
                <div class="card-body text-center">
                    <p class="small text-muted">Manage your administrative access and credentials.</p>
                    <button class="btn btn-outline-primary btn-sm btn-block mb-2">Change
                        Password</button>
                    <button class="btn btn-outline-danger btn-sm btn-block">Two-Factor Auth</button>
                </div>
            </div>

        </div>

    </div>
@endsection
