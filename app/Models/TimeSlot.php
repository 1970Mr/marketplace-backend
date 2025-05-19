<?php

namespace App\Models;

use App\Enums\Escrow\Weekday;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'weekday',
        'start_time'
    ];

    protected $casts = [
        'weekday' => Weekday::class,
        'start_time' => 'datetime:H:i',
    ];

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_time_slot');
    }

    public function escrows(): BelongsToMany
    {
        return $this->belongsToMany(Escrow::class, 'escrow_time_slot');
    }

    public function scopeForAdmin($q, int $adminId)
    {
        return $q->whereHas('admins', fn($q) => $q->where('admin_id', $adminId));
    }

    public function scopeReserved($q)
    {
        return $q->whereHas('escrows');
    }

    public function scopeReservedForAdmin($q, int $adminId)
    {
        return $q->forAdmin($adminId)->reserved();
    }

    public function scopeAvailableForAdmin($q, int $adminId)
    {
        return $q->forAdmin($adminId)->whereDoesntHave('escrows');
    }
}
