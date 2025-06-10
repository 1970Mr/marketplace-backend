<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SignatureService
{
    public function uploadBuyerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $escrow->buyer_signature_path = $this->storeSignature($file, 'signatures/buyers');

        $this->updateEscrowAfterSignature(
            $escrow,
            EscrowStage::AWAITING_SELLER_SIGNATURE,
            (bool)$escrow->seller_signature_path
        );

        $escrow->save();

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots']);
    }

    public function uploadSellerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $escrow->seller_signature_path = $this->storeSignature($file, 'signatures/sellers');

        $this->updateEscrowAfterSignature(
            $escrow,
            EscrowStage::AWAITING_BUYER_SIGNATURE,
            (bool)$escrow->buyer_signature_path
        );

        $escrow->save();

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots']);
    }

    private function storeSignature(UploadedFile $file, string $path): string
    {
        return $file->storeAs(
            $path,
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public'
        );
    }

    private function updateEscrowAfterSignature(Escrow $escrow, EscrowStage $stage, bool $counterpartHasSignature): void
    {
        if ($counterpartHasSignature) {
            $escrow->phase = EscrowPhase::PAYMENT;
            $escrow->stage = EscrowStage::AWAITING_PAYMENT;
        } else {
            $escrow->stage = $stage;
        }
    }
}
