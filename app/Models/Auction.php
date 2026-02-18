<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    use HasFactory, SoftDeletes;

    const MAX_INCREMENT_ALLOWED = 1000.00;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'starting_price',
        'current_price',
        'image',
        'document',
        'start_time',
        'end_time',
        'status',
        'specifications',
        'cancellation_reason',
        'min_increment',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'min_increment' => 'decimal:2',
        'specifications' => 'array',
    ];

    /**
     * Scope for active auctions (Approved and not ended)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('end_time', '>', now());
    }

    /**
     * Scope for live auctions (Currently open for bidding)
     */
    public function scopeLive($query)
    {
        return $query->active()
                     ->where('start_time', '<=', now());
    }

    /**
     * Scope for past auctions (Ended)
     */
    public function scopePast($query)
    {
        return $query->where('status', 'active')
                     ->where('end_time', '<=', now());
    }

    /**
     * Scope for latest auctions
     */
    public function scopeLatestFirst($query)
    {
        return $query->latest();
    }

    // Badge class for status
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'success',
            'pending' => 'info',
            'closed' => 'secondary',
            'cancelled' => 'danger',
            default => 'warning'
        };
    }

    // Get owner
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    // Get images
    public function images()
    {
        return $this->hasMany(AuctionImage::class)->orderBy('sort_order');
    }

    // Get bids
    public function bids()
    {
        return $this->hasMany(Bid::class)->latest();
    }

    // Get highest bid
    public function highestBid()
    {
        return $this->bids()->first();
    }

    // Get watchlists count or check if user watchlisted it
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }
}
