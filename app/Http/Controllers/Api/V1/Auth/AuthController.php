<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getUser(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
