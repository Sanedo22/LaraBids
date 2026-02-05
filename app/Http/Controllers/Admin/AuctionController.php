<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Services\AuctionService;
use App\Http\Requests\Admin\CancelAuctionRequest;
use Illuminate\Support\Str;
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

            $data = $this->auctionService
                ->getFilteredAuctions($request, false)
                ->withTrashed();

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('title', function ($row) {
                    return '<span title="'.e($row->title).'">'
                        . Str::limit($row->title, 30)
                        . '</span>';
                })

                ->addColumn('user', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('category', function ($row) {
                    if ($row->category) {
                        return '<span class="badge badge-primary">'.$row->category->name.'</span>';
                    }
                    return '<span class="badge badge-secondary">N/A</span>';
                })

                ->filterColumn('category', function ($query, $keyword) {
                    $query->whereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('image', function ($row) {
                    $imgUrl = $row->image
                        ? asset('storage/'.$row->image)
                        : asset('admin-assets/img/no-image.png');

                    return '<img src="'.$imgUrl.'" width="50" height="50"
                            class="rounded border" style="object-fit:cover;">';
                })

                ->addColumn('current_price', function ($row) {
                    return '$'.number_format($row->current_price, 2);
                })

                ->addColumn('status', function ($row) {

                    $displayStatus = $row->status;

                    if ($row->status === 'active' && $row->end_time && $row->end_time->isPast()) {
                        $displayStatus = 'closed';
                    }

                    $badgeClass = match ($displayStatus) {
                        'active'    => 'success',
                        'pending'   => 'info',
                        'closed'    => 'secondary',
                        'cancelled' => 'danger',
                        default     => 'warning',
                    };

                    return '<span class="badge badge-'.$badgeClass.'">'
                        . ucfirst($displayStatus)
                        . '</span>';
                })

                ->addColumn('end_time', function ($row) {
                    return $row->end_time
                        ? $row->end_time->format('M d, Y H:i')
                        : 'N/A';
                })

                ->addColumn('action', function ($row) {

                    $btn = '';

                    // View
                    $btn .= '<a href="'.route('admin.auctions.show', $row->id).'"
                                class="btn btn-info btn-sm mr-1" title="View">
                                <i class="fas fa-eye"></i></a>';

                    // Approve
                    if ($row->status === 'pending' && !$row->trashed()) {
                        $btn .= '<form action="'.route('admin.auctions.approve', $row->id).'"
                                    method="POST" class="d-inline mr-1">'
                                    .csrf_field().
                                    '<button type="submit"
                                        class="btn btn-success btn-sm" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>';
                    }

                    if ($row->trashed()) {

                        // Restore
                        $btn .= '<button type="button"
                                    class="btn btn-success btn-sm mr-1 restore-auction"
                                    data-id="'.$row->id.'"
                                    data-url="'.route('admin.auctions.restore', $row->id).'"
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>';

                        // Force Delete
                        $btn .= '<button type="button"
                                    class="btn btn-danger btn-sm force-delete-auction"
                                    data-id="'.$row->id.'"
                                    data-url="'.route('admin.auctions.force_delete', $row->id).'"
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>';

                    } else {

                        // Soft Delete
                        $btn .= '<button type="button"
                                    class="btn btn-danger btn-sm delete-auction"
                                    data-id="'.$row->id.'"
                                    data-url="'.route('admin.auctions.destroy', $row->id).'"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    }

                    return $btn;
                })

                ->rawColumns(['image', 'title', 'category', 'status', 'action'])

                ->filter(function ($query) {
                    if (request()->has('search') && isset(request('search')['value'])) {
                        $keyword = request('search')['value'];

                        if (!empty($keyword)) {
                            $query->where(function ($q) use ($keyword) {
                                $q->where('title', 'like', "%{$keyword}%")
                                  ->orWhere('cancellation_reason', 'like', "%{$keyword}%")
                                  ->orWhereHas('user', fn($u) =>
                                      $u->where('name', 'like', "%{$keyword}%")
                                  )
                                  ->orWhereHas('category', fn($c) =>
                                      $c->where('name', 'like', "%{$keyword}%")
                                  );
                            });
                        }
                    }
                })

                ->make(true);
        }

        return view('admin.auctions.index', [
            'total_auctions' => Auction::count()
        ]);
    }

    // Show auction
    public function show($id)
    {
        $auction = Auction::with([
            'user',
            'category',
            'images' => fn($q) => $q->orderBy('sort_order'),
            'bids.user'
        ])->withTrashed()->findOrFail($id);

        return view('admin.auctions.show', compact('auction'));
    }

    // Delete auction
    public function destroy($id)
    {
        $this->auctionService->deleteAuction($id);

        return request()->ajax()
            ? response()->json(['success' => 'Auction deleted successfully.'])
            : redirect()->route('admin.auctions.index')->with('success', 'Auction deleted successfully.');
    }

    // Cancel auction
    public function cancel(CancelAuctionRequest $request, $id)
    {
        $auction = Auction::findOrFail($id);
        $this->auctionService->updateStatus($auction, 'cancelled', $request->reason);

        return request()->ajax()
            ? response()->json(['success' => 'Auction cancelled successfully.'])
            : redirect()->back()->with('success', 'Auction cancelled successfully.');
    }

    // Approve auction
    public function approve($id)
    {
        $auction = Auction::findOrFail($id);
        $this->auctionService->updateStatus($auction, 'active');

        return redirect()->back()->with('success', 'Auction approved successfully.');
    }

    // Restore auction
    public function restore($id)
    {
        $this->auctionService->restoreAuction($id);

        return request()->ajax()
            ? response()->json(['success' => 'Auction restored successfully.'])
            : redirect()->back()->with('success', 'Auction restored successfully.');
    }

    // Force delete
    public function forceDelete($id)
    {
        $this->auctionService->forceDeleteAuction($id);

        return request()->ajax()
            ? response()->json(['success' => 'Auction permanently deleted.'])
            : redirect()->route('admin.auctions.index')->with('success', 'Auction permanently deleted.');
    }
}
