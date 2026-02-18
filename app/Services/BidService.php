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

        $increment = $bidData['increment'];
        $totalAmount = $auction->current_price + $increment;

        // 4. Ensure increment meets minimum requirement
        $minIncrement = $auction->min_increment ?? 0.01;
        if ($increment < $minIncrement) {
            throw new Exception('The minimum increment required is $' . number_format($minIncrement, 2));
        }

        // 5. Ensure increment does not exceed max limit
        if ($increment > Auction::MAX_INCREMENT_ALLOWED) {
            throw new Exception('The bid increment cannot exceed $' . number_format(Auction::MAX_INCREMENT_ALLOWED, 2));
        }

        return DB::transaction(function () use ($auction, $totalAmount, $user) {
            // Create the bid
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $totalAmount, 
            ]);

            // Update auction price
            $auction->update([
                'current_price' => $totalAmount
            ]);

            return $bid;
        });
    }
}
