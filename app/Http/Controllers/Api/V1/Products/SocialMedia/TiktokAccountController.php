<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\TiktokAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Models\Products\Product;
use App\Services\Products\SocialMedia\TiktokAccountService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TiktokAccountController extends Controller
{
    public function __construct(readonly private TiktokAccountService $service)
    {
    }

    public function store(TiktokAccountRequest $request): TiktokAccountResource
    {
        $tiktokAccount = $this->service->storeOrUpdate($request->validated());
        return new TiktokAccountResource($tiktokAccount);
    }

    public function verify(Product $product): JsonResponse
    {
        try {
            // Implementing bio receiving operations...
            $url = $product->productable->url;
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
