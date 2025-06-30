<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\TiktokAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Tiktok\TiktokAccountService;
use App\Services\Products\SocialMedia\Tiktok\TiktokVerificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TiktokAccountController extends Controller
{
    public function __construct(
        readonly private TiktokAccountService $service,
        readonly private TiktokVerificationService $verificationService
    )
    {
    }

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
            $url = $product->productable->url;
            $result = $this->verificationService->verify($url, $product->uuid);

            // If verification was successful, update the product
            if ($result['contains_uuid']) {
                $product->update(['is_verified' => true]);

                return response()->json([
                    'success' => true,
                    'contains_uuid' => true,
                    'message' => 'TikTok account verified successfully!',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'contains_uuid' => false,
                'message' => 'Verification code not found in bio. Please make sure you have added the UUID to your TikTok bio.',
                'data' => $result
            ]);

        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}
