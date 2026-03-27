<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of settings.
     * Currently returns a structured response of mock settings as the web view is static.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Settings retrieved successfully',
            'data' => [
                'general' => [
                    'site_name' => 'LaraBids',
                    'tagline' => 'Your Premier Online Auction Platform',
                    'admin_email' => 'admin@larabids.com',
                    'support_email' => 'support@larabids.com',
                    'currency' => 'INR',
                    'timezone' => 'Asia/Kolkata',
                ],
                'auction' => [
                    'commission_rate' => 10,
                    'min_bid_increment' => 100,
                    'max_duration_days' => 30,
                    'require_approval' => true,
                ],
                'payment_gateways' => [
                    'stripe' => ['active' => true],
                    'paypal' => ['active' => false],
                    'razorpay' => ['active' => false],
                ]
            ]
        ]);
    }
}
