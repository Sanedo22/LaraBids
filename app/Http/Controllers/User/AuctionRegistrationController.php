<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use Illuminate\Http\Request;
use Exception;

class AuctionRegistrationController extends Controller
{
    //register user for an auction
    public function register(Auction $auction)
    {
        try {
            $user = auth()->user();

            // Check if KYC is approved
            if (!$user->isKycApproved()) {
                return redirect()->route('user.kyc.form')->with('error', 'You must complete your Identity Verification (KYC) before you can register for an auction.');
            }

            // Check if already registered
            if ($user->isRegisteredFor($auction)) {
                return redirect()->back()->with('warning', 'You are already registered for this auction.');
            }

            // Register the user
            AuctionRegistration::create([
                'user_id' => $user->id,
                'auction_id' => $auction->id,
                'status' => 'registered'
            ]);

            $message = 'You have successfully registered for the auction: ' . $auction->title . '.';
            if ($auction->start_time->isFuture()) {
                $message .= ' We will notify you 30 minutes before bidding opens!';
            } else {
                $message .= ' You can now place bids!';
            }

            return redirect()->back()->with('success', $message);

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to register for the auction. Please try again later.');
        }
    }
}
