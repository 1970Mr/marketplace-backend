<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\TiktokAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Tiktok\TiktokAccountService;
use App\Services\Products\SocialMedia\Tiktok\TiktokVerificationService;
use Illuminate\Http\JsonResponse;

class TiktokAccountController extends Controller
{
    public function __construct(
        readonly private TiktokAccountService $channelService,
        readonly private TiktokVerificationService $verificationService
    )
    {
    }

    public function store(TiktokAccountRequest $request): TiktokAccountResource
    {
        $tiktokAccount = $this->channelService->updateOrCreate($request->validated());
        return new TiktokAccountResource($tiktokAccount);
    }

    public function verify(Product $product): JsonResponse
    {
        $this->verificationService->verifyProduct($product);
        return response()->json(['message' => 'Account verified successfully']);
    }
}
