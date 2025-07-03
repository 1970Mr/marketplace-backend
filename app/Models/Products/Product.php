<?php

namespace App\Models\Products;

use App\Enums\Escrow\EscrowType;
use App\Enums\Products\ProductStatus;
use App\Models\Escrow;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use SoftDeletes;

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
        'escrow_type',
        'status',
        'user_id',
        'productable_type',
        'productable_id',
    ];

    protected $casts = [
        'allow_buyer_message' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'is_sold' => 'boolean',
        'is_completed' => 'boolean',
        'is_sponsored' => 'boolean',
        'escrow_type' => EscrowType::class,
        'status' => ProductStatus::class,
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

    public function offer(): HasOne
    {
        return $this->hasOne(Offer::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::APPROVED->value)
            ->where('is_completed', true)
            ->where('is_verified', true)
            ->where('is_sold', false)
            ->where('is_private', false);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }

    public function canDelete(): void
    {
        if ($this->is_sold || $this->offer) {
            throw ValidationException::withMessages([
                'product' => 'Can\'t delete this product'
            ]);
        }
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_user_watchlist')
            ->withTimestamps();
    }

    public function escrow(): ?Escrow
    {
        return $this->offer?->escrow;
    }

    public function hasEscrow(): bool
    {
        return $this->escrow() !== null;
    }
}
