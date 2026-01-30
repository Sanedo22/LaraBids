<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionImage extends Model
{
    protected $fillable = [
        'auction_id',
        'image_path',
        'sort_order',
        'is_primary',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
