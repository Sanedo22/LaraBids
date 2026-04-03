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

        $strike->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Dispute updated successfully.',
            'data' => $strike
        ]);
    }
}
