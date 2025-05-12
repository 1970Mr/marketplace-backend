<?php

namespace App\Http\Resources\V1\Users;

use App\Enums\Acl\RoleType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country' => $this->country,
            'note' => $this->note,
            'last_activity_at' => $this->last_activity_at?->diffForHumans(),
            'status' => $this->status->label(),
            'role' => $this->getUserRole(),
            'permissions' => $this->getUserPermissions(),
        ];
    }

    private function getUserRole(): string
    {
        return $this->whenLoaded('roles',
            fn() => $this->getRoleNames()->first() ?? RoleType::NORMAL->value,
            RoleType::NORMAL->value);
    }

    private function getUserPermissions(): array
    {
        return $this->whenLoaded('permissions',
            fn() => $this->getAllPermissions()->pluck('name')->toArray() ?? [],
            []);
    }
}
