<?php

namespace App\Jobs;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStatus;
use App\Models\Escrow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class ExpireEscrowJob implements ShouldQueue
{
    use Queueable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly protected Escrow $escrow)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $escrow = $this->escrow->fresh();

        if ($escrow && $escrow->phase?->value <= EscrowPhase::PAYMENT->value) {
            $escrow->update([
                'status' => EscrowStatus::EXPIRED,
            ]);
        }
    }
}
