<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Escrow;
use App\Models\TimeSlot;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SchedulingService
{
    public function proposeSlots(Escrow $escrow, array $slotIds): Collection
    {
        $slots = $this->validateAndAttachSlots($escrow, $slotIds);

        $escrow->stage = EscrowStage::SCHEDULING_SUGGESTED;
        $escrow->save();

        return $slots;
    }

    private function validateAndAttachSlots(Escrow $escrow, array $slotIds): Collection
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

        return $validSlots;
    }

    public function selectSlot(Escrow $escrow, int $slotId): TimeSlot
    {
        if (!$escrow->timeSlots()->where('time_slots.id', $slotId)->exists()) {
            throw new RuntimeException('Slot not proposed for this escrow');
        }

        $escrow->timeSlots()->sync([$slotId]);
        $escrow->phase = EscrowPhase::DELIVERY;
        $escrow->stage = EscrowStage::DELIVERY_PENDING;
        $escrow->save();

        return TimeSlot::findOrFail($slotId);
    }

    public function rejectScheduling(Escrow $escrow): Escrow
    {
        $escrow->timeSlots()->detach();
        $escrow->stage = EscrowStage::SCHEDULING_REJECTED;
        $escrow->save();

        return $escrow;
    }
}
