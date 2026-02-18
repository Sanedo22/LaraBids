<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\User;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Contact;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\UserResource;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics and data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total_auctions' => Auction::count(),

            'active_auctions' => Auction::where('status', 'active')
                ->where('end_time', '>', now())
                ->count(),

            'pending_auctions' => Auction::where('status', 'pending')->count(),

            'closed_auctions' => Auction::where('status', 'active')
                ->where('end_time', '<=', now())
                ->count(),

            'cancelled_auctions' => Auction::where('status', 'cancelled')->count(),

            'total_users' => User::role('user')->count(),

            'new_users_this_month' => User::role('user')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            'total_bids' => Bid::count(),

            'bids_today' => Bid::whereDate('created_at', today())->count(),

            'total_categories' => Category::count(),

            'active_categories' => Category::where('is_active', true)->count(),

            'total_contacts' => Contact::count(),

            'unread_contacts' => Contact::where('status', 'unread')->count(),
        ];

        // Recent auctions
        $recent_auctions = Auction::with(['user', 'category', 'images', 'bids.user'])
            ->latest()
            ->take(5)
            ->get();

        // Recent users
        $recent_users = User::role('user')
            ->latest()
            ->take(5)
            ->get();

        // Chart data - Auctions by status
        $auction_chart_data = [
            'labels' => ['Active', 'Pending', 'Closed', 'Cancelled'],
            'data' => [
                $stats['active_auctions'],
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

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'stats' => $stats,
                'recent_auctions' => AuctionResource::collection($recent_auctions),
                'recent_users' => UserResource::collection($recent_users),
                'auction_chart_data' => $auction_chart_data,
                'monthly_chart_data' => $monthly_chart_data,
            ]
        ]);
    }
}
