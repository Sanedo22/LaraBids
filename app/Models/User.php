<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'phone',
        'location',
        'avatar',
        'bio',
        'deleted_by',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    // Check super admin
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super admin');
    }

    // Check admin
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // Check user
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    // Get bids
    public function bids()
    {
        return $this->hasMany(Bid::class)->latest();
    }

    // Get auctions created by the user
    public function auctions()
    {
        return $this->hasMany(Auction::class)->latest();
    }

    // Get user's watchlist
    public function watchlist()
    {
        return $this->hasMany(Watchlist::class);
    }

    // Get avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4e73df&color=ffffff&size=150';
    }

    public function getStatistics(){
        return [
            'auctions_created' => $this->auctions()->count(),
            'active_auctions' => $this->auctions()->where('status', 'active')->count(),
            'total_bids' => $this->bids()->count(),
            'items_won' => $this->getWonAuctionsCount(),
            'watchlist_count' => $this->watchlist()->count(),
            'member_since' => $this->created_at?->toIso8601String(),
        ];
    }

    public function getWonAuctionsCount()
    {
        return Auction::where('winner_id', $this->id)->count();
    }

    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    public function auctionRegistrations()
    {
        return $this->hasMany(AuctionRegistration::class);
    }

    public function registeredAuctions()
    {
        return $this->belongsToMany(Auction::class, 'auction_registrations', 'user_id', 'auction_id');
    }

    public function isRegisteredFor(Auction $auction): bool
    {
        return $this->registeredAuctions()->where('auction_id', $auction->id)->exists();
    }

    public function isKycApproved(): bool
    {
        // Admins and Super Admins are exempt from KYC verification
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        return $this->kyc && $this->kyc->status === 'approved';
    }
    public function strikes()
    {
        return $this->hasMany(UserStrike::class, 'user_id');
    }

    public function getUnpaidStrikesCountAttribute(): int
    {
        return $this->strikes()->count();
    }
}
