<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\User;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Kyc;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [

            'live_auctions' => Auction::where('status', 'active')
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->count(),

            'upcoming_auctions' => Auction::where('status', 'active')
                ->where('start_time', '>', now())
                ->where('end_time', '>', now())
                ->count(),

            'pending_auctions' => Auction::where('status', 'pending')->count(),

            'pending_kycs' => Kyc::where('status', 'pending')->count(),

            'closed_auctions' => Auction::where(function($q) {
                $q->where('status', 'closed')
                  ->orWhere(function($sq) {
                      $sq->where('status', 'active')->where('end_time', '<=', now());
                  });
            })->count(),

            'cancelled_auctions' => Auction::where('status', 'cancelled')->count(),

            'total_users' => User::role('user')->count(),

            'total_bids' => Bid::count(),

            'bids_today' => Bid::whereDate('created_at', today())->count(),

            'active_categories' => Category::where('is_active', true)->count(),

            'unread_contacts' => Contact::where('status', 'unread')->count(),

            // Real payment stats based on PayU online integration
            'total_sales' => Payment::where('status', 'success')->sum('amount') ?? 0,
            
            'platform_fee' => Payment::where('status', 'success')->sum('commission_amount') ?? 0,
            
            'successful_payments' => Payment::where('status', 'success')->count() ?? 0,
            'pending_payments' => Payment::where('status', 'pending')->count() ?? 0,
        ];

        // Recent auctions
        $recent_auctions = Auction::with(['user', 'category'])
            ->latest()
            ->take(5)
            ->get();

        // Pending KYCs
        $recent_kycs = Kyc::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Chart data - Auctions by status
        $auction_chart_data = [
            'labels' => ['Live', 'Upcoming', 'Pending', 'Closed', 'Cancelled'],
            'data' => [
                $stats['live_auctions'],
                $stats['upcoming_auctions'],
                $stats['pending_auctions'],
                $stats['closed_auctions'],
                $stats['cancelled_auctions'],
            ],
        ];

        // Chart data - Auctions per month (last 6 months)
        $monthly_auctions = [];
        $monthly_labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $monthly_labels[] = $date->format('M Y');

            $monthly_auctions[] = Auction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        $monthly_chart_data = [
            'labels' => $monthly_labels,
            'data' => $monthly_auctions,
        ];

        return view(
            'admin.dashboard',
            compact(
                'stats',
                'recent_auctions',
                'recent_kycs',
                'auction_chart_data',
                'monthly_chart_data'
            )
        );
    }

    public function blank()
    {
        return view('admin.blank');
    }

    public function chartData(Request $request)
    {
        $months = max(1, (int) $request->get('months', 6));
        $monthly_auctions = [];
        $monthly_labels = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $monthly_labels[] = $date->format('M Y');

            $monthly_auctions[] = Auction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return response()->json([
            'labels' => $monthly_labels,
            'data' => $monthly_auctions,
        ]);
    }
}
   
  