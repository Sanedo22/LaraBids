<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserStrike;
use App\Models\Auction;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    /**
     * Submit an appeal for a strike.
     */
    public function submitAppeal(Request $request, $id)
    {
        $strike = UserStrike::where('user_id', Auth::id())->findOrFail($id);

        if ($strike->status !== 'active') {
            return redirect()->back()->with('error', 'This strike cannot be appealed.');
        }

        $request->validate([
            'appeal_reason' => 'required|string|min:20|max:1000',
        ]);

        $strike->update([
            'status' => 'appealed',
            'appeal_reason' => $request->appeal_reason,
            'appeal_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Your appeal has been submitted successfully and is under review.');
    }

    /**
     * Report a seller for misconduct.
     */
    public function reportSeller(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        // Basic authorization: Only bidders or winners can report
        $hasBid = $auction->bids()->where('user_id', Auth::id())->exists();
        if (!$hasBid && $auction->user_id !== Auth::id()) {
             // If not a bidder and not the owner (owner shouldn't report self but check logic)
             // Allow winners too
             if($auction->winner_id !== Auth::id()){
                return redirect()->back()->with('error', 'You are not authorized to report this seller.');
             }
        }

        $request->validate([
            'reason' => 'required|string|min:20|max:1000',
        ]);

        // Create a 'pending' strike/report for the seller
        UserStrike::create([
            'user_id' => $auction->user_id, // The seller
            'auction_id' => $auction->id,
            'reported_by' => Auth::id(),
            'reason' => $request->reason,
            'type' => 'seller_misconduct',
            'status' => 'appealed', // Set to 'appealed' by default so it sits in the admin review queue
            'appeal_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Seller has been reported. Admin will review the case.');
    }
}
