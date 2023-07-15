<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Tki\Models\System;

/**
 * @mixin System
 */
class SectorScanResource extends SectorResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            // Scanned Resources, the player must either be in this sector currently
            // or have spent resources scanning to obtain the information.
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'planets' => PlanetResource::collection($this->whenLoaded('planets')),
            'ports' => null, // TODO implement
            'defenses' => SectorDefenseResource::collection($this->whenLoaded('defenses')),
            'links' => LinkResource::collection($this->whenLoaded('links')),
        ]);
    }
}
