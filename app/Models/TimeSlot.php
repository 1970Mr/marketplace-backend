<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'datetime',
        'admin_id'
    ];

    protected $casts = [
        'datetime' => 'datetime'
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function escrows(): BelongsToMany
    {
        return $this->belongsToMany(Escrow::class, 'escrow_time_slot');
    }

    public function scopeForAdmin(Builder $query, int $adminId): Builder
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeReserved($query)
    {
        return $query->whereHas('escrows');
    }

    public function scopeReservedForAdmin(Builder $query, int $adminId): Builder
    {
        return $query->forAdmin($adminId)->reserved();
    }

    public function scopeAvailableForAdmin(Builder $query, int $adminId): Builder
    {
        return $query->forAdmin($adminId)->whereDoesntHave('escrows');
    }

    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('datetime', '>', now());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereDoesntHave('escrows');
    }
}
