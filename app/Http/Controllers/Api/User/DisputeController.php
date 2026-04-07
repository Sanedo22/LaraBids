<?php

namespace App\Http\Controllers\Api\User;

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
            return response()->json([
                'status' => false,
                'message' => 'This strike cannot be appealed.'
            ], 400);
        }

        $request->validate([
            'appeal_reason' => 'required|string|min:20|max:1000',
        ]);

        $strike->update([
            'status' => 'appealed',
            'appeal_reason' => $request->appeal_reason,
            'appeal_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Your appeal has been submitted successfully and is under review.',
            'data' => $strike
        ]);
    }

    /**
     * Report a seller for misconduct.
     */
    public function reportSeller(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        // Authorization: Any authenticated user can report a seller, except the seller themselves
        if ($auction->user_id === Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot report yourself.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:20|max:1000',
        ]);

        $strike = UserStrike::create([
            'user_id' => $auction->user_id, // The seller
            'auction_id' => $auction->id,
            'reported_by' => Auth::id(),
            'reason' => $request->reason,
            'type' => 'seller_misconduct',
            'status' => 'appealed', // Set to 'appealed' by default so it sits in the admin review queue
            'appeal_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Seller has been reported. Admin will review the case.',
            'data' => $strike
        ], 201);
    }
}
