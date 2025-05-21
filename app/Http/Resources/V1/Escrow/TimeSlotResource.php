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
            'weekday' => $this->weekday->name,
            'weekday_label' => $this->weekday->label(),
            'start_time' => $this->start_time->format('H:i'),
        ];
    }
}
