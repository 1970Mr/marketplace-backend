<?php
namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Users\Agents\{
    StoreAgentRequest,
    UpdateAgentRequest,
    UpdateAgentPermissionsRequest,
    ToggleAgentStatusRequest
};
use App\Http\Resources\V1\Users\UserResource;
use App\Models\User;
use App\Services\Admin\Users\AgentService;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function __construct(protected AgentService $agentService) {}

    public function index(): JsonResponse
    {
        $users = $this->agentService->getAgent(request());
        return UserResource::collection($users)->response();
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles','permissions']);
        return UserResource::make($user)->response();
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $user = $this->agentService->createAgent($request->validated());
        return UserResource::make($user)->response();
    }

    public function update(UpdateAgentRequest $request, User $user): JsonResponse
    {
        $updated = $this->agentService->updateAgent($user, $request->validated());
        return UserResource::make($updated)->response();
    }

    public function updatePermissions(UpdateAgentPermissionsRequest $request, User $user): JsonResponse
    {
        $updatedPerms = $this->agentService->updatePermissions($user, $request->input('permissions'));
        return response()->json(['permissions' => $updatedPerms]);
    }

    public function toggleStatus(ToggleAgentStatusRequest $request, User $user): JsonResponse
    {
        $statusLabel = $this->agentService->toggleStatus($user, $request->input('status'));
        return response()->json(['status' => $statusLabel]);
    }
}
