<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'txnid',
        'amount',
        'commission_amount',
        'commission_percentage',
        'payout_amount',
        'payout_status',
        'payout_at',
        'status',
        'payu_id',
        'productinfo',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auction()
    {
        return $this->belongsTo(\App\Models\Auction::class);
    }
}
