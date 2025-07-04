<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use Illuminate\Http\UploadedFile;

class SignatureService
{
    public function uploadBuyerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->store('signatures/buyers', 'public');
        $escrow->update(['buyer_signature_path' => $path]);

        $this->checkSignaturesCompletion($escrow);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function uploadSellerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->store('signatures/sellers', 'public');
        $escrow->update(['seller_signature_path' => $path]);

        $this->checkSignaturesCompletion($escrow);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    private function checkSignaturesCompletion(Escrow $escrow): void
    {
        if ($escrow->buyer_signature_path && $escrow->seller_signature_path) {
            $escrow->adminEscrow->update([
                'phase' => EscrowPhase::PAYMENT,
                'stage' => EscrowStage::AWAITING_PAYMENT,
            ]);
        } elseif ($escrow->buyer_signature_path && !$escrow->seller_signature_path) {
            $escrow->adminEscrow->update([
                'stage' => EscrowStage::AWAITING_SELLER_SIGNATURE,
            ]);
        } elseif (!$escrow->buyer_signature_path && $escrow->seller_signature_path) {
            $escrow->adminEscrow->update([
                'stage' => EscrowStage::AWAITING_BUYER_SIGNATURE,
            ]);
        }
    }
}
