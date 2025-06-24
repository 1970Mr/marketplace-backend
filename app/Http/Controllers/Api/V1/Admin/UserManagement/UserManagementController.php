<?php

namespace App\Http\Controllers\Api\V1\Admin\UserManagement;

use App\Enums\Users\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\UserManagement\ChangeUserStatusRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use App\Services\Admin\UserManagement\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserManagementController extends Controller
{
    public function __construct(readonly private UserManagementService $userService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->userService->getFilteredUsers($request->all());
        return UserResource::collection($users);
    }

    public function show(User $user): UserResource
    {
        return new UserResource(
            $user->loadCount(['products', 'escrowsAsBuyer', 'escrowsAsSeller'])
        );
    }

    // Add or Update User Note
    public function updateNote(Request $request, User $user): JsonResponse
    {
        // Validate the note
        $validated = $request->validate([
            'note' => 'nullable|string|min:2|max:3000',
        ]);

        // Update the note (allow empty note to clear it)
        $note = empty($validated['note']) ? null : trim($validated['note']);
        $user->update(['note' => $note]);

        return response()->json([
            'message' => empty($validated['note']) ? 'Note cleared successfully!' : 'Note updated successfully!',
            'user' => new UserResource($user)
        ]);
    }

    public function changeStatus(ChangeUserStatusRequest $request, User $user): JsonResponse
    {
        $user->update(['status' => $request->get('status')]);
        return UserResource::make($user->loadCount(['products', 'escrows']))->response();
    }

    // Get User Chats
    public function userChats(User $user): JsonResponse
    {
        $chats = $this->userService->getUserChats($user);

        return response()->json([
            'message' => 'Chats retrieved successfully!',
            'chats' => $chats,
        ]);
    }
}
