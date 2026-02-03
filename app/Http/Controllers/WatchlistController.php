<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function toggle(Auction $auction)
    {
        $userId = auth()->id();
        
        $watchlist = Watchlist::where('user_id', $userId)
            ->where('auction_id', $auction->id)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'removed',
                    'message' => 'Removed from watchlist'
                ]);
            }
            return back()->with('success', 'Removed from watchlist');
        }

        Watchlist::create([
            'user_id' => $userId,
            'auction_id' => $auction->id
        ]);

        if (request()->ajax()) {
            return response()->json([
                'status' => 'added',
                'message' => 'Added to watchlist'
            ]);
        }
        return back()->with('success', 'Added to watchlist');
    }
}
