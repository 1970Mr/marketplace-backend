<?php

namespace App\Http\Controllers\Api\V1\Products\Website;

use App\Http\Controllers\Controller;
use App\Models\Products\Product;
use App\Services\Products\Website\DomainVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DomainController extends Controller
{
    public function __construct(
        readonly private DomainVerificationService $verificationService
    ) {}

    public function verify(Product $product): JsonResponse
    {
        try {
            $url = $product->productable->url;
            $uuid = $product->productable->uuid;

            $response = $this->verificationService->verifyDomain($url, $uuid);

            return response()->json($response);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}
