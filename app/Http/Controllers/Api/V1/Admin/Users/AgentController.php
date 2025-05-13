<?php

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Users\StoreAgentRequest;
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use App\Services\Admin\Users\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgentController extends Controller
{
    public function __construct(readonly protected AgentService $agentService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->agentService->getAgent($request);
        return UserResource::collection($users);
    }

    public function show(User $user): JsonResponse
    {
        return UserResource::make($user->load(['roles', 'permissions']))->response();
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $user = $this->agentService->createAgent($request->validated());
        return UserResource::make($user)->response();
    }
}
