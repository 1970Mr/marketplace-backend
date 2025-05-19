<?php

namespace App\Services\Escrows;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Escrow\PaymentMethod;
use App\Models\Escrow;
use App\Models\TimeSlot;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class EscrowService
{
    /**
     * Create new escrow in PENDING status (before admin assignment)
     */
    public function createEscrow(array $data): Escrow
    {
        return Escrow::create([
            'offer_id' => $data['offer_id'],
            'buyer_id' => $data['buyer_id'],
            'seller_id' => $data['seller_id'],
            'status' => EscrowStatus::PENDING,
        ]);
    }

    /**
     * Admin accepts responsibility: change status from PENDING to ACTIVE
     */
    public function acceptEscrow(Escrow $escrow, int $adminId): Escrow
    {
        $escrow->admin_id = $adminId;
        $escrow->status = EscrowStatus::ACTIVE;
        $escrow->current_phase = EscrowPhase::SIGNATURE;
        $escrow->current_stage = EscrowStage::AWAITING_SIGNATURE;
        $escrow->save();

        return $escrow;
    }

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
            $escrow->current_phase = EscrowPhase::PAYMENT;
            $escrow->current_stage = EscrowStage::AWAITING_PAYMENT;
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
            $escrow->current_phase = EscrowPhase::PAYMENT;
            $escrow->current_stage = EscrowStage::AWAITING_PAYMENT;
        }

        $escrow->save();

        return $escrow;
    }

    /**
     * Upload payment receipts (Phase PAYMENT, stage PAYMENT_UPLOADED)
     */
    public function uploadReceipts(Escrow $escrow, array $files): Escrow
    {
        $paths = collect($files)->map(
            fn(UploadedFile $f) => $f->storeAs('receipts', Str::uuid() . '.' . $f->getClientOriginalExtension(), 'public')
        )->toArray();

        $escrow->payment_receipts = array_merge($escrow->payment_receipts ?? [], $paths);
        $escrow->current_stage = EscrowStage::PAYMENT_UPLOADED;
        $escrow->save();

        return $escrow;
    }

    /**
     * Admin confirms payment, stores amount and method and advances scheduling
     */
    public function confirmPayment(Escrow $escrow, float $amount, PaymentMethod $method): Escrow
    {
        $escrow->amount_received = $amount;
        $escrow->amount_received_method = $method;
        $escrow->current_phase = EscrowPhase::SCHEDULING;
        $escrow->current_stage = EscrowStage::AWAITING_SCHEDULING;
        $escrow->save();

        return $escrow;
    }

    /**
     * Seller proposes slots & reserves them for the escrow's admin
     */
    public function proposeSlots(Escrow $escrow, array $weekdays, array $times): Collection
    {
        $slots = collect();
        $admin = $escrow->admin;

        foreach ($weekdays as $day) {
            foreach ($times as $time) {
                $slot = TimeSlot::firstOrCreate([
                    'weekday' => $day->value,
                    'start_time' => Carbon::parse($time)->toTimeString(),
                ]);

                $admin->timeSlots()->syncWithoutDetaching([$slot->id]);
                $escrow->timeSlots()->syncWithoutDetaching([$slot->id]);
                $slots->push($slot);
            }
        }

        $escrow->current_stage = EscrowStage::SCHEDULING_SUGGESTED;
        $escrow->save();

        return $slots;
    }

    /**
     * Buyer selects one of proposed slots
     */
    public function selectSlot(Escrow $escrow, int $slotId): TimeSlot
    {
        if (!$escrow->timeSlots()->where('time_slot_id', $slotId)->exists()) {
            throw new RuntimeException('Slot not proposed for this escrow');
        }

        $escrow->timeSlots()->sync([$slotId]);
        $escrow->current_phase = EscrowPhase::DELIVERY;
        $escrow->current_stage = EscrowStage::DELIVERY_PENDING;
        $escrow->save();

        return TimeSlot::findOrFail($slotId);
    }

    /**
     * Seller rejects scheduling proposal: clear proposed slots and reset to AWAITING_SCHEDULING
     */
    public function rejectScheduling(Escrow $escrow): Escrow
    {
        // detach all proposed slots
        $escrow->timeSlots()->detach();

        // update stage
        $escrow->current_stage = EscrowStage::SCHEDULING_REJECTED;
        $escrow->save();

        return $escrow;
    }

    /**
     * Admin confirms delivery and moves to payout
     */
    public function confirmDelivery(Escrow $escrow): Escrow
    {
        $escrow->current_phase = EscrowPhase::PAYOUT;
        $escrow->current_stage = EscrowStage::AWAITING_PAYOUT;
        $escrow->save();

        return $escrow;
    }

    /**
     * Admin releases funds (finalizes escrow)
     */
    public function releaseFunds(Escrow $escrow, float $amount, PaymentMethod $method): Escrow
    {
        $escrow->amount_released = $amount;
        $escrow->amount_released_method = $method;
        $escrow->current_stage = EscrowStage::PAYOUT_COMPLETED;
        $escrow->status = EscrowStatus::COMPLETED;
        $escrow->save();

        return $escrow;
    }

    public function cancel(Escrow $escrow): Escrow
    {
        $escrow->status = EscrowStatus::CANCELLED;
        $escrow->save();

        return $escrow;
    }

    public function refund(Escrow $escrow): Escrow
    {
        $escrow->status = EscrowStatus::REFUNDED;
        $escrow->save();

        return $escrow;
    }
}
