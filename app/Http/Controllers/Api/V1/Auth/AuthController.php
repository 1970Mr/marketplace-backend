<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\EmailVerificationRequest;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\PasswordResetRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(readonly private AuthService $service)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->createUser($request);
        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tokenOrAccessToken = $this->service->checkAuth($request);

        if (strlen($tokenOrAccessToken) === 64) {
            return response()->json([
                'message' => '2FA code sent',
                '2fa_required' => true,
                'temp_2fa_token' => $tokenOrAccessToken,
            ], 202);
        }

        return response()->json(['access_token' => $tokenOrAccessToken,]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return UserResource::make($request->user())->response();
    }

    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->sendPasswordResetNotification($request->email);
        return response()->json(['message' => 'Password reset link sent']);
    }

    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        $this->service->resetPassword($request->validated());
        return response()->json(['message' => 'Password reset successfully']);
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $response = $this->service->verifyEmailHandler($request->validated(), $request->user());
        return response()->json($response);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $response = $this->service->sendVerificationEmailHandler($request);
        return response()->json($response);
    }

    public function verify2FA(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|digits:6',
            'temp_2fa_token' => 'required|string',
        ]);

        $accessToken = $this->service->verify2FACode(
            $request->input('temp_2fa_token'),
            $request->input('code')
        );

        return response()->json(['access_token' => $accessToken,]);
    }
}
