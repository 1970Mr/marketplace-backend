<?php

namespace App\Services\Products\SocialMedia\Tiktok;

use App\Models\Products\Product;
use App\Models\Products\TiktokAccount;
use App\Services\Products\SocialMedia\Abstracts\BaseSocialMediaService;

class TiktokAccountService extends BaseSocialMediaService
{
    protected array $serviceSpecificFields = [
        'url', 'business_locations', 'business_age',
        'followers_count', 'posts_count', 'average_likes',
        'analytics_screenshot', 'listing_images'
    ];

    protected string $fileStoragePath = 'products/social_media/tiktok';

    protected function updateOrCreateMedia(Product $product, array $mediaData): TiktokAccount
    {
        $media = $product->productable;

        if ($media instanceof TiktokAccount) {
            $media->update($mediaData);
        } else {
            $media = TiktokAccount::create($mediaData);
            $product->productable()->associate($media);
            $product->save();
        }

        return $media->fresh(['product']);
    }
}
