<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\YoutubeChannelRequest;
use App\Http\Resources\V1\Products\SocialMedia\YoutubeChannelResource;
use App\Services\Products\SocialMedia\YoutubeChannelService;

class YoutubeChannelController extends Controller
{
    public function __construct(readonly private YoutubeChannelService $service)
    {
    }

    public function store(YoutubeChannelRequest $request): YoutubeChannelResource
    {
        $youtubeChannel = $this->service->storeOrUpdate($request->validated());
        return new YoutubeChannelResource($youtubeChannel);
    }
}
