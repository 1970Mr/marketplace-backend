<?php

namespace App\Services\Admin\Users;

use App\Enums\Acl\RoleType;
use App\Enums\Users\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AgentService
{
    public function getAgent(Request $request): LengthAwarePaginator
    {
        return User::role('admin')
            ->latest()
            ->paginate($request->get('per_page', 10));
    }

    public function createAgent(array $data): User
    {
        $user = User::create($data);
        $user->assignRole(RoleType::ADMIN->value);
        return $user->fresh();
    }

    public function updateAgent(User $user, array $data): User
    {
        $data['avatar'] = $this->handleAvatarUpload($user, $data);
        $user->update($this->sanitizeNullableData($data));
        return $user->fresh(['roles', 'permissions']);
    }

    protected function handleAvatarUpload(User $user, array $data): string|null
    {
        if (isset($data['avatar']) && $data['avatar']->isValid()) {
            $this->deleteAvatar($user);
            return $data['avatar']->store('avatars', 'public');
        }
        return null;
    }

    protected function deleteAvatar(User $user): void
    {
        if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }

    protected function sanitizeNullableData(array $data): array
    {
        return collect($data)->filter()->toArray();
    }

    public function updatePermissions(User $user, array $permissions): array
    {
        $user->syncPermissions($permissions);
        return $user->getPermissionNames()->toArray();
    }

    public function toggleStatus(User $user, int $status): string
    {
        $user->update(['status' => $status]);
        return $user->status->label();
    }
}
