<?php

namespace App\Services\Products\SocialMedia;

use App\Models\Products\Product;
use App\Models\Products\InstagramAccount;
use Illuminate\Http\UploadedFile;

class InstagramAccountService
{
    public function storeOrUpdate(array $data): InstagramAccount
    {
        $instagramData = $this->getInstagramData($data);
        $productData = $this->getProductData($data, $instagramData);

        $product = Product::updateOrCreate(['uuid' => $productData['uuid']], $productData);
        return $this->updateOrCreateInstagramAccount($product, $instagramData);
    }

    private function getInstagramData(array $data): array
    {
        $instagramData = collect($data)->only([
            'url', 'business_locations', 'business_age',
            'followers_count', 'posts_count', 'average_likes',
            'analytics_screenshot', 'listing_images'
        ])->toArray();

        $instagramData['analytics_screenshot'] = $this->handleAnalyticsScreenshot($instagramData['analytics_screenshot'] ?? null);
        $instagramData['listing_images'] = $this->handleListingImages($instagramData['listing_images'] ?? []);
        return $this->sanitizeNullableData($instagramData);
    }

    private function getProductData(array $data, array $instagramData): array
    {
        return collect($data)->except(array_keys($instagramData))->toArray();
    }

    private function handleAnalyticsScreenshot(UploadedFile|null $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $file->store('products/social_media/instagram/analytics_screenshots', 'public');
    }

    private function handleListingImages(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store('products/social_media/instagram/listing_images', 'public');
            }
        }

        return $paths;
    }

    private function sanitizeNullableData($data): array
    {
        return collect($data)->filter()->toArray();
    }

    private function updateOrCreateInstagramAccount(Product $product, array $instagramData): InstagramAccount
    {
        $instagram = $product->productable;
        if ($instagram instanceof InstagramAccount) {
            $instagram->update($instagramData);
        } else {
            $instagram = InstagramAccount::create($instagramData);
            $product->productable_id = $instagram->id;
            $product->productable_type = InstagramAccount::class;
            $product->save();
        }

        return $instagram->fresh(['product']);
    }
}
