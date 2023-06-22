<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\MovementLog;

/**
 * @mixin MovementLog
 */
class MovementLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sector_id' => $this->sector_id,
            'timestamp' => $this->created_at->unix(),
            'sector' => new SectorResource($this->whenLoaded('sector')),
        ];
    }
}
