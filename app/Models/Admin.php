<?php

namespace App\Models;

use App\Enums\Users\AdminStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'admin-api';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => AdminStatus::class,
        ];
    }

    public function escrows(): HasMany
    {
        return $this->hasMany(Escrow::class, 'admin_id');
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function reservedTimeSlots(): HasMany
    {
        return $this->timeSlots()->whereHas('escrows');
    }

    public function availableTimeSlots(): HasMany
    {
        return $this->timeSlots()->whereDoesntHave('escrows');
    }
}
