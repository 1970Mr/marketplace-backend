<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\InstagramAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\InstagramAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Instagram\InstagramAccountService;
use App\Services\Products\SocialMedia\Instagram\InstagramVerificationService;
use Illuminate\Http\JsonResponse;

class InstagramAccountController extends Controller
{
    public function __construct(
        readonly private InstagramAccountService $channelService,
        readonly private InstagramVerificationService $verificationService
    ) {}

    public function store(InstagramAccountRequest $request): InstagramAccountResource
    {
        $instagramAccount = $this->channelService->updateOrCreate($request->validated());
        return new InstagramAccountResource($instagramAccount);
    }

    public function verify(Product $product): JsonResponse
    {
        $this->verificationService->verifyProduct($product);
        return response()->json(['message' => 'Account verified successfully']);
    }
}
