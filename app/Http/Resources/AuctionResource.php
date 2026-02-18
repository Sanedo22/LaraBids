<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $now = now();
        $endTime = $this->end_time;
        
        // Calculate time remaining in seconds
        $timeRemaining = $endTime->isFuture() ? $endTime->diffInSeconds($now) : 0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'starting_price' => $this->starting_price,
            'current_price' => $this->current_price,
            'min_increment' => $this->min_increment ?? 0.01,
            'specifications' => $this->specifications ?? [],
            'status' => $this->status,
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'time_remaining' => $timeRemaining,
            'time_remaining_human' => $endTime->isFuture() ? $endTime->diffForHumans($now, true) : 'Ended',

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],

            'images' => $this->images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
                'is_primary' => $image->is_primary ?? false,
                ];
            }),

            'bid_count' => $this->bids->count(),
            'bids' => $this->whenLoaded('bids', function () {
                return $this->bids->map(function ($bid) {
                    return [
                        'id' => $bid->id,
                        'amount' => $bid->amount,
                        'user' => [
                            'id' => $bid->user->id,
                            'name' => $bid->user->name
                        ]
                    ];
                });
            }),

            'is_watchlisted' => $this->whenLoaded('watchlists', function () {
                return $this->watchlists->isNotEmpty();
            }, false),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
