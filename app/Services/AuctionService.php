<?php

namespace App\Services;

use Illuminate\Http\Request;

// ✅ Models
use App\Models\Auction;

// ✅ Helpers / Facades
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

// ✅ Notifications
use App\Notifications\AuctionCanceledNotification;
use App\Notifications\AuctionApprovedNotification;

class AuctionService
{
    // Get filtered auctions
    public function getFilteredAuctions(Request $request, bool $activeOnly = true)
    {
        $query = Auction::select('auctions.*');

        $status = $request->input('status', $activeOnly ? 'active' : 'all');

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'past' || $status === 'closed') {
            $query->where(function ($q) {
                $q->where('status', 'closed')
                    ->orWhere(function ($sq) {
                        $sq->where('status', 'active')
                            ->where('end_time', '<=', now());
                    });
            });
        } elseif ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        // Search filter
        if ($request->has('q')) {
            $search = $request->input('q');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQ) use ($search) {
                        $catQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Category filter
        if ($request->has('category')) {
            $categorySlug = $request->input('category');

            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
            $category = \App\Models\Category::where('slug', $categorySlug)->first();

            if ($category) {
                if ($category->parent_id === null) {
                    // Top level category: get this category and all its children
                    $categoryIds = $category->children()->pluck('id')->push($category->id);
                    $query->whereIn('category_id', $categoryIds);
                } else {
                    // Sub-category: get only this category
                    $query->where('category_id', $category->id);
                }
            }
        }

        // Price range filter
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        if ($request->filled('min_price') && $request->filled('max_price') && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        if ($minPrice !== null && $minPrice !== '') {
            $query->where('current_price', '>=', $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('current_price', '<=', $maxPrice);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');

        match ($sort) {
            'price_asc'   => $query->orderBy('current_price', 'asc'),
            'price_desc'  => $query->orderBy('current_price', 'desc'),
            'ending_soon' => $query->orderBy('end_time', 'asc'),
            default       => $query->orderBy('auctions.created_at', 'desc'),
        };

        return $query->with([
            'user',
            'category',
            'watchlists' => function ($q) {
                auth()->check()
                    ? $q->where('user_id', auth()->id())
                    : $q->whereRaw('1 = 0');
            },
        ]);
    }

    // Create new auction
    public function createAuction(array $data, $user)
    {
        $auction = new Auction();

        $auction->user_id        = $user->id;
        $auction->category_id    = $data['category_id'];
        $auction->title          = $data['title'];
        $auction->description    = $data['description'];
        $auction->starting_price = $data['starting_price'];
        $auction->current_price  = $data['starting_price'];
        $auction->status         = $user->hasAnyRole(['admin', 'super admin']) ? 'active' : 'pending';
        $auction->specifications = $data['specifications'] ?? null;
        $auction->min_increment  = $data['min_increment'] ?? 0.01;

        // Start time (snap to now if past)
        $startTime = Carbon::parse($data['start_time']);
        $auction->start_time = $startTime->isPast() ? now() : $startTime;

        // End time (must be after start time)
        $endTime = Carbon::parse($data['end_time']);
        $auction->end_time = $endTime->lessThanOrEqualTo($auction->start_time)
            ? $auction->start_time->copy()->addHour()
            : $endTime;

        // Document upload
        if (isset($data['document']) && $data['document'] instanceof UploadedFile) {
            $auction->document = $data['document']->store('auctions/documents', 'public');
        }

        $auction->save();

        // Multiple images
        if (isset($data['images']) && is_array($data['images'])) {

            $primaryIndex = $data['primary_image_index'] ?? 0;

            foreach ($data['images'] as $index => $imageFile) {
                if ($imageFile instanceof UploadedFile) {

                    $path = $imageFile->store('auctions', 'public');
                    $isPrimary = ($index == $primaryIndex);

                    $auction->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index,
                        'is_primary' => $isPrimary,
                    ]);

                    if ($isPrimary) {
                        $auction->image = $path;
                        $auction->save();
                    }
                }
            }
        }

        return $auction->load([
            'user',
            'category',
            'images',
        ]);
    }

    // Update status
    public function updateStatus(Auction $auction, string $status, ?string $reason = null)
    {
        $payload = ['status' => $status];

        if ($reason) {
            $payload['cancellation_reason'] = $reason;
        } elseif ($status === 'active') {
            $payload['cancellation_reason'] = null;
        }

        $result = $auction->update($payload);

        // Notifications
        if ($status === 'cancelled' && $reason && $auction->user) {
            $auction->user->notify(
                new AuctionCanceledNotification($auction, $reason)
            );
        }

        if ($status === 'active' && $auction->user) {
            $auction->user->notify(
                new AuctionApprovedNotification($auction)
            );
        }

        return $result;
    }

    // Soft delete auction
    public function deleteAuction($id)
    {
        $auction = Auction::findOrFail($id);
        return $auction->delete();
    }

    // Restore auction
    public function restoreAuction($id)
    {
        $auction = Auction::withTrashed()->findOrFail($id);
        return $auction->restore();
    }

    // Force delete auction
    public function forceDeleteAuction($id)
    {
        $auction = Auction::withTrashed()->findOrFail($id);
        return $auction->forceDelete();
    }

    // Get search statistics for metadata
    public function getSearchStatistics(Request $request)
    {
        // Reuse the existing filter logic instead of duplicating it
        $query = $this->getFilteredAuctions($request, false);

        // Get total count
        $totalResults = $query->count();

        // Get price range (need fresh query)
        $priceQuery = $this->getFilteredAuctions($request, false);
        
        // Remove existing select and only select aggregates
        $priceStats = $priceQuery
            ->select(\DB::raw('MIN(current_price) as min_price, MAX(current_price) as max_price'))
            ->first();

        return [
            'total_results' => $totalResults,
            'price_range' => [
                'min' => $priceStats->min_price ?? 0,
                'max' => $priceStats->max_price ?? 0,
            ],
        ];
    }

    // Get single auction by ID
    public function getAuctionById($id)
    {
        return Auction::with([
            'user',
            'category',
            'images',
            'bids.user'
        ])->findOrFail($id);
    }
}
