<?php

namespace App\Http\Resources\V1\Escrow;

use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnifiedEscrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDirectEscrow = $this->isDirectEscrow();
        $escrowDetails = $isDirectEscrow ? $this->directEscrow : $this->adminEscrow;

        return [
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'offer' => OfferResource::make($this->whenLoaded('offer')),
            'buyer' => UserResource::make($this->whenLoaded('buyer')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'admin' => AdminResource::make($this->whenLoaded('admin')),

            // Status fields - unified
            'status' => $escrowDetails?->status?->value,
            'phase' => $escrowDetails?->phase?->value,
            'stage' => $escrowDetails?->stage?->value,
            'status_label' => $escrowDetails?->status?->label(),
            'phase_label' => $escrowDetails?->phase?->label(),
            'stage_label' => $escrowDetails?->stage?->label(),

            // Payment info
            'amount_received' => $this->amount_received,
            'amount_released' => $this->amount_released,
            'amount_refunded' => $this->amount_refunded,
            'amount_received_method' => $this->amount_received_method?->value,
            'amount_released_method' => $this->amount_released_method?->value,
            'amount_refunded_method' => $this->amount_refunded_method?->value,
            'amount_received_method_label' => $this->amount_received_method?->label(),
            'amount_released_method_label' => $this->amount_released_method?->label(),
            'amount_refunded_method_label' => $this->amount_refunded_method?->label(),

            // Direct escrow specific fields
            'dispute_reason' => $this->when($isDirectEscrow, $escrowDetails?->dispute_reason?->value),
            'dispute_reason_label' => $this->when($isDirectEscrow, $escrowDetails?->dispute_reason?->label()),
            'dispute_details' => $this->when($isDirectEscrow, $escrowDetails?->dispute_details),
            'dispute_resolution' => $this->when($isDirectEscrow, $escrowDetails?->dispute_resolution?->value),
            'dispute_resolution_label' => $this->when($isDirectEscrow, $escrowDetails?->dispute_resolution?->label()),
            'dispute_resolution_note' => $this->when($isDirectEscrow, $escrowDetails?->dispute_resolution_note),

            // Common fields
            'cancellation_note' => $this->cancellation_note,
            'refund_reason' => $this->refund_reason,
            'has_unread_messages' => $this->hasUnreadMessages(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function hasUnreadMessages(): bool
    {
        $user = request()->user() ?? request()->user('admin-api');
        $senderId = $user?->id;

        if ($this->isDirectEscrow()) {
            if (!$this->relationLoaded('directChat')) {
                return false;
            }

            return $this->directChat &&
                $this->directChat->relationLoaded('messages') &&
                $this->directChat->messages->where('read_at', null)->where('sender_id', '!=', $senderId)->count() > 0;
        } else {
            if (!$this->relationLoaded('buyerChat') || !$this->relationLoaded('sellerChat')) {
                return false;
            }

            $buyerUnread = $this->buyerChat &&
                $this->buyerChat->relationLoaded('messages') &&
                $this->buyerChat->messages->where('read_at', null)->where('sender_id', '!=', $senderId)->count() > 0;

            $sellerUnread = $this->sellerChat &&
                $this->sellerChat->relationLoaded('messages') &&
                $this->sellerChat->messages->where('read_at', null)->where('sender_id', '!=', $senderId)->count() > 0;

            return $buyerUnread || $sellerUnread;
        }
    }
}
