<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Services\AuctionService;
use App\Http\Resources\AuctionResource;
use Illuminate\Support\Facades\Validator;

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
        $query = $this->auctionService
            ->getFilteredAuctions($request, false)
            ->select('auctions.*')
            ->withTrashed()
            ->with(['user', 'category', 'images', 'bids.user']);

        if ($request->has('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('category', function($cat) use ($search) {
                          $cat->where('name', 'like', "%{$search}%");
                      });
                    
                    if(is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }
        }

        $auctions = $query->paginate(10);

        return AuctionResource::collection($auctions)->additional([
            'status' => true,
            'message' => 'Auctions retrieved successfully'
        ]);
    }

    // Create auction
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|exists:users,id',
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'status'         => 'nullable|in:active,pending,closed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->hasFile('images')) {
            $data['images'] = $request->file('images');
            if (!is_array($data['images'])) {
                $data['images'] = [$data['images']];
            }
        }

        $user = \App\Models\User::find($request->user_id);

        $auction = $this->auctionService->createAuction($data, $user);

        return response()->json([
            'status'  => true,
            'message' => 'Auction created successfully',
            'data'    => new AuctionResource($auction)
        ], 201);
    }

    // Show single auction
    public function show($id)
    {
        $auction = Auction::withTrashed()
            ->with(['user', 'category', 'images', 'bids.user'])
            ->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new AuctionResource($auction)
        ]);
    }

    // Update auction
    public function update(Request $request, $id)
    {
        $auction = Auction::withTrashed()->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id'    => 'sometimes|exists:categories,id',
            'title'          => 'sometimes|string|max:255',
            'description'    => 'sometimes|string',
            'starting_price' => 'sometimes|numeric|min:0',
            'start_time'     => 'sometimes|date',
            'end_time'       => 'sometimes|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $auction->update($request->except('images'));

        return response()->json([
            'status'  => true,
            'message' => 'Auction updated successfully',
            'data'    => new AuctionResource($auction)
        ]);
    }

    // Approve auction
    public function approve($id)
    {
        $auction = Auction::withTrashed()->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $this->auctionService->updateStatus($auction, 'active');

        return response()->json([
            'status'  => true,
            'message' => 'Auction approved successfully',
            'data'    => new AuctionResource($auction->fresh())
        ]);
    }

    // Cancel auction
    public function cancel(Request $request, $id)
    {
        $auction = Auction::withTrashed()->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $this->auctionService->updateStatus($auction, 'cancelled', $request->reason);

        return response()->json([
            'status'  => true,
            'message' => 'Auction cancelled successfully',
            'data'    => new AuctionResource($auction->fresh())
        ]);
    }

    // Soft delete
    public function destroy($id)
    {
        $auction = Auction::find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $this->auctionService->deleteAuction($id);

        return response()->json([
            'status' => true,
            'message' => 'Auction moved to trash'
        ]);
    }

    // Restore auction
    public function restore($id)
    {
        $auction = Auction::withTrashed()->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $this->auctionService->restoreAuction($id);

        return response()->json([
            'status' => true,
            'message' => 'Auction restored successfully'
        ]);
    }

    // Force delete
    public function forceDelete($id)
    {
        $auction = Auction::withTrashed()->find($id);

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        $this->auctionService->forceDeleteAuction($id);

        return response()->json([
            'status' => true,
            'message' => 'Auction permanently deleted'
        ]);
    }
}
