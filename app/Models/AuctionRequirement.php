<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionRequirement extends Model
{
    protected $fillable = [
        'auction_id',
        'max_strikes_allowed',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
