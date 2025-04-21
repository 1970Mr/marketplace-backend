<?php

namespace App\Services\Products\SocialMedia;

use App\Models\Products\Product;
use App\Models\Products\YoutubeChannel;
use Illuminate\Http\UploadedFile;

class YoutubeChannelService
{
    public function storeOrUpdate(array $data): YoutubeChannel
    {
        $youtubeData = $this->getYoutubeData($data);
        $productData = $this->getProductData($data, $youtubeData);

        $product = Product::updateOrCreate(['uuid' => $productData['uuid']], $productData);
        return $this->updateOrCreateYoutubeChannel($product, $youtubeData);
    }

    private function getYoutubeData(array $data): array
    {
        $youtubeData = collect($data)->only([
            'url', 'business_locations', 'channel_age',
            'subscribers', 'monthly_revenue', 'monthly_views',
            'monetization_method', 'analytics_screenshot', 'listing_images'
        ])->toArray();

        $youtubeData['analytics_screenshot'] = $this->handleAnalyticsScreenshot($youtubeData['analytics_screenshot'] ?? null);
        $youtubeData['listing_images'] = $this->handleListingImages($youtubeData['listing_images'] ?? []);
        return $this->sanitizeNullableData($youtubeData);
    }

    private function getProductData(array $data, array $youtubeData): array
    {
        return collect($data)->except(array_keys($youtubeData))->toArray();
    }

    private function handleAnalyticsScreenshot(UploadedFile|null $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $file->store('products/social_media/youtube/analytics_screenshots', 'public');
    }

    private function handleListingImages(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store('products/social_media/youtube/listing_images', 'public');
            }
        }

        return $paths;
    }

    private function sanitizeNullableData($data): array
    {
        return collect($data)->filter()->toArray();
    }

    private function updateOrCreateYoutubeChannel(Product $product, array $youtubeData): YoutubeChannel
    {
        $youtube = $product->productable;
        if ($youtube instanceof YoutubeChannel) {
            $youtube->update($youtubeData);
        } else {
            $youtube = YoutubeChannel::create($youtubeData);
            $product->productable_id = $youtube->id;
            $product->productable_type = YoutubeChannel::class;
            $product->save();
        }

        return $youtube->fresh(['product']);
    }
}
