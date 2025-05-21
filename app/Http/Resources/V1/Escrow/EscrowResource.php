<?php

namespace App\Http\Resources\V1\Escrow;

use App\Enums\Escrow\EscrowStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function Symfony\Component\Translation\t;

class EscrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'offer_id' => $this->offer_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'admin_id' => $this->admin_id,
            'admin' => $this->whenLoaded('admin'),
            'time_slots' => TimeSlotResource::collection($this->whenLoaded('timeSlots')),
            'selected_time_slot' => $this->getSelectedSlot(),
            'status' => $this->status->value,
            'phase' => $this->phase?->value,
            'stage' => $this->stage?->value,
            'status_label' => $this->status->label(),
            'phase_label' => $this->phase?->label(),
            'stage_label' => $this->stage?->label(),
        ];
    }

    private function getSelectedSlot(): ?string
    {
        return $this->whenLoaded('timeSlots', function (): ?string {
            return $this->status === EscrowStage::DELIVERY_PENDING ?
                TimeSlotResource::make($this->timeSlots->first()) :
                null;
        });
    }
}
