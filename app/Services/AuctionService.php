<?php

namespace App\Services;

use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AuctionService
{
    // Get filtered auctions
    public function getFilteredAuctions(Request $request, bool $activeOnly = true)
    {
        $query = Auction::query();

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        // Search filter
        if ($request->has('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('current_price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('current_price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('current_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('current_price', 'desc');
                break;
            case 'ending_soon':
                $query->orderBy('end_time', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        return $query->with(['user', 'category']);
    }

    // Create new auction
    public function createAuction(array $data, $user)
    {
        $auction = new Auction();
        $auction->user_id = $user->id;
        $auction->category_id = $data['category_id'];
        $auction->title = $data['title'];
        $auction->description = $data['description'];
        $auction->starting_price = $data['starting_price'];
        $auction->current_price = $data['starting_price'];
        $auction->status = 'pending';
        $auction->specifications = $data['specifications'] ?? null;

        // Snap start_time to now if it's in the past
        $startTime = Carbon::parse($data['start_time']);
        if ($startTime->isPast()) {
            $startTime = now();
        }
        $auction->start_time = $startTime;

        // Ensure end_time is still after our (potentially snapped) start_time
        $endTime = Carbon::parse($data['end_time']);
        if ($endTime->lessThanOrEqualTo($startTime)) {
            $auction->end_time = $startTime->copy()->addHour();
        } else {
            $auction->end_time = $endTime;
        }

        if (isset($data['document']) && $data['document'] instanceof \Illuminate\Http\UploadedFile) {
            $auction->document = $data['document']->store('auctions/documents', 'public');
        }

        $auction->save();

        // Handle multiple image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            $primaryIndex = $data['primary_image_index'] ?? 0;
            
            foreach ($data['images'] as $index => $imageFile) {
                if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                    $path = $imageFile->store('auctions', 'public');
                    $isPrimary = ($index == $primaryIndex);
                    
                    $auction->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index,
                        'is_primary' => $isPrimary,
                    ]);

                    // Set the primary image on the auction table itself
                    if ($isPrimary) {
                        $auction->image = $path;
                        $auction->save();
                    }
                }
            }
        }

        return $auction;
    }

    // Update status
    public function updateStatus(Auction $auction, string $status, ?string $reason = null)
    {
        $payload = ['status' => $status];
        
        if ($reason) {
            $payload['cancellation_reason'] = $reason;
        } elseif ($status === 'active') {
            // Clear cancellation reason when activating
            $payload['cancellation_reason'] = null;
        }
        
        return $auction->update($payload);
    }

    // Delete auction (Soft delete)
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
}
