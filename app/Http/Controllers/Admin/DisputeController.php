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
                $avatar = $strike->user ? $strike->user->avatar_url : asset('website/images/default-avatar.png');
                $name = $strike->user ? $strike->user->name : 'Unknown User';
                $username = $strike->user ? $strike->user->username : 'unknown';
                return '<div class="d-flex align-items-center">
                            <img src="'.$avatar.'" class="rounded-circle me-2" width="30" height="30">
                            <div>
                                <div class="fw-bold">'.$name.'</div>
                                <div class="small text-muted">@'.$username.'</div>
                            </div>
                        </div>';
            })
            ->addColumn('reporter_info', function($strike) {
                $name = $strike->reporter ? $strike->reporter->name : 'System / Unknown';
                $username = $strike->reporter ? $strike->reporter->username : 'system';
                return '<div class="small">
                            <div class="fw-bold">'.$name.'</div>
                            <div class="text-muted">@'.$username.'</div>
                        </div>';
            })
            ->addColumn('status_badge', function($strike) {
                $badges = [
                    'active' => 'danger',
                    'appealed' => 'warning text-dark',
                    'dismissed' => 'success',
                    'resolved' => 'info'
                ];
                $color = $badges[$strike->status] ?? 'secondary';
                return '<span class="badge bg-'.$color.'">'.ucfirst($strike->status).'</span>';
            })
            ->addColumn('type_badge', function($strike) {
                return '<span class="small text-uppercase fw-bold">'.str_replace('_', ' ', $strike->type).'</span>';
            })
            ->editColumn('created_at', function($strike) {
                return $strike->created_at->format('M d, Y');
            })
            ->addColumn('reason_short', function($strike) {
                return \Illuminate\Support\Str::limit($strike->reason, 50);
            })
            ->addColumn('action', function($strike) {
                $html = '<div class="d-flex justify-content-center">';
                $html .= '<button type="button" class="btn btn-sm btn-outline-primary btn-action mx-1" onclick="viewDispute('.$strike->id.')" title="View Details"><i class="fas fa-eye"></i></button>';
                
                if ($strike->status === 'appealed' || $strike->status === 'active') {
                    $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-action mx-1" onclick="resolveDispute('.$strike->id.', \'dismissed\')" title="Dismiss Strike"><i class="fas fa-check"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-outline-danger btn-action mx-1" onclick="resolveDispute('.$strike->id.', \'resolved\')" title="Keep/Resolve Strike"><i class="fas fa-times"></i></button>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['user_info', 'reporter_info', 'status_badge', 'type_badge', 'action'])
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

        // If it was a seller misconduct report and we 'resolved' it (meaning we verified it), 
        // the strike stays active on the seller. 
        // If it was a buyer strike appeal and we 'dismissed' it, the buyer's strike is removed.

        return response()->json([
            'status' => true,
            'message' => 'Dispute updated successfully.'
        ]);
    }
}
