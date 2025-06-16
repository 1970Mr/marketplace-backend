<?php

namespace App\Models;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Enums\Escrow\DirectEscrowStatus;
use App\Enums\Escrow\DisputeReason;
use App\Enums\Escrow\DisputeResolution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectEscrow extends Model
{
    protected $fillable = [
        'escrow_id',
        'status',
        'phase',
        'stage',
        'dispute_reason',
        'dispute_details',
        'dispute_resolution',
        'dispute_resolution_note',
    ];

    protected $casts = [
        'status' => DirectEscrowStatus::class,
        'phase' => DirectEscrowPhase::class,
        'stage' => DirectEscrowStage::class,
        'dispute_reason' => DisputeReason::class,
        'dispute_resolution' => DisputeResolution::class,
    ];

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }
}
