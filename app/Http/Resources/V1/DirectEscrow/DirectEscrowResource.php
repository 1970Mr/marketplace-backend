<?php

namespace App\Http\Resources\V1\DirectEscrow;

use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectEscrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $directEscrow = $this->directEscrow;

        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'offer' => OfferResource::make($this->whenLoaded('offer')),
            'buyer' => UserResource::make($this->whenLoaded('buyer')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'admin' => AdminResource::make($this->whenLoaded('admin')),
            'direct_chat' => ChatResource::make($this->whenLoaded('directChat')),

            // Direct Escrow specific fields
            'status' => $directEscrow?->status?->value,
            'phase' => $directEscrow?->phase?->value,
            'stage' => $directEscrow?->stage?->value,
            'status_label' => $directEscrow?->status?->label(),
            'phase_label' => $directEscrow?->phase?->label(),
            'stage_label' => $directEscrow?->stage?->label(),

            // Signatures
            'buyer_signature_url' => $this->when(
                $this->buyer_signature_path,
                asset('storage/' . $this->buyer_signature_path)
            ),
            'seller_signature_url' => $this->when(
                $this->seller_signature_path,
                asset('storage/' . $this->seller_signature_path)
            ),

            // Payment info
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

            // Dispute info
            'dispute_reason' => $directEscrow?->dispute_reason?->value,
            'dispute_reason_label' => $directEscrow?->dispute_reason?->label(),
            'dispute_details' => $directEscrow?->dispute_details,
            'dispute_resolution' => $directEscrow?->dispute_resolution?->value,
            'dispute_resolution_label' => $directEscrow?->dispute_resolution?->label(),
            'dispute_resolution_note' => $directEscrow?->dispute_resolution_note,

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
        if (!$this->relationLoaded('directChat')) {
            return false;
        }

        $user = auth()->user() ?? auth('admin-api')->user();
        $senderId = $user?->id;

        return $this->directChat &&
            $this->directChat->relationLoaded('messages') &&
            $this->directChat->messages->where('read_at', null)->where('sender_id', '!=', $senderId)->count() > 0;
    }
}
