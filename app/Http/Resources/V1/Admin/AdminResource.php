<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'avatar' => $this->getAvatarUrl(),
            'status' => $this->status->label(),
            'type' => 'admin',
            'created_at' => $this->created_at,
            'role' => $this->getAdminRole(),
            'permissions' => $this->getAdminPermissions(),
        ];
    }

    private function getAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    private function getAdminRole(): string
    {
        return $this->whenLoaded('roles', fn() => $this->getRoleNames()->first(), '');
    }

    private function getAdminPermissions(): array
    {
        return $this->whenLoaded('permissions',
            fn() => $this->getAllPermissions()->pluck('name')->toArray(), []);
    }
}
