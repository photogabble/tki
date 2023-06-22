<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Universe;
use Tki\Models\User;

/**
 * @mixin Universe
 *
 * Virtual Attributes only loaded occasionally making this Resource
 * SectorResourceWithPlayerMeta on the frontend:
 * @property-read bool $is_current_sector
 * @property-read bool $has_visited
 * @property-read bool $has_danger
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
        if ($this->is_current_sector === true && static::class === SectorResource::class) {
            return (new SectorScanResource($this->resource))->toArray($request);
        }

        return [
            'id' => $this->id,
            'beacon' => $this->beacon,
            'port_type' => (!is_null($this->has_visited) && $this->has_visited === false) ? 'unknown' : $this->port_type,

            // Player meta, based upon the players relationship to this sector
            'is_current_sector' => $this->is_current_sector,
            'has_visited' => $this->has_visited,
            'has_danger' => $this->has_danger, // TODO: implement danger
        ];
    }
}
