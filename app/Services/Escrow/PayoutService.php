<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Models\Escrow;

class PayoutService
{
    public function releaseFunds(Escrow $escrow, float $amount, int $method): Escrow
    {
        $escrow->amount_released = $amount;
        $escrow->amount_released_method = $method;
        $escrow->stage = EscrowStage::PAYOUT_COMPLETED;
        $escrow->phase = EscrowPhase::COMPLETED;
        $escrow->status = EscrowStatus::COMPLETED;
        $escrow->save();

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
    }

    public function refundEscrow(Escrow $escrow, float $amount, int $method, string $refundReason): Escrow
    {
        $escrow->amount_refunded = $amount;
        $escrow->amount_refunded_method = $method;
        $escrow->refund_reason = $refundReason;
        $escrow->status = EscrowStatus::REFUNDED;
        $escrow->save();

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin']);
    }
}
