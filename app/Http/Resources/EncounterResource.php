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
        $options = $action->options();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $action->title(),
            'messages' => $action->messages(),
            'options' => array_reduce(array_keys($options), function($carry, $key) use ($options){
                $option = $options[$key];
                $carry[$key] = [
                    ...array_filter($option, fn($key) => $key !== 'class', ARRAY_FILTER_USE_KEY),
                    'link' => route('encounter.execute', ['action' => $key]),
                ];
                return $carry;
            }, []),
        ];
    }
}
