<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Link;
use Tki\Models\User;

/**
 * @mixin Link
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

        return [
            'to_sector_id' => $this->dest,
            'hasVisited' => $user->hasVisitedSector($this->dest),
            'hasDanger' => false, // TODO: Implement hasDanger, maybe link with Zone Resource isFriendly
        ];
    }
}
