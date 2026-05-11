<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
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
            ->paginate(12)
            ->withQueryString();

        $categories = Category::topLevel()->active()->with('children')->get();
        $currentCategory = null;

        if ($request->filled('category')) {
            $currentCategory = Category::where('slug', $request->category)->first();
        }

        if ($request->ajax()) {
            return view('website.auctions._auction_grid', compact('auctions'));
        }

        return view('website.auctions.index', compact('auctions', 'categories', 'currentCategory'));
    }

    // Show auction
    public function show($id)
    {
        $auction = Auction::with(['user', 'category', 'images', 'bids.user', 'watchlists' => function($q) {
            if (auth()->check()) {
                $q->where('user_id', auth()->id());
            } else {
                $q->whereRaw('1 = 0');
            }
        }])->findOrFail($id);

        // if auction is not active, closed, or cancelled, only owner or admin can view
        if ($auction->status !== 'active' && $auction->status !== 'closed' && $auction->status !== 'cancelled') {
            if (!auth()->check()) {
                abort(404);
            }

            // Only owner, admin, or super admin can view restricted auctions
            $user = auth()->user();
            if ($user->id !== $auction->user_id && $user->role !== 'admin' && $user->role !== 'super admin') {
                 abort(404);
            }
        }

        // Fetch related active auctions (only where end_time has not passed)
        $relatedAuctions = Auction::where('status', 'active')
            ->where('id', '!=', $id)
            ->where('category_id', $auction->category_id)
            ->where('end_time', '>', now())
            ->with(['category', 'images', 'user'])
            ->take(4)
            ->get();

        if ($relatedAuctions->count() < 4) {
            $filler = Auction::where('status', 'active')
                ->where('id', '!=', $id)
                ->where('end_time', '>', now())
                ->whereNotIn('id', $relatedAuctions->pluck('id'))
                ->with(['category', 'images', 'user'])
                ->inRandomOrder()
                ->take(4 - $relatedAuctions->count())
                ->get();
            $relatedAuctions = $relatedAuctions->merge($filler);
        }

        return view('website.auctions.show', compact('auction', 'relatedAuctions'));
    }

    // Create form
    public function create()
    {
        $categories = Category::topLevel()->active()->with('children')->get();
        
        $categoryTree = $categories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'children' => $cat->children->map(function($child) {
                    return ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug];
                })
            ];
        });

        if (!auth()->user()->isKycApproved()) {
            return redirect()->route('user.kyc.form')->with('error', 'You must complete your Identity Verification (KYC) before you can create an auction.');
        }

        return view('website.auctions.create', compact('categories', 'categoryTree'));
    }

    // Store auction
    public function store(StoreAuctionRequest $request)
    {
        $auction = $this->auctionService->createAuction($request->validated(), auth()->user());

        $message = $auction->status === 'active' 
            ? 'Auction created successfully and is now live!' 
            : 'Auction created successfully! It will be live after admin approval.';

        return redirect()->route('auctions.show', $auction->id)
            ->with('success', $message);
    }

    // Search auctions
    public function search(Request $request)
    {
        return $this->index($request);
    }

    // Edit form
    public function edit($id)
    {
        $auction = Auction::findOrFail($id);

        // Ownership check
        if ($auction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Bid check: Cannot edit if bids exist
        if ($auction->bids()->exists()) {
            return redirect()->route('auctions.show', $auction->id)
                ->with('error', 'You cannot edit this auction because bids have already been placed.');
        }

        $isWithin24Hours = $auction->created_at && $auction->created_at->diffInHours(now()) <= 24;
        if ($auction->status === 'pending' && !$isWithin24Hours) {
            abort(403, 'You can only edit a pending auction within 24 hours of its creation.');
        }

        $categories = Category::topLevel()->active()->with('children')->get();
        $categoryTree = $categories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'children' => $cat->children->map(function($child) {
                    return ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug];
                })
            ];
        });

        return view('website.auctions.edit', compact('auction', 'categories', 'categoryTree'));
    }

    // Update auction
    public function update(UpdateAuctionRequest $request, $id)
    {
        $auction = Auction::findOrFail($id);
        
        // Bid check: Cannot update if bids exist
        if ($auction->bids()->exists()) {
            return redirect()->route('auctions.show', $auction->id)
                ->with('error', 'You cannot update this auction because bids have already been placed.');
        }

        // Ownership check is handled in the Request (authorize method)
        $isWithin24Hours = $auction->created_at && $auction->created_at->diffInHours(now()) <= 24;
        if ($auction->status === 'pending' && !$isWithin24Hours) {
            abort(403, 'You can only edit a pending auction within 24 hours of its creation.');
        }
        
        $this->auctionService->updateAuction($auction, $request->validated());
        
        $message = $auction->fresh()->status === 'pending' 
            ? 'Auction updated successfully! It is now pending admin approval again.' 
            : 'Auction updated successfully!';

        return redirect()->route('auctions.show', $auction->id)
            ->with('success', $message);
    }

    /**
     * Remove the specified auction from storage.
     */
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);

        if ($auction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->auctionService->deleteAuction($id);

        return redirect()->route('user.my-auctions')
            ->with('success', 'Auction deleted successfully!');
    }
}

