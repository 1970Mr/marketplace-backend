<?php

namespace App\Http\Resources\V1\Products;

use App\Http\Resources\V1\Products\SocialMedia\SecondInstagramAccountResource;
use App\Http\Resources\V1\Products\SocialMedia\SecondTiktokAccountResource;
use App\Http\Resources\V1\Products\SocialMedia\SecondYoutubeChannelResource;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\Products\InstagramAccount;
use App\Models\Products\TiktokAccount;
use App\Models\Products\YoutubeChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'summary' => $this->summary,
            'about_business' => $this->about_business,
            'price' => $this->price,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'industry' => $this->industry,
            'sub_industry' => $this->sub_industry,
            'allow_buyer_message' => $this->allow_buyer_message,
            'is_private' => $this->is_private,
            'is_verified' => $this->is_verified,
            'is_sold' => $this->is_sold,
            'is_completed' => $this->is_completed,
            'is_sponsored' => $this->is_sponsored,
            'escrow_type' => $this->escrow_type?->value,
            'escrow_type_label' => $this->escrow_type?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'created_at' => $this->created_at,
            'is_in_watchlist' => $this->when($this->relationLoaded('watchers'), fn() => $this->isInWatchList()),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];

        if ($this->relationLoaded('productable')) {
            $base['details'] = $this->getProductableResource();
        }

        return $base;
    }

    private function getProductableResource(): mixed
    {
        $productable = $this->productable;

        return match (true) {
            $productable instanceof YoutubeChannel => SecondYoutubeChannelResource::make($productable),
            $productable instanceof InstagramAccount => SecondInstagramAccountResource::make($productable),
            $productable instanceof TiktokAccount => SecondTiktokAccountResource::make($productable),
            default => null,
        };
    }

    private function isInWatchList(): bool
    {
        if (!Auth::guard('sanctum')->check()) {
            return false;
        }
        return $this->watchers->contains(Auth::guard('sanctum')->id());
    }
}
