<?php

namespace App\Models;

use App\Enums\Offer\OfferType;
use Illuminate\Database\Eloquent\Model;

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
}
