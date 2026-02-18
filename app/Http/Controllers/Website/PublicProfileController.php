<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    /**
     * Display the public profile of a seller.
     */
    public function show($id)
    {
        $seller = User::with(['auctions' => function ($query) {
            $query->active()->latest();
        }])->findOrFail($id);

        $stats = [
            'total_auctions' => $seller->auctions()->count(),
            'active_auctions' => $seller->auctions()->active()->count(),
            'completed_auctions' => $seller->auctions()->where('status', 'closed')->count(),
            'member_since' => $seller->created_at->format('M Y'),
        ];

        return view('website.sellers.show', compact('seller', 'stats'));
    }
}
