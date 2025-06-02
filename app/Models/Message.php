<?php

namespace App\Models;

use App\Enums\Messenger\MessageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $fillable = [
        'uuid',
        'content',
        'type',
        'chat_id',
        'sender_type',
        'sender_id',
        'offer_id',
        'read_at',
    ];

    protected $casts = [
        'type' => MessageType::class,
        'read_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
