<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use Yajra\DataTables\Facades\DataTables;

class AdminKycController extends Controller
{
    public function index()
    {
        $total_kycs = Kyc::count();
        $pending_kycs = Kyc::where('status', 'pending')->count();
        return view('admin.kyc.index', compact('total_kycs', 'pending_kycs'));
    }

    public function data(Request $request)
    {
        $kycs = Kyc::with('user')->select('kycs.*');

        // Apply Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $kycs->where('status', $request->status);
        }

        // Apply ID Type Filter
        if ($request->filled('id_type') && $request->id_type !== 'all') {
            $kycs->where('id_type', $request->id_type);
        }

        // Apply Date Range Filter
        if ($request->filled('start_date')) {
            $kycs->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $kycs->whereDate('created_at', '<=', $request->end_date);
        }

        return DataTables::of($kycs)
            ->addIndexColumn()
            ->filterColumn('user', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('username', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user', function ($kyc) {
                return $kyc->user->username;
            })
            ->editColumn('id_type', function ($kyc) {
                return ucfirst(str_replace('_', ' ', $kyc->id_type));
            })
            ->editColumn('created_at', function ($kyc) {
                return $kyc->created_at->format('M d, Y H:i');
            })
            ->editColumn('gender', function ($kyc) {
                return ucfirst($kyc->gender);
            })
            ->editColumn('status', function ($kyc) {
                $badges = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger'
                ];
                $color = $badges[$kyc->status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst($kyc->status) . '</span>';
            })
            ->addColumn('action', function ($kyc) {
                return '<div class="d-flex justify-content-center gap-1">'
                    . '<a href="' . route('admin.kyc.show', $kyc->id) . '" class="btn btn-outline-info btn-sm btn-action" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>'
                    . '</div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $kyc = Kyc::with('user')->findOrFail($id);
        return view('admin.kyc.show', compact('kyc'));
    }

    public function approve($id)
    {
        $kyc = Kyc::findOrFail($id);
        $kyc->update(['status' => 'approved', 'admin_note' => null]);

        $kyc->user->notify(new \App\Notifications\KycStatusUpdatedNotification($kyc));

        return redirect()->back()->with('success', 'KYC approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $kyc = Kyc::findOrFail($id);
        $kyc->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        $kyc->user->notify(new \App\Notifications\KycStatusUpdatedNotification($kyc));

        return redirect()->back()->with('success', 'KYC rejected with note.');
    }
}
