<?php

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Enums\Users\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Users\UserManagement\UserFilterRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use App\Services\Admin\Users\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserManagementController extends Controller
{
    public function __construct(readonly private UserManagementService $userService) {}

    public function index(UserFilterRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $users = $this->userService->getFilteredUsers($filters);

        return UserResource::collection($users);
    }

    // View Single User Details
    public function show(User $user): UserResource
    {
        return new UserResource($user);
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

    // Update User Status
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        // Validate the status
        $validated = $request->validate([
            'status' => 'required|integer|in:' . implode(',', array_column(UserStatus::cases(), 'value'))
        ]);

        // Update the status
        $user->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'User status updated successfully!',
            'user' => new UserResource($user)
        ]);
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
