<?php

namespace App\Http\Resources\V1\Products\SocialMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecondYoutubeChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->url,
            'subscribers_count' => $this->subscribers_count,
            'monthly_revenue' => $this->monthly_revenue,
            'monthly_views' => $this->monthly_views,
            'monetization_method' => $this->monetization_method,
            'business_age' => $this->business_age,
            'business_location' => $this->business_locations[0],
            'featured_image' => $this->getFeaturedImage(),
            'analytics_screenshot' => $this->getAnalyticsScreenshot(),
        ];
    }

    private function getFeaturedImage(): ?string
    {
        return $this->listing_images && count($this->listing_images)
            ? asset('storage/' . $this->listing_images[0])
            : null;
    }

    private function getAnalyticsScreenshot(): ?string
    {
        return $this->analytics_screenshot
            ? asset('storage/' . $this->analytics_screenshot)
            : null;
    }
}
