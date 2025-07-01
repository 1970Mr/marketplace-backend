<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\YoutubeChannelRequest;
use App\Http\Resources\V1\Products\SocialMedia\YoutubeChannelResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Youtube\YoutubeChannelService;
use App\Services\Products\SocialMedia\Youtube\YoutubeVerificationService;
use Illuminate\Http\JsonResponse;

class YoutubeChannelController extends Controller
{
    public function __construct(
        readonly private YoutubeChannelService $channelService,
        readonly private YoutubeVerificationService $verificationService
    )
    {
    }

    public function store(YoutubeChannelRequest $request): YoutubeChannelResource
    {
        $youtubeChannel = $this->channelService->updateOrCreate($request->validated());
        return new YoutubeChannelResource($youtubeChannel);
    }

    public function verify(Product $product): JsonResponse
    {
        $this->verificationService->verifyProduct($product);
        return response()->json(['message' => 'Channel verified successfully']);
    }
}
