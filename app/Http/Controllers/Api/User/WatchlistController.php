<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Watchlist;
use App\Http\Resources\AuctionResource;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * Get user's watchlist
     */
    public function index()
    {
        $watchlists = auth()->user()
            ->watchlist()
            ->with(['auction.category', 'auction.images', 'auction.user'])
            ->latest()
            ->get();

        $auctions = $watchlists->map(function ($watchlist) {
            return $watchlist->auction;
        });

        return response()->json([
            'success' => true,
            'data' => AuctionResource::collection($auctions)
        ]);
    }

    /**
     * Add auction to watchlist
     */
    public function store($auctionId)
    {
        $auction = Auction::findOrFail($auctionId);
        $userId = auth()->id();

        // Check if already in watchlist
        $exists = Watchlist::where('user_id', $userId)
            ->where('auction_id', $auctionId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Auction is already in your watchlist'
            ], 409);
        }

        Watchlist::create([
            'user_id' => $userId,
            'auction_id' => $auctionId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to watchlist',
            'data' => [
                'auction_id' => $auctionId,
                'auction_title' => $auction->title
            ]
        ], 201);
    }

    /**
     * Remove auction from watchlist
     */
    public function destroy($auctionId)
    {
        $userId = auth()->id();

        $watchlist = Watchlist::where('user_id', $userId)
            ->where('auction_id', $auctionId)
            ->first();

        if (!$watchlist) {
            return response()->json([
                'success' => false,
                'message' => 'Auction not found in your watchlist'
            ], 404);
        }

        $watchlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from watchlist'
        ]);
    }
}
