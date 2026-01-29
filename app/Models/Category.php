<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_active',
    ];

    // List auctions
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get auctions
    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }
}
