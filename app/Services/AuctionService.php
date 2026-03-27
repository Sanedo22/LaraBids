<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Auction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

//Notifications
use App\Notifications\AuctionCanceledNotification;
use App\Notifications\AuctionApprovedNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class AuctionService
{
    // Get filtered auctions
    public function getFilteredAuctions(Request $request, bool $activeOnly = true)
    {
        $query = Auction::select('auctions.*');

        $status = $request->input('status', $activeOnly ? 'active' : 'all');

        if ($status === 'active' || $status === 'live') {
            $query->live();
        } elseif ($status === 'upcoming') {
            $query->where('status', 'active')
                  ->where('start_time', '>', now());
        } elseif ($status === 'past' || $status === 'closed') {
            $query->where(function ($q) {
                $q->where('status', 'closed')
                    ->orWhere(function ($sq) {
                        $sq->where('status', 'active')
                            ->where('end_time', '<=', now());
                    });
            });
        } elseif ($status === 'resubmitted') {
            $query->where('status', 'pending')->where('is_resubmitted', true);
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

        // Category filter (Includes children products)
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();

            if ($category) {
                // Get this category and all its nested children IDs using recursive helper
                $categoryIds = $category->getAllChildIds();
                $query->whereIn('category_id', $categoryIds);
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

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('auctions.created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('auctions.created_at', '<=', $request->input('end_date'));
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

    /**
     * Update existing auction
     */
    public function updateAuction(Auction $auction, array $data)
    {
        $hasBids = $auction->bids()->exists();
        
        // Track if any meaningful changes occurred
        $hasChanges = false;

        // Check basic fields against existing values
        if (isset($data['title']) && $data['title'] !== $auction->title) $hasChanges = true;
        if (isset($data['description']) && $data['description'] !== $auction->description) $hasChanges = true;
        if (isset($data['min_increment']) && (float)$data['min_increment'] !== (float)$auction->min_increment) $hasChanges = true;
        
        // Compare specifications array
        if (isset($data['specifications']) && json_encode($data['specifications']) !== json_encode($auction->specifications)) {
            $hasChanges = true;
        }

        // Check lockable fields
        if (!$hasBids) {
            if (isset($data['starting_price']) && (float)$data['starting_price'] !== (float)$auction->starting_price) $hasChanges = true;
            if (isset($data['category_id']) && (int)$data['category_id'] !== (int)$auction->category_id) $hasChanges = true;
        }

        // Check times
        if (isset($data['start_time'])) {
            $newStart = Carbon::parse($data['start_time']);
            if (!$auction->start_time->eq($newStart) && !($newStart->isPast() && $auction->start_time->isPast())) {
                $hasChanges = true;
            }
        }
        if (isset($data['end_time'])) {
            $newEnd = Carbon::parse($data['end_time']);
            if (!$auction->end_time->eq($newEnd)) {
                $hasChanges = true;
            }
        }

        // Check media
        if (!empty($data['deleted_images']) || !empty($data['images']) || isset($data['document'])) {
            $hasChanges = true;
        }

        // Check primary image change
        if (isset($data['primary_image_index'])) {
            $currentPrimary = $auction->images()->where('is_primary', true)->first();
            // If there's a primary array index change but no new images, it means reordering existing ones
            if (empty($data['images']) && $currentPrimary && $currentPrimary->sort_order != $data['primary_image_index']) {
                 $hasChanges = true;
            }
        }

        // Re-approval logic: Reset status to pending IF edited by a non-admin AND actual changes happened
        if ($hasChanges && !auth()->user()->hasAnyRole(['admin', 'super admin'])) {
            // If it was already active, mark as resubmitted
            if ($auction->status === 'active') {
                $auction->is_resubmitted = true;
            }
            $auction->status = 'pending';
        }

        // 1. Update basic fields
        if (isset($data['title'])) $auction->title = $data['title'];
        if (isset($data['description'])) $auction->description = $data['description'];
        
        // Only allow price/category change if no bids exist
        if (!$hasBids) {
            if (isset($data['starting_price'])) {
                $auction->starting_price = $data['starting_price'];
                $auction->current_price  = $data['starting_price'];
            }
            if (isset($data['category_id'])) $auction->category_id = $data['category_id'];
        }

        if (isset($data['specifications'])) $auction->specifications = $data['specifications'];
        if (isset($data['min_increment'])) $auction->min_increment = $data['min_increment'];

        // Start time
        if (isset($data['start_time'])) {
            $startTime = Carbon::parse($data['start_time']);
            $auction->start_time = ($startTime->isPast() && !$auction->start_time->isPast()) ? now() : $startTime;
        }

        // End time
        if (isset($data['end_time'])) {
            $endTime = Carbon::parse($data['end_time']);
            $auction->end_time = $endTime->lessThanOrEqualTo($auction->start_time)
                ? $auction->start_time->copy()->addHour()
                : $endTime;
        }

        // 2. Handle Image Deletions
        if (isset($data['deleted_images']) && is_array($data['deleted_images'])) {
            foreach ($data['deleted_images'] as $imageId) {
                $image = $auction->images()->find($imageId);
                if ($image) {
                    // Delete file from storage
                    Storage::disk('public')->delete($image->image_path);
                    
                    // If this was the primary image, we need to clear it from the auction
                    if ($image->is_primary) {
                        $auction->image = null;
                        $auction->save();
                    }
                    
                    $image->delete();
                }
            }
        }

        // 3. Document upload
        if (isset($data['document']) && $data['document'] instanceof UploadedFile) {
            // Delete old document if exists
            if ($auction->document) {
                Storage::disk('public')->delete($auction->document);
            }
            $auction->document = $data['document']->store('auctions/documents', 'public');
        }

        $auction->save();

        // 4. New Images (Enforce 5-image limit)
        if (isset($data['images']) && is_array($data['images'])) {
            $existingCount = $auction->images()->count();
            $maxAllowed = 5 - $existingCount;
            
            if ($maxAllowed > 0) {
                $primaryIndex = $data['primary_image_index'] ?? 0;
                $newImages = array_slice($data['images'], 0, $maxAllowed);

                foreach ($newImages as $index => $imageFile) {
                    if ($imageFile instanceof UploadedFile) {
                        $path = $imageFile->store('auctions', 'public');
                        $isPrimary = ($index == $primaryIndex);

                        $auction->images()->create([
                            'image_path' => $path,
                            'sort_order' => $index + ($auction->images()->max('sort_order') ?? 0) + 1,
                            'is_primary' => $isPrimary,
                        ]);

                        if ($isPrimary || !$auction->image) {
                            $auction->image = $path;
                            $auction->save();
                        }
                    }
                }
            }
        }

        // 5. Ensure we have a primary image if some were deleted
        if (!$auction->image && $auction->images()->exists()) {
            $firstImage = $auction->images()->orderBy('sort_order')->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
                $auction->update(['image' => $firstImage?->image_path]);
            }
        }

        return $auction->load(['user', 'category', 'images']);
    }

    // Update status
    public function updateStatus(Auction $auction, string $status, ?string $reason = null)
    {
        $payload = ['status' => $status];

        // Reset resubmitted flag if approving
        if ($status === 'active') {
            $payload['is_resubmitted'] = false;
        }

        if ($reason) {
            $payload['cancellation_reason'] = $reason;
        } elseif ($status === 'active') {
            $payload['cancellation_reason'] = null;
        }

        $result = $auction->update($payload);

        // Notifications
        if ($status === 'cancelled' && $reason) {
            
            // Notify auction owner (seller)
            if ($auction->user) {
                $auction->user->notify(
                    new \App\Notifications\AuctionCanceledNotification($auction, $reason)
                );
            }

            // Notify all bidders
            $bidderIds = $auction->bids()->pluck('user_id')->where('user_id', '!=', $auction->user_id)->unique();
            $bidders = \App\Models\User::whereIn('id', $bidderIds)->get();
            
            foreach ($bidders as $bidder) {
                $bidder->notify(
                    new \App\Notifications\BidderAuctionCanceledNotification($auction, $reason)
                );
            }
        }

        if ($status === 'active' && $auction->user) {
            $auction->user->notify(
                new \App\Notifications\AuctionApprovedNotification($auction)
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
            ->select(DB::raw('MIN(current_price) as min_price, MAX(current_price) as max_price'))
            ->first();

        return [
            'total_results' => $totalResults,
            'price_range' => [
                'min' => $priceStats?->min_price ?? 0,
                'max' => $priceStats?->max_price ?? 0,
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
