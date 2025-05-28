<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    protected const RECEIPTS_LIMIT = 3;

    public function uploadReceipts(Escrow $escrow, array $files): Escrow
    {
        $this->checkReceiptsLimit($escrow);

        $paths = collect($files)->map(
            fn(UploadedFile $f) => $f->storeAs('receipts', Str::uuid() . '.' . $f->getClientOriginalExtension(), 'public')
        )->toArray();

        $escrow->payment_receipts = array_merge($escrow->payment_receipts ?? [], $paths);
        $escrow->stage = EscrowStage::PAYMENT_UPLOADED;
        $escrow->save();

        return $escrow;
    }

    private function checkReceiptsLimit(Escrow $escrow): void
    {
        if (count($escrow->payment_receipts ?? []) >= self::RECEIPTS_LIMIT) {
            throw ValidationException::withMessages([
                'number_limit' => 'Payment receipts limit exceeded!'
            ]);
        }
    }

    public function confirmPayment(Escrow $escrow, float $amount, int $method): Escrow
    {
        $escrow->amount_received = $amount;
        $escrow->amount_received_method = $method;
        $escrow->phase = EscrowPhase::SCHEDULING;
        $escrow->stage = EscrowStage::AWAITING_SCHEDULING;
        $escrow->save();

        return $escrow;
    }
}
