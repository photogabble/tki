<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Universe;

/**
 * @mixin Universe
 */
class SectorResource extends JsonResource
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
            'zone' => new ZoneResource($this->zone),
            'planets' => PlanetResource::collection($this->planets),
            'ports' => null, // TODO implement
            'defenses' => SectorDefenseResource::collection($this->defenses),
            'links' => LinkResource::collection($this->links),
        ];
    }
}
