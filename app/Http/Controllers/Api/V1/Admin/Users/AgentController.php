<?php

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Enums\Acl\RoleType;
use App\Http\Requests\V1\Users\AgentRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgentController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->role(RoleType::ADMIN->value)
            ->latest()
            ->paginate($request->get('per_page', 10));
        return UserResource::collection($users);
    }

    public function store(AgentRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $user->assignRole(RoleType::ADMIN->value);
        return UserResource::make($user->fresh())->response();
    }
}
