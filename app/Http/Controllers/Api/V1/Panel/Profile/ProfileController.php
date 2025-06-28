<?php

namespace App\Http\Controllers\Api\V1\Panel\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\ChangeEmailRequest;
use App\Http\Requests\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\V1\Profile\ProfileUpdateRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Services\Panel\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService)
    {
    }

    public function show(): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->updateProfileHandler($request->user(), $request->validated());
        return UserResource::make($updatedUser)->response();
    }

    public function changeEmail(ChangeEmailRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->changeEmailHandler($request->user(), $request->validated());
        return UserResource::make($updatedUser)->response();
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->changePasswordHandler($request->user(), $request->validated());
        return UserResource::make($updatedUser)->response();
    }
}
