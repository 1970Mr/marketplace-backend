<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'uuid',
        'product_id',
        'buyer_id',
        'seller_id',
    ];
}
