<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int organics
 * @property int $ore
 * @property int $goods
 * @property int $energy
 * @property int $colonists
 * @property int $torps
 * @property int $fighters
 * @property int $credits
 * @property int|null $owner_id
 * @property int|null $team_id
 * @property-read Team|null $team
 * @property-read Ship|null $owner
 */
class Planet extends Model
{
    use HasFactory;

    protected $fillable = [
        'organics',
        'ore',
        'goods',
        'energy',
        'colonists',
        'torps',
        'fighters',
        'credits',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // TODO: Need to move owner_id from relating to Ship to User
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'owner_id');
    }
}
