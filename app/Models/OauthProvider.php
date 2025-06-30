<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OauthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'provider' => 'string',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the oauth provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
