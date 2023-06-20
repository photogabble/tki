<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\SectorDefense;

/**
 * @mixin SectorDefense
 */
class SectorDefenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'owner' => [
                'name' => $this->owner->name,
                'id' => $this->owner->id,
            ],
            'type' => $this->defense_type,
            'quantity' => $this->quantity,
        ];
    }
}
