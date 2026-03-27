<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'auction.user']);

        // Custom Filters
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            default  => $query->latest(),
        };

        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => PaymentResource::collection($payments),
            'meta'   => [
                'total'        => $payments->total(),
                'per_page'     => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with(['user', 'auction' => function($q) {
            $q->withTrashed()->with('bids.user', 'user');
        }])->find($id);

        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $totalBids = $payment->auction ? $payment->auction->bids->count() : 0;
        
        return response()->json([
            'status' => true,
            'data'   => new PaymentResource($payment),
            'extra'  => [
                'total_bids' => $totalBids
            ]
        ]);
    }

    /**
     * Mark a payout as paid.
     */
    public function markPayoutPaid($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $additionalData = $payment->additional_data ?? [];
        $additionalData['payout_status'] = 'paid';
        $additionalData['payout_at'] = now();
        
        $payment->update([
            'additional_data' => $additionalData
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Payout marked as paid successfully',
            'data'    => new PaymentResource($payment)
        ]);
    }

    /**
     * Export payments as CSV stream.
     */
    public function export(Request $request)
    {
        $query = Payment::with(['user', 'auction']);

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->latest()->get();

        $filename = "payments_export_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Transaction ID', 'Buyer Name', 'Auction Item', 'Method', 'Total Amount', 'Platform Fee', 'Status', 'Timestamp'];

        $callback = function() use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                $fee = $payment->commission_amount ?? ($payment->amount * 0.05);
                fputcsv($file, [
                    $payment->id,
                    $payment->txnid,
                    $payment->user->name ?? 'N/A',
                    $payment->auction->title ?? 'N/A',
                    'ONLINE',
                    $payment->amount,
                    $fee,
                    strtoupper($payment->status),
                    $payment->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
