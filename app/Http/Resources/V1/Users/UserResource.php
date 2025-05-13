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
            'avatar' => $this->avatar,
            'country' => $this->country,
            'note' => $this->note,
            'last_activity_at' => $this->last_activity_at?->diffForHumans(),
            'status' => $this->status->label(),
            'created_at' => $this->created_at,
            'role' => $this->getUserRole(),
            'permissions' => $this->getUserPermissions(),
            'created_at' => $this->created_at->toDateTimeString(),
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
