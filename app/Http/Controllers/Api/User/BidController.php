<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BidResource;
use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    /**
     * List all bids by authenticated user
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $bids = Bid::where('user_id', auth()->id())
            ->with(['auction.category', 'auction.images', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return BidResource::collection($bids)->additional([
            'success' => true,
        ]);
    }

    /**
     * List active bids (on ongoing auctions)
     */
    public function active(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $bids = Bid::where('user_id', auth()->id())
            ->whereHas('auction', function($q) {
                $q->where('status', 'active')
                  ->where('end_time', '>', now());
            })
            ->with(['auction.category', 'auction.images', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Calculate winning/losing counts
        $winningCount = 0;
        $losingCount = 0;
        
        foreach ($bids as $bid) {
            if ($bid->isWinning()) {
                $winningCount++;
            } else {
                $losingCount++;
            }
        }

        return BidResource::collection($bids)->additional([
            'success' => true,
            'meta' => [
                'total' => $bids->total(),
                'winning_count' => $winningCount,
                'losing_count' => $losingCount,
            ],
        ]);
    }

    //list won bids
    public function won(Request $request){
        $perPage = $request->input('per_page', 10);

        $bids = Bid::where('user_id', auth()->id())
        ->whereHas('auction', function($q){
            $q->where('end_time', '<=', now())
              ->whereColumn('current_price', 'bids.amount');
        })
        ->with(['auction.category', 'auction.images', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return BidResource::collection($bids)->additional([
            'success' => true,
        ]);
    }

    //lost bids
    public function lost(Request $request){
        $perPage = $request->input('per_page', 10);

        $bids = Bid::where('user_id', auth()->id())
        ->whereHas('auction', function($q){
            $q->where('end_time', '<=', now())
              ->whereColumn('current_price', '!=', 'bids.amount');
        })
        ->whereRaw('amount = (SELECT MAX(amount) FROM bids as b2 WHERE b2.auction_id = bids.auction_id AND b2.user_id = bids.user_id)')
        ->with(['auction.category', 'auction.images', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return BidResource::collection($bids)->additional([
            'success' => true,
        ]);
    }
}
