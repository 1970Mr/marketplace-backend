<?php

namespace App\Models;

use App\Enums\Offers\OfferType;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Offer extends Model
{
    protected $fillable = [
        'uuid',
        'amount',
        'status',
        'product_id',
        'buyer_id',
        'seller_id',
        'chat_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => OfferType::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }
}
