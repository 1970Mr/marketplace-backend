<?php

namespace App\Services\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Models\Admin;
use App\Models\Escrow;
use App\Models\TimeSlot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class SchedulingService
{
    /**
     * Seller proposes slots & reserves them for the escrow's admin
     */
    public function proposeSlots(Escrow $escrow, array $weekdays, array $times): Collection
    {
        $slots = $this->createSlots($weekdays, $times, $escrow->admin, $escrow);

        $escrow->stage = EscrowStage::SCHEDULING_SUGGESTED;
        $escrow->save();

        return $slots;
    }

    private function createSlots(array $weekdays, array $times, Admin $admin, Escrow $escrow): Collection
    {
        $slots = collect();

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
        $escrow->phase = EscrowPhase::DELIVERY;
        $escrow->stage = EscrowStage::DELIVERY_PENDING;
        $escrow->save();

        return TimeSlot::findOrFail($slotId);
    }

    /**
     * Seller rejects scheduling proposal: clear proposed slots and reset to AWAITING_SCHEDULING
     */
    public function rejectScheduling(Escrow $escrow): Escrow
    {
        $escrow->timeSlots()->detach();
        $escrow->stage = EscrowStage::SCHEDULING_REJECTED;
        $escrow->save();

        return $escrow;
    }
}
