<?php

namespace App\Services\Products\SocialMedia;

use App\Models\Products\InstagramAccount;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Abstracts\BaseSocialMediaService;

class InstagramAccountService extends BaseSocialMediaService
{
    protected array $serviceSpecificFields = [
        'url', 'business_locations', 'business_age',
        'followers_count', 'posts_count', 'average_likes',
        'analytics_screenshot', 'listing_images'
    ];

    protected string $fileStoragePath = 'products/social_media/instagram';

    protected function updateOrCreateMedia(Product $product, array $mediaData): InstagramAccount
    {
        $media = $product->productable;

        if ($media instanceof InstagramAccount) {
            $media->update($mediaData);
        } else {
            $media = InstagramAccount::create($mediaData);
            $product->productable()->associate($media);
            $product->save();
        }

        return $media->fresh(['product']);
    }
}
