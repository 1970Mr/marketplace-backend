<?php

namespace App\Services\Admin\Agents;

use App\Enums\Acl\RoleType;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AgentService
{
    public function getPaginatedAgents(Request $request): LengthAwarePaginator
    {
        return Admin::with('roles')
            ->role('admin')
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function getAllAgents(): Collection
    {
        return Admin::with('roles')
            ->role('admin')
            ->latest()
            ->get();
    }

    public function createAgent(array $data): Admin
    {
        $admin = Admin::create($data);
        $admin->assignRole(RoleType::ADMIN->value);
        return $admin->fresh();
    }

    public function updateAgent(Admin $admin, array $data): Admin
    {
        $data['avatar'] = $this->handleAvatarUpload($admin, $data);
        $admin->update($this->sanitizeNullableData($data));
        return $admin->fresh(['roles', 'permissions']);
    }

    protected function handleAvatarUpload(Admin $admin, array $data): string|null
    {
        if (isset($data['avatar']) && $data['avatar']->isValid()) {
            $this->deleteAvatar($admin);
            return $data['avatar']->store('avatars', 'public');
        }
        return null;
    }

    protected function deleteAvatar(Admin $admin): void
    {
        if (!empty($admin->avatar) && Storage::disk('public')->exists($admin->avatar)) {
            Storage::disk('public')->delete($admin->avatar);
        }
    }

    protected function sanitizeNullableData(array $data): array
    {
        return collect($data)->filter()->toArray();
    }

    public function updatePermissions(Admin $admin, array $permissions): array
    {
        $admin->syncPermissions($permissions);
        return $admin->getPermissionNames()->toArray();
    }

    public function toggleStatus(Admin $admin, int $status): string
    {
        $admin->update(['status' => $status]);
        return $admin->status->label();
    }
}
