<?php declare(strict_types = 1);
/**
 * classes/LogMove.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tki\Types\MovementMode;

/**
 * @property Carbon $created_at
 * @property int $sector_id
 * @property-read Universe $sector
 */
class MovementLog extends Model
{
    protected $fillable = [
        'user_id', 'sector_id', 'turns_used', 'energy_scooped', 'mode'
    ];

    protected $casts = [
        'mode' => MovementMode::class,
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public static function writeLog(int $user_id, int $sector_id, MovementMode $mode = MovementMode::RealSpace, int $turnsUsed = 0, int $energyScooped = 0): void
    {
        static::query()
            ->create([
                'user_id' => $user_id,
                'sector_id' => $sector_id,
                'mode' => $mode,
                'turns_used' => $turnsUsed,
                'energy_scooped' => $energyScooped,
            ]);

        // Clear Response cache for map pages
        Cache::tags('galaxy-'.$user_id)->clear();
    }
}
