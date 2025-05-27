<?php

namespace App\Http\Resources\V1\Escrow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeSlotResource extends JsonResource
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
            'datetime' => $this->datetime->format('Y-m-d H:i:s'),
            'formatted_date' => $this->datetime->format('F j'),
            'formatted_time' => $this->datetime->format('h:i A T'),
            'admin' => $this->whenLoaded('admin'),
        ];
    }
}
