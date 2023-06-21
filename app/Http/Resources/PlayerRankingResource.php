<?php

namespace Tki\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Actions\Character;
use Tki\Models\Team;
use Tki\Models\User;

/**
 * @property string $name
 * @property int $turns_used
 * @property int $score
 * @property Carbon $last_login
 * @property int $rating
 * @property int $efficiency
 * @property Team $team
 * @property-read User $resource
 * @mixin User
 */
class PlayerRankingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'insignia' => (new Character)->getInsignia($this->resource),
            'turns_used' => $this->turns_used,
            'score' => $this->score,
            'last_login' => [
                'nice' => $this->last_login->ago(),
                'unix' => $this->last_login->unix(),
            ],
            'rating' => $this->rating,
            'efficiency' => $this->efficiency,
            'team' => $this->team,
        ];
    }
}
