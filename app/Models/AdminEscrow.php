<?php

namespace App\Models;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminEscrow extends Model
{
    protected $fillable = [
        'escrow_id',
        'status',
        'phase',
        'stage',
    ];

    protected $casts = [
        'status' => EscrowStatus::class,
        'phase' => EscrowPhase::class,
        'stage' => EscrowStage::class,
    ];

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }
}
