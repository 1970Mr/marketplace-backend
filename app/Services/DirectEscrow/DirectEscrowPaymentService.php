<?php

namespace App\Services\DirectEscrow;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Enums\Escrow\PaymentMethod;
use App\Models\Escrow;

class DirectEscrowPaymentService
{
    public function confirmPayment(Escrow $escrow, array $data): Escrow
    {
        $escrow->update([
            'amount_received' => $data['amount'],
            'amount_received_method' => PaymentMethod::from($data['method']),
        ]);

        $escrow->directEscrow->update([
            'phase' => DirectEscrowPhase::DELIVERY,
            'stage' => DirectEscrowStage::AWAITING_DELIVERY,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow', 'directChat']);
    }
}
