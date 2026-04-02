<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserStrike;
use Yajra\DataTables\Facades\DataTables;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes.
     */
    public function index()
    {
        return view('admin.disputes.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = UserStrike::with(['user', 'auction', 'reporter'])->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        return DataTables::of($query)
            ->addColumn('user_info', function($strike) {
                return '<div class="d-flex align-items-center">
                            <img src="'.$strike->user->avatar_url.'" class="rounded-circle me-2" width="30" height="30">
                            <div>
                                <div class="fw-bold">'.$strike->user->name.'</div>
                                <div class="small text-muted">@'.$strike->user->username.'</div>
                            </div>
                        </div>';
            })
            ->addColumn('reporter_info', function($strike) {
                return '<div class="small">
                            <div class="fw-bold">'.$strike->reporter->name.'</div>
                            <div class="text-muted">@'.$strike->reporter->username.'</div>
                        </div>';
            })
            ->editColumn('status', function($strike) {
                $badges = [
                    'active' => 'danger',
                    'appealed' => 'warning text-dark',
                    'dismissed' => 'success',
                    'resolved' => 'info'
                ];
                $color = $badges[$strike->status] ?? 'secondary';
                return '<span class="badge bg-'.$color.'">'.ucfirst($strike->status).'</span>';
            })
            ->editColumn('type', function($strike) {
                return '<span class="small text-uppercase fw-bold">'.str_replace('_', ' ', $strike->type).'</span>';
            })
            ->addColumn('action', function($strike) {
                $html = '<div class="btn-group">';
                $html .= '<button type="button" class="btn btn-sm btn-primary" onclick="viewDispute('.$strike->id.')" title="View Details"><i class="fas fa-eye"></i></button>';
                
                if ($strike->status === 'appealed' || $strike->status === 'active') {
                    $html .= '<button type="button" class="btn btn-sm btn-success" onclick="resolveDispute('.$strike->id.', \'dismissed\')" title="Dismiss Strike"><i class="fas fa-check"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger" onclick="resolveDispute('.$strike->id.', \'resolved\')" title="Keep/Resolve Strike"><i class="fas fa-times"></i></button>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['user_info', 'reporter_info', 'status', 'type', 'action'])
            ->make(true);
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

        // If it was a seller misconduct report and we 'resolved' it (meaning we verified it), 
        // the strike stays active on the seller. 
        // If it was a buyer strike appeal and we 'dismissed' it, the buyer's strike is removed.

        return response()->json([
            'status' => true,
            'message' => 'Dispute updated successfully.'
        ]);
    }
}
