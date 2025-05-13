<?php

namespace App\Services\Admin\Users;

use App\Enums\Acl\RoleType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AgentService
{
    public function getAgent(Request $request): LengthAwarePaginator
    {
        return User::query()
            ->role(RoleType::ADMIN->value)
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function createAgent(array $agentData): User
    {
        $user = User::create($agentData);
        $user->assignRole(RoleType::ADMIN->value);
        return $user->fresh();
    }
}
