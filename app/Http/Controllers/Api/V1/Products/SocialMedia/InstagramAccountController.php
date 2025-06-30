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
        readonly private InstagramAccountService      $service,
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
            $uuid = $product->productable->uuid;

            $path = parse_url($url, PHP_URL_PATH);
            $username = Str::of($path)->after('/')->before('/')->before('?');
            $username = $username->isNotEmpty() ? $username : null;

            $profile = $this->verificationService->verifyAccount($username, $uuid);

            return response()->json($profile);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}
