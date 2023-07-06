<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Tki\Models\Encounter;

/**
 * @mixin Encounter
 * @property-read Encounter $resource
 */
class EncounterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $action = $this->resource->action();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $action->title(),
            'messages' => $action->messages(),
            'options' => [],
        ];
    }
}
