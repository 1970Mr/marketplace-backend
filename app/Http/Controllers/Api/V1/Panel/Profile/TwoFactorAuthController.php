<?php

namespace App\Http\Controllers\Api\V1\Panel\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Panel\Profile\VerifyTwoFactorRequest;
use App\Services\Panel\Profile\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorAuthController extends Controller
{
    public function __construct(readonly private TwoFactorAuthService $twoFactorAuthService)
    {
    }

    public function enableTwoFactor(Request $request): JsonResponse
    {
        $secret_key = $this->twoFactorAuthService->enableTwoFactor($request->user());
        return response()->json([
            'message' => 'Two-factor authentication enabled successfully',
            'data' => $secret_key,
        ]);
    }

    public function disableTwoFactor(Request $request): JsonResponse
    {
        $this->twoFactorAuthService->disableTwoFactor($request->user());
        return response()->json([
            'message' => 'Two-factor authentication disabled successfully',
        ]);
    }

    public function getTwoFactorQrCode(Request $request): JsonResponse
    {
        $response = $this->twoFactorAuthService->getTwoFactorQrCode($request->user());
        return response()->json([
            'message' => 'QR code generated successfully',
            'data' => $response
        ]);
    }

    public function verifyTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        $this->twoFactorAuthService->verifyTwoFactor($request->user(), $request->input('code'));
        return response()->json([
            'message' => 'Two-factor authentication verified successfully',
        ]);
    }

    public function getRecoveryCodes(Request $request): JsonResponse
    {
        $codes = $this->twoFactorAuthService->getRecoveryCodes($request->user());
        return response()->json([
            'data' => $codes
        ]);
    }
}
