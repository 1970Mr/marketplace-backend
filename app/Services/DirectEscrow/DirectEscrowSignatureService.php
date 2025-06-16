<?php

namespace App\Services\DirectEscrow;

use App\Enums\Escrow\DirectEscrowPhase;
use App\Enums\Escrow\DirectEscrowStage;
use App\Models\Escrow;
use Illuminate\Http\UploadedFile;

class DirectEscrowSignatureService
{
    public function uploadBuyerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->store('signatures/buyers', 'public');
        $escrow->update(['buyer_signature_path' => $path]);

        $this->checkSignaturesCompletion($escrow);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    public function uploadSellerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->store('signatures/sellers', 'public');
        $escrow->update(['seller_signature_path' => $path]);

        $this->checkSignaturesCompletion($escrow);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'directEscrow']);
    }

    private function checkSignaturesCompletion(Escrow $escrow): void
    {
        if ($escrow->buyer_signature_path && $escrow->seller_signature_path) {
            $escrow->directEscrow->update([
                'phase' => DirectEscrowPhase::PAYMENT,
                'stage' => DirectEscrowStage::AWAITING_PAYMENT,
            ]);
        } elseif ($escrow->buyer_signature_path && !$escrow->seller_signature_path) {
            $escrow->directEscrow->update([
                'stage' => DirectEscrowStage::AWAITING_SELLER_SIGNATURE,
            ]);
        } elseif (!$escrow->buyer_signature_path && $escrow->seller_signature_path) {
            $escrow->directEscrow->update([
                'stage' => DirectEscrowStage::AWAITING_BUYER_SIGNATURE,
            ]);
        }
    }
}
