<?php

namespace App\Http\Resources\V1\Users;

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
            'phone_number' => $this->phone_number,
            'avatar' => $this->getAvatarUrl(),
            'company_name' => $this->company_name,
            'country' => $this->country,
            'note' => $this->note,
            'last_activity_at' => $this->last_activity_at?->diffForHumans(),
            'status' => $this->status->label(),
            'type' => 'user',
            'created_at' => $this->created_at,
        ];
    }

    private function getAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
