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
        $channel = YoutubeChannel::query()->updateOrCreate(
            ['uuid' => $request->get('uuid')],
            $request->validated()
        );

        return new YoutubeChannelResource($channel->fresh());
    }
}
