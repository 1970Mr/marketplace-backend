<?php

namespace App\Services\Products\SocialMedia;

use App\Models\Products\Product;
use App\Models\Products\YoutubeChannel;
use App\Services\Products\SocialMedia\Abstracts\BaseSocialMediaService;

class YoutubeChannelService extends BaseSocialMediaService
{
    protected array $serviceSpecificFields = [
        'url', 'business_locations', 'business_age',
        'subscribers_count', 'monthly_revenue', 'monthly_views',
        'monetization_method', 'analytics_screenshot', 'listing_images'
    ];

    protected string $fileStoragePath = 'products/social_media/youtube';

    protected function updateOrCreateMedia(Product $product, array $mediaData): YoutubeChannel
    {
        $media = $product->productable;

        if ($media instanceof YoutubeChannel) {
            $media->update($mediaData);
        } else {
            $media = YoutubeChannel::create($mediaData);
            $product->productable()->associate($media);
            $product->save();
        }

        return $media->fresh(['product']);
    }
}
