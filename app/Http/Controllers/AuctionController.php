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
        $auction = Auction::with(['user', 'category'])->findOrFail($id);
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
            ->with('success', 'Auction created successfully!');
    }

    // Search auctions
    public function search(Request $request)
    {
        return $this->index($request);
    }
}

