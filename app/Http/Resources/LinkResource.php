<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Link;
use Tki\Models\Universe;
use Tki\Models\User;

/**
 * @mixin Link|Universe
 */
class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        if ($this->resource instanceof Link) {
            return [
                'to_sector_id' => $this->dest,
                'hasVisited' => $user->hasVisitedSector($this->dest),
                'hasDanger' => false, // TODO: Implement hasDanger, maybe link with Zone Resource isFriendly
            ];
        }

        if ($this->resource instanceof Universe) {
            return [
                'to_sector_id' => $this->id,
                'hasVisited' => $this->has_visited,
                'hasDanger' => false,
            ];
        }
    }
}
