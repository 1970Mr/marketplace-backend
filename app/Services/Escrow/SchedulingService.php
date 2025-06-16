<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use App\Models\TimeSlot;
use Illuminate\Validation\ValidationException;

class SchedulingService
{
    public function proposeSlots(Escrow $escrow, array $slotIds): Escrow
    {
        $this->validateAndAttachSlots($escrow, $slotIds);

        $escrow->adminEscrow->update(['stage' => EscrowStage::SCHEDULING_SUGGESTED]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    private function validateAndAttachSlots(Escrow $escrow, array $slotIds): void
    {
        $validSlots = TimeSlot::where('admin_id', $escrow->admin_id)
            ->available()
            ->whereIn('id', $slotIds)
            ->get();

        if ($validSlots->count() !== count($slotIds)) {
            throw ValidationException::withMessages([
                'invalid_slots' => __('Invalid or unavailable slots selected')
            ]);
        }

        $escrow->timeSlots()->sync($validSlots->pluck('id'));
    }

    public function selectSlot(Escrow $escrow, int $slotId): Escrow
    {
        if (!$escrow->timeSlots()->where('time_slots.id', $slotId)->exists()) {
            throw ValidationException::withMessages([
                'not_proposed' => 'Slot not proposed for this escrow'
            ]);
        }

        $escrow->timeSlots()->sync([$slotId]);
        $escrow->adminEscrow->update([
            'phase' => EscrowPhase::DELIVERY,
            'stage' => EscrowStage::DELIVERY_PENDING,
        ]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }

    public function rejectScheduling(Escrow $escrow): Escrow
    {
        $escrow->timeSlots()->detach();
        $escrow->adminEscrow->update(['stage' => EscrowStage::SCHEDULING_REJECTED]);

        return $escrow->load(['offer.product', 'buyer', 'seller', 'admin', 'timeSlots', 'adminEscrow']);
    }
}
