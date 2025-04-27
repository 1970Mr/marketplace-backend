<?php

namespace App\Models;

use App\Enums\Offers\OfferType;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Offer extends Model
{
    protected $fillable = [
        'uuid',
        'amount',
        'status',
        'product_id',
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
