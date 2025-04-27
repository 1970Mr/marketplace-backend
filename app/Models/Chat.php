<?php

namespace App\Models;

use App\Enums\Messenger\ChatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Chat extends Model
{
    protected $fillable = [
        'uuid',
        'type',
        'product_id',
        'buyer_id',
        'seller_id',
    ];

    protected $casts = [
        'type' => ChatType::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadMessages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->whereNull('read_at')
            ->whereNot('user_id', auth()->id());
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
