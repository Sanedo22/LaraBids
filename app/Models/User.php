<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'avatar',
        'bio',
        'deleted_by',
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
}
