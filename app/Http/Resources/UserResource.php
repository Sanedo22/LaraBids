<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'roles' => $this->roles->pluck('name'),
            'registration_source' => $this->created_by ? 'Admin-Created' : 'Self-Registered',
            'strikes' => $this->when($this->relationLoaded('strikes'), function() {
                return $this->strikes->map(function($strike) {
                    return [
                        'id' => $strike->id,
                        'reason' => $strike->reason,
                        'auction' => $strike->auction ? $strike->auction->title : 'N/A',
                        'reporter' => $strike->reporter ? $strike->reporter->name : 'System',
                        'created_at' => $strike->created_at->toIso8601String(),
                    ];
                });
            }),
            'statistics' => $this->when($request->routeIs('api.user.profile.show'), function () {
                return $this->getStatistics();
            }),
        ];
    }
}