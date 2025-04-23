<?php

namespace App\Services\Products\SocialMedia\Abstracts;

use App\Models\Products\Product;
use Illuminate\Http\UploadedFile;

abstract class BaseSocialMediaService
{
    protected array $serviceSpecificFields = [];
    protected string $fileStoragePath;

    public function storeOrUpdate(array $data): mixed
    {
        $mediaData = $this->getMediaData($data);
        $productData = $this->getProductData($data, $mediaData);

        $product = Product::updateOrCreate(['uuid' => $productData['uuid']], $productData);
        return $this->updateOrCreateMedia($product, $mediaData);
    }

    protected function getMediaData(array $data): array
    {
        $mediaData = collect($data)->only($this->serviceSpecificFields)->toArray();

        $mediaData['analytics_screenshot'] = $this->handleAnalyticsScreenshot($mediaData['analytics_screenshot'] ?? null);
        $mediaData['listing_images'] = $this->handleListingImages($mediaData['listing_images'] ?? []);

        return $this->sanitizeNullableData($mediaData);
    }

    protected function getProductData(array $data, array $mediaData): array
    {
        return collect($data)->except(array_keys($mediaData))->toArray();
    }

    protected function handleAnalyticsScreenshot(?UploadedFile $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $file->store("{$this->fileStoragePath}/analytics_screenshots", 'public');
    }

    protected function handleListingImages(array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store("{$this->fileStoragePath}/listing_images", 'public');
            }
        }
        return $paths;
    }

    protected function sanitizeNullableData(array $data): array
    {
        return collect($data)->filter()->toArray();
    }

    abstract protected function updateOrCreateMedia(Product $product, array $mediaData);
}
