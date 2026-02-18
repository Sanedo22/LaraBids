<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\SearchAuctionRequest;
use App\Models\Auction;
use App\Models\User;
use App\Services\AuctionService;
use App\Http\Resources\AuctionResource;
use App\Models\Category;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    protected $auctionService;

    public function __construct(AuctionService $auctionService)
    {
        $this->auctionService = $auctionService;   // You can inject services here if needed
    }
    public function index(Request $request)
    {
        $auctions = $this->auctionService
            ->getFilteredAuctions($request)
            ->with(['user', 'category']) // Eager load relationships
            ->paginate(10);

        $categories = Category::topLevel()->active()->get();

        $subcategories = collect();
        $parentCategory = null;

        if ($request->has('category')) {
            $currentCategory = category::where('slug', $request->category)->first();

            if ($currentCategory->parent_id) {
                $parentCategory = $currentCategory->parent;

                $subcategories = $currentCategory
                    ->children()
                    ->active()
                    ->get();
            } else {
                $parentCategory = $currentCategory;

                $subcategories = $currentCategory
                    ->children()
                    ->active()
                    ->get();
            }
        }
        return AuctionResource::collection($auctions)->additional([
            'success' => true,
            'categories' => $categories,
            'parent_category' => $parentCategory,
            'subcategories' => $subcategories,
        ]);
    }

    // Get single auction
    public function show($id)
    {
        $auction = $this->auctionService->getAuctionById($id);

        return new AuctionResource($auction);
    }

    public function store(StoreAuctionRequest $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            if (!$request->has('user_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please provide user_id or use API token authentication.'
                ], 401);
            }
            
            $user = User::findOrFail($request->user_id);
        }

        $auction = $this->auctionService->createAuction(
            $request->validated(),
            $user
        );

        return new AuctionResource($auction);
    }

    /**
     * Get all bids for a specific auction
     */
    public function bids($id)
    {
        $auction = Auction::findOrFail($id);

        $bids = $auction->bids()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'auction_id' => $auction->id,
                'auction_title' => $auction->title,
                'bids' => \App\Http\Resources\BidResource::collection($bids)
            ]
        ]);
    }

    /**
     * Place a bid on an auction
     */
    public function placeBid($id, \App\Http\Requests\PlaceBidRequest $request)
    {
        $auction = Auction::findOrFail($id);
        
        try {
            $bidService = app(\App\Services\BidService::class);
            $bid = $bidService->placeBid($auction, $request->validated(), auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Bid placed successfully!',
                'data' => [
                    'bid' => new \App\Http\Resources\BidResource($bid),
                    'auction' => [
                        'id' => $auction->id,
                        'current_price' => $auction->fresh()->current_price
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Search and filter auctions with advanced criteria
     * Public endpoint for Angular frontend
     */
    public function search(SearchAuctionRequest $request)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;

        // Get filtered auctions using the service
        $auctions = $this->auctionService
            ->getFilteredAuctions($request, false) // false = don't force activeOnly
            ->with(['user', 'category', 'images'])
            ->paginate($perPage);

        // Get statistics for metadata
        $stats = $this->auctionService->getSearchStatistics($request);

        // Build filters applied object
        $filtersApplied = [];
        
        if ($request->filled('q') || $request->filled('keyword')) {
            $filtersApplied['keyword'] = $request->input('q') ?? $request->input('keyword');
        }
        
        if ($request->filled('category')) {
            $filtersApplied['category'] = $request->input('category');
        }
        
        if ($request->filled('category_id')) {
            $filtersApplied['category_id'] = $request->input('category_id');
        }
        
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $filtersApplied['price_range'] = [
                'min' => $request->input('min_price'),
                'max' => $request->input('max_price'),
            ];
        }
        
        if ($request->filled('status')) {
            $filtersApplied['status'] = $request->input('status');
        }
        
        if ($request->filled('sort')) {
            $filtersApplied['sort'] = $request->input('sort');
        }

        return AuctionResource::collection($auctions)->additional([
            'success' => true,
            'filters_applied' => $filtersApplied,
            'statistics' => $stats,
            'available_filters' => [
                'statuses' => ['active', 'pending', 'closed', 'cancelled', 'past', 'all'],
                'sort_options' => ['latest', 'price_asc', 'price_desc', 'ending_soon'],
            ],
        ]);
    }

    //list auctions created by the authenticated user
    public function managedAuctions(Request $request){
        $perPage = $request->input('per_page', 10);

        $status = $request->input('status');

        $query = Auction::where('user_id', auth()->id())
            ->with(['category', 'images'])
            ->withCount('bids');

            if($status && $status !== 'all'){
                if($status === 'active'){
                    $query->active();
                } else {
                    $query->where('status', $status);
                }
            }

            $auctions = $query->latest()->paginate($perPage);

            return AuctionResource::collection($auctions)->additional([
                'success' => true,
                'filters_applied' => [
                    'status' => $status,
                ],
            ]);
    }
}
