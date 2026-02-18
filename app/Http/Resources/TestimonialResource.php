<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user_name ?? $this->user->name,
            'avatar' => $this->user_avatar ?? ($this->user ? $this->user->avatar_url : null),
            'rating' => $this->rating,
            'message' => $this->message,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
