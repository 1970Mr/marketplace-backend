<?php

namespace App\Http\Controllers\Api\V1\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\UserManagement\ChangeUserStatusRequest;
use App\Http\Requests\V1\Admin\UserManagement\UpdateUserNoteRequest;
use App\Http\Resources\V1\Messenger\ChatResource;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use App\Services\Admin\UserManagement\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(readonly private UserManagementService $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getFilteredUsers($request->all());
        return UserResource::collection($users)->response();
    }

    public function show(User $user): JsonResponse
    {
        return UserResource::make(
            $this->userService->getUserWithRelations($user)
        )->response();
    }

    public function updateNote(UpdateUserNoteRequest $request, User $user): JsonResponse
    {
        $user->update(['note' => $request->get('note')]);
        return UserResource::make($user )->response();
    }

    public function changeStatus(ChangeUserStatusRequest $request, User $user): JsonResponse
    {
        $user->update(['status' => $request->get('status')]);
        return UserResource::make($user)->response();
    }

    public function userChats(User $user): JsonResponse
    {
        return ChatResource::make(
            $this->userService->getUserChats($user)
        )->response();
    }
}
