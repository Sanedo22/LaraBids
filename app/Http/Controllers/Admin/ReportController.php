<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Payment;
use App\Models\Bid;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Revenue Growth (Last 12 Months)
        $revenueData = [];
        $revenueLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueLabels[] = $month->format('M Y');
            $revenueData[] = Payment::where('status', 'success')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
        }

        // 2. Auctions by Year (Last 5 Years)
        $auctionYearsData = [];
        $auctionYearsLabels = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $auctionYearsLabels[] = $year;
            $auctionYearsData[] = Auction::whereYear('created_at', $year)->count();
        }

        // 3. Top Categories Distribution (Parent Only)
        $topCategories = Category::whereNull('parent_id')
            ->withCount('auctions')
            ->orderBy('auctions_count', 'desc')
            ->take(5)
            ->get();
        
        $pieLabels = $topCategories->pluck('name');
        $pieData = $topCategories->pluck('auctions_count');

        // 4. Payment Success Rate
        $paymentStats = [
            'success' => Payment::where('status', 'success')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed'  => Payment::where('status', 'failed')->count(),
        ];

        // 5. Top Sellers by Volume
        $topSellers = User::role('user')
            ->withCount('auctions')
            ->orderBy('auctions_count', 'desc')
            ->take(5)
            ->get();

        // 6. Top Bidders by Activity
        $topBidders = User::role('user')
            ->withCount('bids')
            ->orderBy('bids_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.reports', compact(
            'revenueLabels', 'revenueData',
            'auctionYearsLabels', 'auctionYearsData',
            'pieLabels', 'pieData',
            'paymentStats',
            'topSellers',
            'topBidders'
        ));
    }
}
