<?php

namespace Tki\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\Team;
use Tki\Models\User;

/**
 * @property string $name
 * @property int $player_count
 * @property int $turns_used_sum
 * @property int $score_sum
 * @property int $rating_sum
 * @property int $efficiency
 * @mixin Team
 */
class TeamRankingResource extends JsonResource
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
            'turns_used' => $this->turns_used_sum,
            'score' => $this->score_sum,
            'player_count' => $this->player_count,
            'rating' => $this->rating_sum,
            'efficiency' => $this->efficiency,
        ];
    }
}
