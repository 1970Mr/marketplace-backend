<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Resources\V1\Admin\AdminResource;
use App\Http\Resources\V1\Users\UserResource;
use App\Services\Admin\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(readonly private AuthService $service)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json([
            'access_token' => $this->service->checkAuth($request),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('admin-api')->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user('admin-api');
        $user->load(['roles.permissions', 'permissions']);
        return AdminResource::make($user)->response();
    }
}
