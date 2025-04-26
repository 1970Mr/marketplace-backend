<?php

namespace App\Models;

use App\Enums\Message\MessageType;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'uuid',
        'content',
        'type',
        'chat_id',
        'user_id',
        'offer_id',
        'read_at',
    ];

    protected $casts = [
        'type' => MessageType::class,
    ];
}
