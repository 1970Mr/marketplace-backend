<?php

namespace App\Http\Resources\V1\Products\SocialMedial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class YoutubeChannelResource extends JsonResource
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
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'url' => $this->url,
            'category' => $this->category,
            'sub_category' => $this->sub_category,
            'business_location' => $this->business_location,
            'age_of_channel' => $this->age_of_channel,
            'subscribers' => $this->subscribers,
            'monthly_revenue' => $this->monthly_revenue,
            'monthly_views' => $this->monthly_views,
            'monetization_method' => $this->monetization_method,
            'price' => $this->price,
            'summary' => $this->summary,
            'about_channel' => $this->about_channel,
            'allow_buyer_messages' => $this->allow_buyer_messages,
            'is_private' => $this->is_private,
            'is_verified' => $this->is_verified,
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
}
