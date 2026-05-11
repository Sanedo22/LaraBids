<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStrike extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'reported_by',
        'reason',
        'status',
        'type',
        'appeal_reason',
        'appeal_at',
        'admin_note',
    ];

    protected $casts = [
        'appeal_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAppealed($query)
    {
        return $query->where('status', 'appealed');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by')->withTrashed();
    }
}
