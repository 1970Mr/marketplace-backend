<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedial;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedial\YoutubeChannelRequest;
use App\Http\Resources\V1\Products\SocialMedial\YoutubeChannelResource;
use App\Models\Products\YoutubeChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class YoutubeChannelController extends Controller
{
    public function store(YoutubeChannelRequest $request): YoutubeChannelResource
    {
        $data = $request->validated();

        if ($request->hasFile('analytics_screenshot')) {
            $data['analytics_screenshot'] = $request->file('analytics_screenshot')?->store('products/social_media/youtube/analytics_screenshots', 'public');
        }

        if ($request->hasFile('listing_images')) {
            $listingImagesPaths = [];
            foreach ($request->file('listing_images') as $image) {
                $listingImagesPaths[] = $image->store('products/social_media/youtube/listing_images', 'public');
            }
            $data['listing_images'] = $listingImagesPaths;
        }

        $channel = YoutubeChannel::query()->updateOrCreate(
            ['uuid' => $data['uuid']],
            $data
        );

        return new YoutubeChannelResource($channel->fresh());
    }
}
