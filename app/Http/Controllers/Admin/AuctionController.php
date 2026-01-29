<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\AuctionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AuctionController extends Controller
{
    protected $auctionService;

    public function __construct(AuctionService $auctionService)
    {
        $this->auctionService = $auctionService;
    }

    // List auctions
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->auctionService->getFilteredAuctions($request, false)
                ->withTrashed();
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user', function($row){
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->filterColumn('user', function($query, $keyword) {
                    $query->whereHas('user', function($q) use($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('current_price', function($row){
                    return '$' . number_format($row->current_price, 2);
                })
                ->addColumn('status', function($row){
                    $badgeClass = $row->getStatusBadgeClass();
                    return '<span class="badge badge-'.$badgeClass.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('end_time', function($row){
                    return $row->end_time ? $row->end_time->format('M d, Y H:i') : 'N/A';
                })
                ->addColumn('action', function($row){
                    $btn = '';
                    
                    // View (Always visible)
                    $btn .= '<a href="'.route('admin.auctions.show', $row->id).'" class="btn btn-info btn-sm mr-1" title="View"><i class="fas fa-eye"></i></a>';
                    
                    if ($row->trashed()) {
                         // Restore
                         $btn .= '<button type="button" class="btn btn-success btn-sm mr-1 restore-auction" data-id="'.$row->id.'" data-url="'.route('admin.auctions.restore', $row->id).'" title="Restore"><i class="fas fa-trash-restore"></i></button>';
                         
                         // Permanent Delete
                         $btn .= '<button type="button" class="btn btn-danger btn-sm force-delete-auction" data-id="'.$row->id.'" data-url="'.route('admin.auctions.force_delete', $row->id).'" title="Permanent Delete"><i class="fas fa-times"></i></button>';
                    } else {
                        // Delete (Soft Delete)
                        $btn .= '<button type="button" class="btn btn-danger btn-sm delete-auction" data-id="'.$row->id.'" data-url="'.route('admin.auctions.destroy', $row->id).'" title="Delete"><i class="fas fa-trash"></i></button>';
                    }
                    
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.auctions.index', [
            'total_auctions' => Auction::count()
        ]);
    }

    // Show auction
    public function show($id)
    {
        $auction = Auction::withTrashed()->findOrFail($id);
        return view('admin.auctions.show', compact('auction'));
    }

    // Delete auction
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);
        $auction->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'Auction deleted successfully.']);
        }

        return redirect()->route('admin.auctions.index')->with('success', 'Auction deleted successfully.');
    }

    // Cancel auction
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $auction = Auction::findOrFail($id);
        $this->auctionService->updateStatus($auction, 'cancelled', $request->reason);

        if (request()->ajax()) {
             return response()->json(['success' => 'Auction cancelled successfully.']);
        }

        return redirect()->back()->with('success', 'Auction cancelled successfully.');
    }

    // Approve auction
    public function approve($id)
    {
        $auction = Auction::findOrFail($id);
        $this->auctionService->updateStatus($auction, 'active');

        return redirect()->back()->with('success', 'Auction approved (activated) successfully.');
    }

    // Restore auction
    public function restore($id)
    {
        $auction = Auction::withTrashed()->findOrFail($id);
        $auction->restore();

        if (request()->ajax()) {
            return response()->json(['success' => 'Auction restored successfully.']);
        }

        return redirect()->back()->with('success', 'Auction restored successfully.');
    }

    // Force delete
    public function forceDelete($id)
    {
        $auction = Auction::withTrashed()->findOrFail($id);
        $auction->forceDelete();

        if (request()->ajax()) {
            return response()->json(['success' => 'Auction permanently deleted.']);
        }

        return redirect()->route('admin.auctions.index')->with('success', 'Auction permanently deleted.');
    }
}
