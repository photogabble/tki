<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Planet;

/**
 * @mixin Planet
 */
class PlanetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!is_null($this->owner_id)) {
            $owner = [
                'id' => $this->owner_id,
                'name' => $this->owner->name,
            ];
        }

        return [
            'name' => $this->name,
            'owner' => $owner,
            // TODO: team
            // TODO: details
        ];
    }
}
