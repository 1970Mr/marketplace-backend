<?php

namespace App\Http\Resources\V1\Products\SocialMedia;

use App\Models\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productData = $this->getProductData($this->whenLoaded('product'));

        return [
            ...$productData,

            // Instagram-specific
            'id' => $this->id,
            'url' => $this->url,
            'business_locations' => $this->business_locations,
            'business_age' => $this->business_age,
            'followers_count' => $this->followers_count,
            'posts_count' => $this->posts_count,
            'average_likes' => $this->average_likes,
            'analytics_screenshot_url' => $this->analytics_screenshot
                ? asset('storage/' . $this->analytics_screenshot)
                : null,
            'listing_images_urls' => $this->listing_images
                ? collect($this->listing_images)->map(fn($img) => asset('storage/' . $img))
                : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getProductData(?Product $product): array
    {
        if (!$product) {
            return [];
        }

        return [
            'uuid' => $product->uuid,
            'title' => $product->title,
            'summary' => $product->summary,
            'about_business' => $product->about_business,
            'price' => $product->price,
            'type' => $product->type,
            'sub_type' => $product->sub_type,
            'industry' => $product->industry,
            'sub_industry' => $product->sub_industry,
            'allow_buyer_message' => $product->allow_buyer_message,
            'is_private' => $product->is_private,
            'is_verified' => $product->is_verified,
            'is_sold' => $product->is_sold,
            'is_completed' => $product->is_completed,
            'is_sponsored' => $product->is_sponsored,
            'escrow_type' => $product->escrow_type?->value,
            'escrow_type_label' => $product->escrow_type?->label(),
            'status' => $product->status->label(),
        ];
    }
}
