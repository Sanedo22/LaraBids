<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'placed_at' => $this->created_at?->toIso8601String(),
            'placed_at_human' => $this->created_at?->diffForHumans(),
            'is_winning' => $this->isWinning(),
            'auction' => $this->whenLoaded('auction', function () {
                return [
                    'id' => $this->auction->id,
                    'title' => $this->auction->title,
                    'current_price' => $this->auction->current_price,
                    'status' => $this->auction->status,
                    'end_time' => $this->auction->end_time?->toIso8601String(),
                    'time_remaining' => $this->auction->end_time?->isFuture() 
                        ? $this->auction->end_time->diffInSeconds(now()) 
                        : 0,
                    'image_url' => $this->auction->images->first()?->image_url ?? null,
                    'category' => $this->whenLoaded('auction.category', function () {
                        return [
                            'id' => $this->auction->category->id,
                            'name' => $this->auction->category->name,
                        ];
                    }),
                ];
            }),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
        ];
    }
}
