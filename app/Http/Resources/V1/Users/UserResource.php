<?php

namespace App\Http\Resources\V1\Users;

use App\Http\Resources\V1\Escrow\UnifiedEscrowResource;
use App\Http\Resources\V1\Products\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'avatar' => $this->getAvatarUrl(),
            'company_name' => $this->company_name,
            'country_name' => $this->country_name,
            'note' => $this->note,
            'last_activity_at' => $this->last_activity_at?->diffForHumans(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'products_count' => $this->whenCounted('products'),
            'escrows_count' => $this->getEscrowsCount(),
            'products' => ProductResource::make($this->whenLoaded('products')),
            'escrows' => UnifiedEscrowResource::collection($this->getAllEscrows()),
            'type' => 'user',
            'created_at' => $this->created_at,
        ];
    }

    private function getAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    private function getEscrowsCount(): ?int
    {
        if (!$this->escrows_as_buyer_count && !$this->escrows_as_seller_count) {
            return null;
        }
        return ($this->escrows_as_buyer_count ?? 0) + ($this->escrows_as_seller_count ?? 0);
    }

    private function getAllEscrows(): Collection
    {
        $allEscrows = collect();

        if ($this->relationLoaded('escrowsAsBuyer')) {
            $allEscrows = $allEscrows->merge($this->escrowsAsBuyer);
        }

        if ($this->relationLoaded('escrowsAsSeller')) {
            $allEscrows = $allEscrows->merge($this->escrowsAsSeller);
        }

        return $allEscrows;
    }
}
