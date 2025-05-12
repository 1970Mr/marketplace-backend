<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
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
        return response()->json([
            'access_token' => $this->service->checkAuth($request),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getUser(Request $request): UserResource
    {
        $user = $request->user();
        $user->load(['roles', 'permissions']);
        return new UserResource($user);
    }
}
