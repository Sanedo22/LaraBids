<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Payment::with(['user', 'auction'])->latest();

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

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('txnid', function ($row) {
                    return '<div class="text-dark">' . e($row->txnid) . '</div>';
                })
                ->addColumn('user', function ($row) {
                    $buyer = $row->user ? e($row->user->name) : 'N/A';
                    $seller = ($row->auction && $row->auction->user) ? e($row->auction->user->name) : 'N/A';
                    
                    return '<div style="font-size: 0.85rem; line-height: 1.4;">'
                        . '<div class="text-dark font-weight-bold">Buyer: ' . $buyer . '</div>'
                        . '<div class="text-muted">Seller: ' . $seller . '</div>'
                        . '</div>';
                })
                ->addColumn('auction', function ($row) {
                    if (!$row->auction) return '<span class="text-muted small">N/A</span>';
                    return '<a href="'.route('admin.auctions.show', $row->auction_id).'" class="text-primary font-weight-bold" style="font-size: 0.85rem;">' . e($row->auction->title) . '</a>';
                })
                ->addColumn('method', function ($row) {
                    return '<span class="text-uppercase small font-weight-bold text-dark">' . e($row->payment_method) . '</span>';
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="text-dark font-weight-bold">₹' . number_format($row->amount, 2) . '</span>';
                })
                ->addColumn('fee', function ($row) {
                    $fee = $row->commission_amount ?? ($row->amount * 0.05);
                    
                    return '<div class="text-right">'
                        . '<div class="font-weight-bold text-dark">₹' . number_format($fee, 2) . '</div>'
                        . '</div>';
                })
                ->editColumn('status', function ($row) {
                    $color = match ($row->status) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        default   => 'secondary',
                    };
                    return '<span class="badge badge-' . $color . ' py-1 px-2 text-uppercase" style="font-size: 0.65rem;">' . e($row->status) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return '<span class="text-muted small">' . $row->created_at->format('M d, Y H:i') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="'.route('admin.payments.show', $row->id).'" class="btn btn-outline-info btn-sm btn-action">
                                <i class="fas fa-eye"></i>
                            </a>';
                })
                ->rawColumns(['txnid', 'user', 'auction', 'method', 'amount', 'fee', 'status', 'created_at', 'action'])
                ->make(true);
        }

        return view('admin.payments.index');
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'auction' => function($q) {
            $q->withTrashed()->with('bids.user');
        }]);
        $totalBids = $payment->auction ? $payment->auction->bids->count() : 0;
        
        return view('admin.payments.show', compact('payment', 'totalBids'));
    }

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
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Transaction ID', 'Buyer Name', 'Auction Item', 'Method', 'Total Amount', 'Commission', 'Payment Status', 'Timestamp'];

        $callback = function() use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->txnid,
                    $payment->user->name ?? 'N/A',
                    $payment->auction->title ?? 'N/A',
                    $payment->payment_method,
                    $payment->amount,
                    $payment->commission_amount ?? ($payment->amount * 0.05),
                    $payment->status,
                    $payment->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
