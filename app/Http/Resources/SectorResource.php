<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Universe;

/**
 * @mixin Universe
 *
 * On the galaxy map thousands of sectors are being json_encoded for passing to the
 * frontend; this was adding up to 400ms to the page load. To help alleviate that
 * the resource can either be the Universe model _or_ if its never been visited
 * then resource will be sector id:
 * @property-read Universe|int $resource
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
        if (is_numeric($this->resource)) {
            return [
                'id' => $this->resource,
                'port_type' => 'unknown',
                'is_current_sector' => false,
                'has_visited' => false,
                'has_danger' => false,
            ];
        }

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
