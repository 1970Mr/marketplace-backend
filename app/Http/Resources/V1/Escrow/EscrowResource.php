<?php

namespace App\Http\Resources\V1\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'phase' => $this->phase->value,
            'stage' => $this->stage->value,
            'status_label' => $this->status->label(),
            'phase_label' => $this->phase->label(),
            'stage_label' => $this->stage->label(),
            'buyer_signature_url' => $this->when(
                $this->buyer_signature_path,
                asset('storage/' . $this->buyer_signature_path)
            ),
            'seller_signature_url' => $this->when(
                $this->seller_signature_path,
                asset('storage/' . $this->seller_signature_path)
            ),
            'payment_receipts_urls' => $this->when(
                $this->payment_receipts,
                collect($this->payment_receipts)->map(static fn(string $path) => asset('storage/' . $path))->toArray()
            ),
            'amount_received' => $this->amount_received,
            'amount_released' => $this->amount_released,
            'amount_refunded' => $this->amount_refunded,
            'amount_received_method' => $this->amount_received_method?->value,
            'amount_released_method' => $this->amount_released_method?->value,
            'amount_refunded_method' => $this->amount_refunded_method?->value,
            'amount_received_method_label' => $this->amount_received_method?->label(),
            'amount_released_method_label' => $this->amount_released_method?->label(),
            'amount_refunded_method_label' => $this->amount_refunded_method?->label(),
            'cancellation_note' => $this->cancellation_note,
            'refund_reason' => $this->refund_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getSelectedSlot(): ?TimeSlotResource
    {
        return $this->whenLoaded('timeSlots', function (): ?TimeSlotResource {
            return $this->status->value >= EscrowPhase::DELIVERY->value ?
                TimeSlotResource::make($this->timeSlots->first()) :
                null;
        }, null);
    }
}
