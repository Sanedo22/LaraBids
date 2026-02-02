<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use App\Http\Requests\StoreAuctionRequest;
use App\Services\AuctionService;
use Illuminate\Http\Request;

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
        $auctions = $this->auctionService->getFilteredAuctions($request)
            ->paginate(9)
            ->withQueryString();

        $categories = Category::where('is_active', true)->get();

        return view('website.auctions.index', compact('auctions', 'categories'));
    }

    // Show auction
    public function show($id)
    {
        $auction = Auction::with(['user', 'category', 'images', 'bids.user'])->findOrFail($id);

        // if auction is not active, only owner or admin can view
        if ($auction->status !== 'active') {
            if (!auth()->check()) {
                abort(404);
            }

            // Check if user is owner or admin
            // Assuming 'role' column exists on User model for admin check
            $user = auth()->user();
            if ($user->id !== $auction->user_id && $user->role !== 'admin' && $user->role !== 'super admin') {
                 abort(404);
            }
        }

        return view('website.auctions.show', compact('auction'));
    }

    // Create form
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('website.auctions.create', compact('categories'));
    }

    // Store auction
    public function store(StoreAuctionRequest $request)
    {
        $auction = $this->auctionService->createAuction($request->validated(), auth()->user());

        return redirect()->route('auctions.show', $auction->id)
            ->with('success', 'Auction created successfully! It will be live after admin approval.');
    }

    // Search auctions
    public function search(Request $request)
    {
        return $this->index($request);
    }
}

