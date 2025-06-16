<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;

class DeliveryService
{
    /**
     * Admin confirms delivery and moves to payout
     */
    public function confirmDelivery(Escrow $escrow): Escrow
    {
        $escrow->adminEscrow->update([
            'phase' => EscrowPhase::PAYOUT,
            'stage' => EscrowStage::AWAITING_PAYOUT,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }
}
