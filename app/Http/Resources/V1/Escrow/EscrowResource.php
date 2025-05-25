<?php

namespace App\Http\Resources\V1\Escrow;

use App\Enums\Escrow\EscrowStage;
use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
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
            'offer' => OfferResource::make($this->whenLoaded('offer')),
            'buyer' => UserResource::make($this->whenLoaded('buyer')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'admin' => AdminResource::make($this->whenLoaded('admin')),
            'time_slots' => TimeSlotResource::collection($this->whenLoaded('timeSlots')),
            'selected_time_slot' => $this->getSelectedSlot(),
            'status' => $this->status->value,
            'phase' => $this->phase?->value,
            'stage' => $this->stage?->value,
            'status_label' => $this->status->label(),
            'phase_label' => $this->phase?->label(),
            'stage_label' => $this->stage?->label(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getSelectedSlot(): ?string
    {
        return $this->whenLoaded('timeSlots', function (): ?string {
            return $this->status === EscrowStage::DELIVERY_PENDING ?
                TimeSlotResource::make($this->timeSlots->first()) :
                null;
        }, null);
    }
}
