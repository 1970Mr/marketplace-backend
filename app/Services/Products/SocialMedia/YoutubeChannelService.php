<?php

namespace App\Services\Products\SocialMedia;

use App\Models\Products\YoutubeChannel;
use Illuminate\Http\UploadedFile;

class YoutubeChannelService
{
    public function storeOrUpdate(array $data): YoutubeChannel
    {
        $data['analytics_screenshot'] = $this->handleAnalyticsScreenshot($data['analytics_screenshot'] ?? null);
        $data['listing_images'] = $this->handleListingImages($data['listing_images'] ?? []);

        return $this->saveChannel($data);
    }

    protected function handleAnalyticsScreenshot(UploadedFile|null $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $file->store('products/social_media/youtube/analytics_screenshots', 'public');
    }

    protected function handleListingImages(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store('products/social_media/youtube/listing_images', 'public');
            }
        }

        return $paths;
    }

    protected function saveChannel(array $data): YoutubeChannel
    {
        return YoutubeChannel::query()->updateOrCreate(
            ['uuid' => $data['uuid']],
            $data
        );
    }
}
