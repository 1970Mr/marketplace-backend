<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorToken extends Model
{
    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'confirmed_at',
        'temp_token',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at);
    }

    public function resetTempToken(): void
    {
        $this->update(['temp_token' => null]);
    }
}
