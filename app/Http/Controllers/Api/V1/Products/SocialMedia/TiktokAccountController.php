<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\TiktokAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Tiktok\TiktokVerificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TiktokAccountController extends Controller
{
    public function __construct(
        readonly private \App\Services\Products\SocialMedia\Tiktok\TiktokAccountService $service,
        readonly private TiktokVerificationService                                      $verificationService
    ) {}

    public function store(TiktokAccountRequest $request): TiktokAccountResource
    {
        $tiktokAccount = $this->service->storeOrUpdate($request->validated());
        return new TiktokAccountResource($tiktokAccount);
    }

    /**
     * Verify a TikTok account by checking the presence of a UUID in the profile bio.
     *
     * @param Product $product
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verify(Product $product): JsonResponse
    {
        try {
            // Implementing bio receiving operations...
            $url = $product->productable->url;
            $result = $this->verificationService->verify($url, $product->uuid);
            return response()->json([
                'message' => $result['contains_uuid'] ? __('Account verified successfully') :  __('Account not verified'),
                'data' => $result
            ]);

            $bio = 'Example bio';

            if (!str_contains($bio, $product->uuid)) {
                throw ValidationException::withMessages([
                    'verification' => __('UUID not found in account bio')
                ]);
            }

            $product->update([
                'is_verified' => true
            ]);

            return response()->json([
                'message' => __('Account verified successfully'),
                'data' => new TiktokAccountResource($product->productable)
            ]);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}
