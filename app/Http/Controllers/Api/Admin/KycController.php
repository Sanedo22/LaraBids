<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use App\Http\Resources\KycResource;

class KycController extends Controller
{
    //display a listing of the KYC requests with filtering, sorting, and pagination
    public function index(Request $request)
    {
        $status    = $request->get('status', 'all'); // all | pending | approved | rejected | resubmitted
        $idType    = $request->get('id_type', 'all');
        $sort      = $request->get('sort', 'latest'); // latest | oldest
        $search    = $request->get('search', '');

        $query = Kyc::with('user');

        // Status filter
        if ($status !== 'all') {
            if ($status === 'resubmitted') {
                $query->where('status', 'pending')->where('is_resubmitted', true);
            } else {
                $query->where('status', $status);
            }
        }

        // ID Type filter
        if ($idType !== 'all') {
            $query->where('id_type', $idType);
        }

        // Date filtering
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        match ($sort) {
            'oldest' => $query->oldest(),
            default  => $query->latest(),
        };

        // Pagination
        $perPage = $request->get('per_page', 15);
        $kycs = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => KycResource::collection($kycs),
            'meta'   => [
                'total'        => $kycs->total(),
                'per_page'     => $kycs->perPage(),
                'current_page' => $kycs->currentPage(),
                'last_page'    => $kycs->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified KYC request.
     */
    public function show($id)
    {
        $kyc = Kyc::with('user')->find($id);

        if (!$kyc) {
            return response()->json([
                'status'  => false,
                'message' => 'KYC record not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new KycResource($kyc)
        ]);
    }

    /**
     * Update the status of the KYC request (Approve/Reject).
     */
    public function updateStatus(Request $request, $id)
    {
        $kyc = Kyc::find($id);

        if (!$kyc) {
            return response()->json([
                'status'  => false,
                'message' => 'KYC record not found'
            ], 404);
        }

        $validated = $request->validate([
            'status'     => 'required|in:approved,rejected',
            'admin_note' => 'required_if:status,rejected|nullable|string|max:500',
        ]);

        $kyc->update([
            'status'         => $validated['status'],
            'admin_note'     => $validated['status'] === 'rejected' ? $validated['admin_note'] : null,
            'is_resubmitted' => false,
        ]);

        $kyc->user->notify(new \App\Notifications\KycStatusUpdatedNotification($kyc));

        return response()->json([
            'status'  => true,
            'message' => 'KYC status updated to ' . $validated['status'],
            'data'    => new KycResource($kyc)
        ]);
    }
}

