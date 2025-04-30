<?php

namespace App\Services\Panel\Products;

use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;

readonly class SocialMediaService
{
    public function deleteAnalyticsScreenshotImages(string $imagePath): void
    {
        if (!empty($imagePath) && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    public function deleteListingImages(array $imagePaths): void
    {
        foreach ($imagePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function deleteSocialMediaImages(Product $product): void
    {
        $this->deleteAnalyticsScreenshotImages($product->analytics_screenshot ?? '');
        $this->deleteListingImages($product->listing_images ?? []);
    }
}
