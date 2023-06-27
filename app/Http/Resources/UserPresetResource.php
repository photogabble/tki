<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Preset;

/**
 * @mixin Preset
 */
class UserPresetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'link' => [
                'to_sector_id' => $this->preset,
                'hasVisited' => $user->hasVisitedSector($this->preset),
                'hasDanger' => false, // TODO: Implement hasDanger, maybe link with Zone Resource isFriendly
            ],
            'type' => $this->type,
        ];
    }
}
