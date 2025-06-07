<?php

namespace App\Http\Resources\V1\Messenger;

use App\Enums\Messenger\MessageType;
use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Offers\OfferResource;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
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
        $senderResource = $this->sender instanceof User
            ? UserResource::make($this->whenLoaded('sender'))
            : AdminResource::make($this->whenLoaded('sender'));
        $senderType = $this->sender instanceof User ? 'user' : 'admin';

        return [
            'uuid' => $this->uuid,
            'content' => $this->content,
            'type' => MessageType::getLabelByValue($this->type->value),
            'sender' => $senderResource,
            'sender_type' => $senderType,
            'chat' => ChatResource::make($this->whenLoaded('chat')),
            'offer' => OfferResource::make($this->whenLoaded('offer')),
            'is_read' => (bool)$this->read_at,
            'sent_at' => $this->created_at->diffForHumans(),
        ];
    }
}
