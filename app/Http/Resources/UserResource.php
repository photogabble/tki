<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\User;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'lang' => $this->lang,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,

            'name' => $this->name,
            'ship' => new ShipResource($this->whenLoaded('ship')),
            'presets' => UserPresetResource::collection($this->whenLoaded('presets')),
            'turns' => $this->turns,
            'turns_used' => $this->turns_used,
            'credits' => $this->credits,
            'score' => $this->score,
        ];
    }
}
