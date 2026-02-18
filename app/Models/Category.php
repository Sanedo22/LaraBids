<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'is_active',
    ];

    // List active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Top-level categories
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // Sub-categories
    public function scopeSubLevel($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Get auctions
    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    // Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id')->withTrashed();
    }

    // Child categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Check if it's a sub-category
    public function isSubCategory(): bool
    {
        return !is_null($this->parent_id);
    }
}
