<?php

namespace App\Models;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Escrow\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Escrow extends Model
{
    protected $fillable = [
        'uuid',
        'offer_id',
        'buyer_id',
        'seller_id',
        'admin_id',
        'phase',
        'stage',
        'buyer_signature_path',
        'seller_signature_path',
        'payment_receipts',
        'amount_received',
        'amount_released',
        'amount_refunded',
        'amount_received_method',
        'amount_released_method',
        'amount_refunded_method',
        'cancellation_note',
        'refund_reason',
        'status',
    ];

    protected $casts = [
        'phase' => EscrowPhase::class,
        'stage' => EscrowStage::class,
        'status' => EscrowStatus::class,
        'amount_received_method' => PaymentMethod::class,
        'amount_released_method' => PaymentMethod::class,
        'amount_refunded_method' => PaymentMethod::class,
        'payment_receipts' => 'array',
        'amount_received' => 'decimal:2',
        'amount_released' => 'decimal:2',
        'amount_refunded' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            $model->uuid = $model->uuid ?? (string)Str::uuid();
        });
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function timeSlots(): BelongsToMany
    {
        return $this->belongsToMany(
            TimeSlot::class,
            'escrow_time_slot',
            'escrow_id',
            'time_slot_id'
        )->withTimestamps();
    }

    public function scopeFilterByProductTitle(Builder $query, string|null $search): Builder
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('offer.product', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        });
    }

    public function scopeFilterBy(Builder $query, string $column, string|int|null $value): Builder
    {
        return $query->when($value, function ($query) use ($column, $value) {
            $query->where($column, $value);
        });
    }
}
