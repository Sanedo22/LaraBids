<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Facades\DB;
use Exception;

class BidService
{
    public function placeBid(Auction $auction, array $bidData, $user): Bid
    {
        // 1. Check if auction is active
        if ($auction->status !== 'active') {
            throw new Exception('Bidding is only allowed on active auctions.');
        }

        // 2. Check if auction has expired
        if ($auction->end_time->isPast()) {
            throw new Exception('This auction has already ended.');
        }

        // 3. User cannot bid on their own auction
        if ($auction->user_id === $user->id) {
            throw new Exception('You cannot bid on your own auction.');
        }

        // 4. Ensure bid is higher than current price
        if ($bidData['amount'] <= $auction->current_price) {
            throw new Exception('Your bid must be higher than the current price.');
        }

        return DB::transaction(function () use ($auction, $bidData, $user) {
            // Create the bid
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $bidData['amount'],
            ]);

            // Update auction price
            $auction->update([
                'current_price' => $bidData['amount']
            ]);

            return $bid;
        });
    }
}
