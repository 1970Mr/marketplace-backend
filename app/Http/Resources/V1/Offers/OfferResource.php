<?php

namespace App\Http\Resources\V1\Offers;

use App\Enums\Offers\OfferType;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Http\Resources\V1\Products\ProductResource;
use App\Http\Resources\V1\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'amount' => $this->amount,
            'status' => OfferType::getLabelByValue($this->status->value),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'chat' => ChatResource::make($this->whenLoaded('chat')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at
        ];
    }
}
