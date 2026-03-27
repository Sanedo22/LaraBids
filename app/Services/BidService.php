<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Facades\DB;
use Exception;

class BidService
{
    public function placeBid(Auction $auction, array $bidData, $user): array
    {
        // 1. Check if auction is active
        if ($auction->status !== 'active') {
            throw new Exception('Bidding is only allowed on active auctions.');
        }

        // 2. Check if auction has started
        if ($auction->start_time->isFuture()) {
            throw new Exception('Bidding has not started yet for this auction.');
        }

        // 3. Check if auction has expired
        if ($auction->end_time->isPast()) {
            throw new Exception('This auction has already ended.');
        }

        // 3. User cannot bid on their own auction
        if ($auction->user_id === $user->id) {
            throw new Exception('You cannot bid on your own auction.');
        }

        return DB::transaction(function () use ($auction, $bidData, $user) {
            // Lock the auction for update to prevent race conditions
            $auction = Auction::where('id', $auction->id)->lockForUpdate()->first();
            
            $isExtended = false;
            $minIncrement = (float)($auction->min_increment ?? 100.00);

            \Illuminate\Support\Facades\Log::debug("Bidding started for auction {$auction->id} by user {$user->id}", [
                'bid_data' => $bidData,
                'current_price' => $auction->current_price
            ]);

            // 1. Handle Proxy Bidding Setup (Maximum Bid)
            if (isset($bidData['max_bid_amount']) && $bidData['max_bid_amount'] > 0) {
                $maxBidAmount = (float)$bidData['max_bid_amount'];
                
                // Ensure max_bid_amount is higher than current_price + increment
                $minimumRequired = (float)$auction->current_price + $minIncrement;
                if ($maxBidAmount < $minimumRequired) {
                    throw new Exception('Your maximum bid must be at least ₹' . number_format($minimumRequired, 2));
                }

                \App\Models\AutoBids::updateOrCreate(
                    ['user_id' => $user->id, 'auction_id' => $auction->id],
                    ['max_bid_amount' => $maxBidAmount, 'active' => true]
                );
                
                \Illuminate\Support\Facades\Log::debug("Proxy updated for user {$user->id} to {$maxBidAmount}");
            }

            // 2. Handle Manual Increment (Standard Bidding)
            $manualIncrement = (float)($bidData['increment'] ?? 0);
            $manualBidAmount = $manualIncrement > 0 ? (float)$auction->current_price + $manualIncrement : 0;

            if ($manualIncrement > 0) {
                if ($manualIncrement < $minIncrement) {
                    throw new Exception('The minimum increment required is ₹' . number_format($minIncrement, 2));
                }
                if ($manualIncrement > Auction::MAX_INCREMENT_ALLOWED) {
                    throw new Exception('The bid increment cannot exceed ₹' . number_format(Auction::MAX_INCREMENT_ALLOWED, 2));
                }
            }

            // 3. Determine the "Current Target"
            $currentUserProxy = $auction->autoBids()->where('user_id', $user->id)->where('active', true)->first();
            
            // If they provided a manual increment, that is a FORCED jump.
            // If they only have a proxy limit, we use that as their potential.
            $userMax = (float)max($manualBidAmount, ($currentUserProxy ? $currentUserProxy->max_bid_amount : 0));

            // Get current top bid
            $currentTopBid = $auction->bids()->first(); 
            $isAlreadyWinning = ($currentTopBid && (int)$currentTopBid->user_id === (int)$user->id);

            // Get the best competing Proxy
            $topCompetingProxy = $auction->autoBids()
                ->where('active', true)
                ->where('user_id', '!=', $user->id)
                ->orderByDesc('max_bid_amount')
                ->first();

            $winnerId = null;
            $newPrice = (float)$auction->current_price;

            \Illuminate\Support\Facades\Log::debug("Competition analysis", [
                'manualIncrement' => $manualIncrement,
                'manualBidAmount' => $manualBidAmount,
                'userMax' => $userMax,
                'isAlreadyWinning' => $isAlreadyWinning,
                'topCompetingProxyUser' => $topCompetingProxy ? $topCompetingProxy->user_id : 'none',
                'topCompetingProxyMax' => $topCompetingProxy ? (float)$topCompetingProxy->max_bid_amount : 0
            ]);

            if ($manualIncrement > 0) {
                // SCENARIO 1: Manual "Strong" Bid. 
                // This ALWAYS becomes the current price (if higher than current).
                // It still has to compete with other proxies.
                if ($topCompetingProxy) {
                    $competingMax = (float)$topCompetingProxy->max_bid_amount;
                    if ($competingMax > $manualBidAmount) {
                        // Competition stays leader. Price = manualBidAmount + increment.
                        $winnerId = $topCompetingProxy->user_id;
                        $newPrice = min($competingMax, $manualBidAmount + $minIncrement);
                        \Illuminate\Support\Facades\Log::debug("Manual bid outbid by competing proxy", ['winnerId' => $winnerId, 'newPrice' => $newPrice]);
                    } elseif ($competingMax == $manualBidAmount) {
                        // Tie at manual bid level. Earlier proxy wins.
                        $winnerId = $topCompetingProxy->user_id;
                        $newPrice = $manualBidAmount;
                        \Illuminate\Support\Facades\Log::debug("Manual bid tied with competing proxy", ['winnerId' => $winnerId, 'newPrice' => $newPrice]);
                    } else {
                        // Manual bid takes lead
                        $winnerId = $user->id;
                        $newPrice = $manualBidAmount;
                        \Illuminate\Support\Facades\Log::debug("Manual bid successful (outbid proxy)", ['newPrice' => $newPrice]);
                    }
                } else {
                    // No proxy competition. Just jump to manual amount.
                    $winnerId = $user->id;
                    $newPrice = $manualBidAmount;
                    \Illuminate\Support\Facades\Log::debug("Manual bid successful (no competition)", ['newPrice' => $newPrice]);
                }
            } else {
                // SCENARIO 2: Pure Proxy Update / Outbid (No manual increment)
                if ($topCompetingProxy) {
                    $competingMax = (float)$topCompetingProxy->max_bid_amount;
                    if ($competingMax > $userMax) {
                        $winnerId = $topCompetingProxy->user_id;
                        $newPrice = min($competingMax, $userMax + $minIncrement);
                    } elseif ($competingMax == $userMax) {
                        // Tie at proxy level. Earlier wins.
                        if ($topCompetingProxy->created_at->lessThan(($currentUserProxy ? $currentUserProxy->created_at : now()))) {
                            $winnerId = $topCompetingProxy->user_id;
                        } else {
                            $winnerId = $user->id;
                        }
                        $newPrice = $userMax;
                    } else {
                        $winnerId = $user->id;
                        $newPrice = min($userMax, $competingMax + $minIncrement);
                    }
                    \Illuminate\Support\Facades\Log::debug("Outcome: Proxy competition result", ['winnerId' => $winnerId, 'newPrice' => $newPrice]);
                } else {
                    // No competing proxies.
                    if ($currentTopBid && !$isAlreadyWinning) {
                        // Outbid static leader by only 1 increment
                        $winnerId = $user->id;
                        $newPrice = min($userMax, (float)$currentTopBid->amount + $minIncrement);
                        \Illuminate\Support\Facades\Log::debug("Outcome: Proxy outbids static leader", ['winnerId' => $winnerId, 'newPrice' => $newPrice]);
                    } elseif ($isAlreadyWinning) {
                        $winnerId = $user->id;
                        $newPrice = (float)$auction->current_price; // Already winning, proxy update only
                        \Illuminate\Support\Facades\Log::debug("Outcome: Winning user proxy update");
                    } else {
                        // First bid ever.
                        $winnerId = $user->id;
                        $newPrice = (float)$auction->starting_price;
                        \Illuminate\Support\Facades\Log::debug("Outcome: First proxy bid at starting price");
                    }
                }
            }

            $newPrice = max($newPrice, (float)$auction->current_price);

            // Anti-Sniping
            $now = now();
            $newEndTime = $auction->end_time->copy();

            if ($now->greaterThanOrEqualTo($auction->end_time->copy()->subMinutes(2))) {
                $newEndTime = $auction->end_time->copy()->addMinutes(5);
                $isExtended = true;
            }
            
            $auction->end_time = $newEndTime;

            $previousWinner = $currentTopBid ? $currentTopBid->user : null;
            $priceChanged = abs((float)($currentTopBid ? $currentTopBid->amount : 0) - $newPrice) > 0.001;
            $winnerChanged = !$currentTopBid || (int)$currentTopBid->user_id !== (int)$winnerId;

            if ($winnerChanged || $priceChanged) {
                $bid = Bid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $winnerId,
                    'amount' => $newPrice, 
                ]);

                $auction->update([
                    'current_price' => $newPrice,
                    'end_time' => $newEndTime
                ]);
                
                \Illuminate\Support\Facades\Log::debug("Auction updated with new bid", ['bidId' => $bid->id]);

                // 4. Notifications
                // A) Notify the PREVIOUS winner that they were outbid
                if ($previousWinner && (int)$previousWinner->id !== (int)$winnerId) {
                    $previousWinner->notify(new \App\Notifications\OutbidNotification($auction, $newPrice));
                }

                // B) Notify the NEW bidder if they were INSTANTLY outbid by a proxy
                // (This handles the case where $user is our $user but $winnerId is someone else)
                if ((int)$user->id !== (int)$winnerId && (int)$user->id !== (int)($previousWinner ? $previousWinner->id : 0)) {
                   $user->notify(new \App\Notifications\OutbidNotification($auction, $newPrice));
                }
            } else {
                $bid = $currentTopBid;
                if ($isExtended) {
                    $auction->update(['end_time' => $newEndTime]);
                }
                \Illuminate\Support\Facades\Log::debug("No bid created (no change)");
            }

            if ($isExtended && $auction->user) {
                $auction->user->notify(new \App\Notifications\AuctionExtendedNotification($auction));
            }

            // Broadcast the new bid dynamically
            if ($bid) {
                event(new \App\Events\BidPlaced($auction, $bid));
            }

            return [
                'bid' => $bid,
                'is_extended' => $isExtended
            ];
        });
    }
}
