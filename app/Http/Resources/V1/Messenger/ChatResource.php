<?php

namespace App\Http\Resources\V1\Messenger;

use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Escrow\EscrowResource;
use App\Http\Resources\V1\Products\ProductResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'buyer' => UserResource::make($this->whenLoaded('buyer')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'last_message' => MessageResource::make($this->whenLoaded('lastMessage')),
            'unread_count' => $this->whenCounted('unreadMessages'),
            'is_escrow' => $this->isEscrow(),
            'admin' => AdminResource::make($this->whenLoaded('admin')),
            'escrow' => EscrowResource::make($this->whenLoaded('escrow')),
            'created_at' => $this->created_at,
        ];
    }
}
