<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SignatureService
{
    /**
     * Upload buyer signature at any time after active
     */
    public function uploadBuyerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->storeAs(
            'signatures/buyers',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public'
        );
        $escrow->buyer_signature_path = $path;

        if ($escrow->seller_signature_path) {
            $escrow->phase = EscrowPhase::PAYMENT;
            $escrow->stage = EscrowStage::AWAITING_PAYMENT;
        } else {
            $escrow->stage = EscrowStage::AWAITING_SELLER_SIGNATURE;
        }

        $escrow->save();

        return $escrow;
    }

    /**
     * Upload seller signature at any time after active
     */
    public function uploadSellerSignature(Escrow $escrow, UploadedFile $file): Escrow
    {
        $path = $file->storeAs(
            'signatures/sellers',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public'
        );
        $escrow->seller_signature_path = $path;

        if ($escrow->buyer_signature_path) {
            $escrow->phase = EscrowPhase::PAYMENT;
            $escrow->stage = EscrowStage::AWAITING_PAYMENT;
        } else {
            $escrow->stage = EscrowStage::AWAITING_BUYER_SIGNATURE;
        }

        $escrow->save();

        return $escrow;
    }
}
