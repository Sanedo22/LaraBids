<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    // Dashboard
    public function index()
    {
        return view('website.user.dashboard');
    }

    // My bids
    public function myBids()
    {
        $bids = auth()->user()->bids()
            ->with('auction')
            ->latest()
            ->get()
            ->unique('auction_id');

        return view('website.user.my-bids', compact('bids'));
    }

    // Winning items
    public function winningItems()
    {
        // TODO: Fetch user's winning items from database
        return view('website.user.winning-items');
    }

    // Wishlist
    public function wishlist()
    {
        // TODO: Fetch user's wishlist from database
        return view('website.user.wishlist');
    }

    // Profile
    public function profile()
    {
        return view('website.user.profile');
    }
}
