<?php

namespace App\Http\Controllers;

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
            $this->bidService->placeBid($auction, $request->validated(), auth()->user());

            return redirect()->back()->with('success', 'Your bid has been placed successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
