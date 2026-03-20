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
                    $html = '<div class="d-flex flex-column">';
                    $html .= '<span class="font-weight-bold text-dark">' . e($row->txnid) . '</span>';
                    if ($row->payu_id) {
                        $html .= '<span class="text-muted small">PayU: ' . e($row->payu_id) . '</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('user', function ($row) {
                    if (!$row->user) return 'N/A';
                    $html = '<div class="d-flex flex-column">';
                    $html .= '<span class="font-weight-bold text-dark">' . e($row->user->name) . '</span>';
                    $html .= '<span class="text-muted small">' . e($row->user->email) . '</span>';
                    $html .= '</div>';
                    return $html;
                })
                ->filterColumn('user', function($query, $keyword) {
                    $query->whereHas('user', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('auction', function ($row) {
                    if (!$row->auction) return 'N/A';
                    $html = '<div class="d-flex flex-column">';
                    $title = e($row->auction->title);
                    if(strlen($title) > 40) $title = substr($title, 0, 40) . '...';
                    $html .= '<span class="text-primary font-weight-bold d-inline-block text-truncate" style="max-width:250px;" title="'.e($row->auction->title).'">' . $title . '</span>';
                    $html .= '<span class="text-muted small">ID: #' . $row->auction_id . '</span>';
                    $html .= '</div>';
                    return $html;
                })
                ->filterColumn('auction', function($query, $keyword) {
                    $query->whereHas('auction', function($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="font-weight-bold text-dark">₹' . number_format($row->amount, 2) . '</span>';
                })
                ->addColumn('fee', function ($row) {
                    return '<span class="text-muted small">₹' . number_format($row->amount * 0.05, 2) . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $badgeClass = match ($row->status) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        default   => 'secondary',
                    };
                    return '<span class="badge badge-' . $badgeClass . ' py-2 px-3 text-uppercase font-weight-bold" style="font-size: 0.7rem; border-radius: 0.5rem;">'
                        . e($row->status)
                        . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return '<div class="d-flex flex-column">'
                        . '<span class="font-weight-bold text-dark">' . $row->created_at->format('d M, Y') . '</span>'
                        . '<span class="text-muted small">' . $row->created_at->format('h:i A') . '</span>'
                        . '</div>';
                })
                ->rawColumns(['txnid', 'user', 'auction', 'amount', 'fee', 'status', 'created_at'])
                ->make(true);
        }

        return view('admin.payments.index');
    }
}
