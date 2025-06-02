<?php

namespace App\Models;

use App\Enums\Messenger\ChatType;
use App\Models\Products\Product;
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
        'admin_id',
        'escrow_id',
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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadMessages(): HasMany
    {
        $senderId = auth()->id() ?? auth('admin-api')->id();
        return $this->hasMany(Message::class)
            ->whereNull('read_at')
            ->whereNot('sender_id', $senderId);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function scopeIsEscrow(): bool
    {
        return in_array($this->type, [ChatType::ESCROW_BUYER, ChatType::ESCROW_SELLER], true);
    }
}
