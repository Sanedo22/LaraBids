<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceBidRequest;
use App\Models\Auction;
use App\Services\BidService;
use Illuminate\Http\Request;
use Exception;

class BidController extends Controller
{
    protected $bidService;

    public function __construct(BidService $bidService)
    {
        $this->bidService = $bidService;
    }

    /**
     * Store a newly created bid in storage.
     */
    public function store(PlaceBidRequest $request, Auction $auction)
    {
        try {
            $user = auth()->user();

            if (!$user->isKycApproved()) {
                $message = 'You must complete your Identity Verification (KYC) before you can place a bid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $message], 403);
                }
                return redirect()->route('user.kyc.form')->with('error', $message);
            }

            if (!$user->isRegisteredFor($auction)) {
                $message = 'You must register for this auction before you can place a bid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $message], 403);
                }
                return redirect()->back()->with('error', $message);
            }

            $result = $this->bidService->placeBid($auction, $request->validated(), $user);

                $auction = $auction->fresh();
                $highestBid = $auction->bids()->first();
                $currentWinnerId = $highestBid ? (int)$highestBid->user_id : null;
                
                if ($currentWinnerId === (int)$user->id) {
                    $message = 'Great news! Your bid has been placed successfully and you are currently the highest bidder.';
                    if ($result['is_extended']) {
                        $message .= ' This auction has been extended by 5 minutes to give everyone a fair chance to respond.';
                    }

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => 'success', 
                            'message' => $message, 
                            'bid' => $result['bid'],
                            'current_price' => (float)$auction->current_price,
                            'min_increment' => (float)$auction->min_increment,
                            'is_winning' => true,
                            'current_user_id' => (int)$user->id,
                            'winner_username' => $user->username,
                            'total_bids' => (int)$auction->bids()->count(),
                            'end_time' => $auction->end_time->toIso8601String(),
                            'end_time_formatted' => $auction->end_time->format('F d, Y \a\t g:i A')
                        ]);
                    }
                    return redirect()->back()->with('success', $message);
                } else {
                    $minNextBid = number_format($auction->current_price + ($auction->min_increment ?? 0.01), 2);
                    $message = 'You were instantly outbid! Another user has a higher automatic "Proxy Bid" limit. To take the lead, you will need to bid at least ₹' . $minNextBid . '.';
                    
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => 'warning', 
                            'message' => $message, 
                            'bid' => $result['bid'],
                            'current_price' => (float)$auction->current_price,
                            'min_increment' => (float)$auction->min_increment,
                            'is_winning' => false,
                            'winner_id' => $currentWinnerId,
                            'current_user_id' => (int)$user->id,
                            'winner_username' => $highestBid ? $highestBid->user->username : null,
                            'total_bids' => (int)$auction->bids()->count(),
                            'end_time' => $auction->end_time->toIso8601String(),
                            'end_time_formatted' => $auction->end_time->format('F d, Y \a\t g:i A')
                        ]);
                    }
                    return redirect()->back()->with('warning', $message);
                }

        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
