<?php

namespace App\Http\Resources\V1\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'is_active' => $this->is_active,
            'productable' => $this->whenLoaded('productable'),
        ];
    }
}
