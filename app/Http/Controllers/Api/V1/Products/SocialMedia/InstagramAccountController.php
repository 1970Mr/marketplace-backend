<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\InstagramAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\InstagramAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\Instagram\InstagramAccountService;
use App\Services\Products\SocialMedia\Instagram\InstagramVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InstagramAccountController extends Controller
{
    public function __construct(
        readonly private InstagramAccountService $service,
        readonly private InstagramVerificationService $verificationService
    ) {}

    public function store(InstagramAccountRequest $request): InstagramAccountResource
    {
        $instagramAccount = $this->service->storeOrUpdate($request->validated());
        return new InstagramAccountResource($instagramAccount);
    }

    /**
     * Verify an Instagram account by reading the bio of the provided username.
     *
     * @param Product $product The product to verify.
     * @return JsonResponse The verification result.
     * @throws ValidationException If verification fails.
     */
    public function verify(Product $product): JsonResponse
    {
        try {
            $url = $product->productable->url;
            $uuid = $product->uuid;

            $path = parse_url($url, PHP_URL_PATH);
            $username = Str::of($path)->after('/')->before('/')->before('?');
            $username = $username->isNotEmpty() ? $username : null;

            if (!$username) {
                throw new \Exception('Invalid Instagram URL. Could not extract username.');
            }

            $result = $this->verificationService->verifyAccount($username, $uuid);

            // If verification was successful, update the product
            if ($result['contains_uuid']) {
                $product->update(['is_verified' => true]);

                return response()->json([
                    'success' => true,
                    'contains_uuid' => true,
                    'message' => 'Instagram account verified successfully!',
                    'profile' => $result['profile'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'contains_uuid' => false,
                'message' => 'Verification code not found in bio. Please make sure you have added the UUID to your Instagram bio.',
                'profile' => $result['profile'] ?? null
            ]);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}
