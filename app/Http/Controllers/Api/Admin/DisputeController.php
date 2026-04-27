<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserStrike;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes for mobile admin.
     */
    public function index(Request $request)
    {
        $query = UserStrike::with(['user', 'auction', 'reporter'])->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $disputes = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data' => $disputes
        ]);
    }

    /**
     * Resolve a dispute.
     */
    public function resolve(Request $request, $id)
    {
        $strike = UserStrike::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:dismissed,resolved',
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $newStatus = $request->status;
        if ($newStatus === 'resolved') {
            $newStatus = 'active'; // Uphold the strike so it penalizes the user
        }

        $strike->update([
            'status' => $newStatus,
            'admin_note' => $request->admin_note
        ]);

        if ($newStatus === 'active') {
            $user = $strike->user;
            if ($user && $user->unpaid_strikes_count >= \App\Models\User::MAX_GLOBAL_STRIKES) {
                // Suspend the user globally if limit is reached
                $user->delete(); 
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Dispute updated successfully.',
            'data' => $strike
        ]);
    }
}
