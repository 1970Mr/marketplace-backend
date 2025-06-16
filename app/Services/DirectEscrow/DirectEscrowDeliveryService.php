<?php

namespace App\Services\DirectEscrow;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Models\Escrow;

class DirectEscrowDeliveryService
{
    public function confirmDelivery(Escrow $escrow): Escrow
    {
        $escrow->directEscrow->update([
            'phase' => DirectEscrowPhase::CONFIRMATION,
            'stage' => DirectEscrowStage::AWAITING_BUYER_CONFIRMATION,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function acceptDelivery(Escrow $escrow): Escrow
    {
        $escrow->directEscrow->update([
            'phase' => DirectEscrowPhase::PAYOUT,
            'stage' => DirectEscrowStage::AWAITING_PAYOUT,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }
}
