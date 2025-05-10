<?php

namespace App\Models\Products;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'summary',
        'about_business',
        'price',
        'type',
        'sub_type',
        'industry',
        'sub_industry',
        'allow_buyer_message',
        'is_private',
        'is_verified',
        'is_sold',
        'is_completed',
        'is_sponsored',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'allow_buyer_message' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'is_sold' => 'boolean',
        'is_completed' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_completed', true)
            ->where('is_sold', false)
            ->where('is_private', false);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_completed', false);
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_user_watchlist')
            ->withTimestamps();
    }
}
