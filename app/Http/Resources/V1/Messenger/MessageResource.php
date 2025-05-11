<?php

namespace App\Http\Resources\V1\Messenger;

use App\Enums\Messenger\MessageType;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'content' => $this->content,
            'type' => MessageType::getLabelByValue($this->type->value),
            'user' => UserResource::make($this->whenLoaded('user')),
            'chat' => OfferResource::make($this->whenLoaded('chat')),
            'offer' => OfferResource::make($this->whenLoaded('offer')),
            'is_read' => (bool)$this->read_at,
            'sent_at' => $this->created_at->diffForHumans(),
        ];
    }
}
