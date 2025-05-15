<?php
namespace App\Http\Controllers\Api\V1\Admin\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\V1\Admin\Agents\{
    StoreAgentRequest,
    ToggleAgentStatusRequest,
    UpdateAgentPermissionsRequest,
    UpdateAgentRequest
};
use App\Http\Resources\V1\Admin\AdminResource;
use App\Models\Admin;
use App\Services\Admin\Agents\AgentService;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function __construct(protected AgentService $agentService) {}

    public function index(Request $request): JsonResponse
    {
        $admins = $this->agentService->getAgents($request);
        return AdminResource::collection($admins)->response();
    }

    public function show(Admin $admin): JsonResponse
    {
        $admin->load(['roles','permissions']);
        return AdminResource::make($admin)->response();
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $admin = $this->agentService->createAgent($request->validated());
        return AdminResource::make($admin)->response();
    }

    public function update(UpdateAgentRequest $request, Admin $admin): JsonResponse
    {
        $updated = $this->agentService->updateAgent($admin, $request->validated());
        return AdminResource::make($updated)->response();
    }

    public function updatePermissions(UpdateAgentPermissionsRequest $request, Admin $admin): JsonResponse
    {
        $updatedPerms = $this->agentService->updatePermissions($admin, $request->input('permissions'));
        return response()->json(['permissions' => $updatedPerms]);
    }

    public function toggleStatus(ToggleAgentStatusRequest $request, Admin $admin): JsonResponse
    {
        $statusLabel = $this->agentService->toggleStatus($admin, $request->input('status'));
        return response()->json(['status' => $statusLabel]);
    }
}
