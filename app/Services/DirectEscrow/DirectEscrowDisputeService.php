<?php

namespace App\Services\DirectEscrow;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Enums\Escrow\DirectEscrowStatus;
use App\Enums\Escrow\DisputeReason;
use App\Enums\Escrow\DisputeResolution;
use App\Enums\Escrow\PaymentMethod;
use App\Models\Escrow;

class DirectEscrowDisputeService
{
    public function openDispute(Escrow $escrow, int $reason, string $details): Escrow
    {
        $escrow->directEscrow->update([
            'status' => DirectEscrowStatus::DISPUTED,
            'phase' => DirectEscrowPhase::DISPUTE,
            'stage' => DirectEscrowStage::DISPUTE_UNDER_REVIEW,
            'dispute_reason' => DisputeReason::from($reason),
            'dispute_details' => $details,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function resolveDispute(Escrow $escrow, int $resolution, string $note, float $amount, int $method): Escrow
    {
        $disputeResolution = DisputeResolution::from($resolution);
        $paymentMethod = PaymentMethod::from($method);

        $escrow->directEscrow->update([
            'status' => DirectEscrowStatus::RESOLVED,
            'phase' => DirectEscrowPhase::COMPLETED,
            'stage' => DirectEscrowStage::DISPUTE_RESOLVED,
            'dispute_resolution' => $disputeResolution,
            'dispute_resolution_note' => $note,
        ]);

        $this->updateEscrowAmountsBasedOnResolution($escrow, $disputeResolution, $amount, $paymentMethod);

        $escrow->offer->product->update(['is_sold' => true]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function updateEscrowAmountsBasedOnResolution(Escrow $escrow, DisputeResolution $disputeResolution, float $amount, PaymentMethod $paymentMethod): void
    {
        if ($disputeResolution === DisputeResolution::SELLER_WINS) {
            // Seller wins - amount goes to seller
            $escrow->update([
                'amount_released' => $amount,
                'amount_released_method' => $paymentMethod,
            ]);
        } else {
            // Buyer wins - amount is refunded to buyer
            $escrow->update([
                'amount_refunded' => $amount,
                'amount_refunded_method' => $paymentMethod,
            ]);
        }
    }
}
