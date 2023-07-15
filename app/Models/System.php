<?php declare(strict_types=1);
/**
 * classes/Sectors/SectorsGateway.php from The Kabal Invasion.
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

// TODO: rename Sector and handle all the migration stuff

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Psy\Exception\DeprecatedException;
use DB;


/**
 * @property string $beacon
 * @property-read Zone $zone
 * @property-read Collection|Link[] $links
 * @property-read Collection|Planet[] $planets
 * @property-read Collection|Ship[] $ships
 * @property-read Collection|SectorDefense[] $defenses
 * @property-read Collection $ports
 * @property-read Collection|MovementLog[] $movementLog
 *
 * Virtual Attributes, these are set by the queryForUser method or elsewhere
 * when needing to flag one or all of the below values:
 * @property bool $has_visited
 * @property bool $is_current_sector
 */
class System extends Model
{
    use HasFactory;

    protected $casts = [
        // These are virtual attributes, loaded for the Galaxy Map
        // See: GameController::galaxyMap
        'is_current_sector' => 'bool',
        'has_visited' => 'bool',
        'has_danger' => 'bool',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function planets(): HasMany
    {
        return $this->hasMany(Planet::class, 'sector_id');
    }

    public function defenses(): HasMany
    {
        return $this->hasMany(SectorDefense::class, 'sector_id');
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class, 'sector_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'start');
    }

    public function movementLog(): HasMany
    {
        return $this->hasMany(MovementLog::class, 'sector_id');
    }

    public function latestMovementLog(): HasOne
    {
        return $this->hasOne(MovementLog::class, 'sector_id')->latestOfMany();
    }

    /**
     * This functionality was refactored from rsmove.php, which was also
     * similar or the same as classes/Realspace.php.
     * @param Ship $ship
     * @param System $destination
     * @return array
     */
    public function calculateRealSpaceMove(Ship $ship, System $destination): array
    {
        if ($destination->id === $ship->sector_id) {
            return [
                'turns' => 0,
                'energyScooped' => 0,
            ];
        }

        // Calculate the distance.
        $deg = pi() / 180;
        $sa1 = $this->angle1 * $deg;
        $sa2 = $this->angle2 * $deg;
        $fa1 = $destination->angle1 * $deg;
        $fa2 = $destination->angle2 * $deg;

        $xx = ($this->distance * sin($sa1) * cos($sa2)) - ($destination->distance * sin($fa1) * cos($fa2));
        $yy = ($this->distance * sin($sa1) * sin($sa2)) - ($destination->distance * sin($fa1) * sin($fa2));
        $zz = ($this->distance * cos($sa1)) - ($destination->distance * cos($fa1));

        $distance = (int)round(sqrt(pow($xx, 2) + pow($yy, 2) + pow($zz, 2)));

        // Calculate the speed of the ship.
        $shipSpeed = pow(config('game.level_factor'), $ship->engines);

        // Calculate the trip time.
        $turns = (int)round($distance / $shipSpeed);

        return [
            'turns' => $turns,
            'energyScooped' => \Tki\Actions\Move::calcFuelScooped($ship, $distance, $turns),
        ];
    }

    /**
     * Helper function for getting a sector (or sectors) from the point of view of the
     * player. If this begins getting more complex, maybe turn into a macro.
     *
     * @param User $user
     * @return Builder
     */
    public static function queryForUser(User $user): Builder
    {
        return System::query()
            ->select([
                'systems.*',
                DB::raw("(SELECT COUNT(id) FROM movement_logs WHERE `movement_logs`.`sector_id` = `systems`.`id` AND `movement_logs`.`user_id` = $user->id) > 0 as has_visited"),
                DB::raw("(SELECT COUNT(id) FROM ships WHERE `ships`.`sector_id` = `systems`.`id` AND `ships`.`id` = $user->ship_id) > 0 as is_current_sector"),
            ]);
    }

    /**
     * @param int $sector_id
     * @return array|bool
     * @todo refactor all usages to use sector relationship
     * @deprecated
     */
    public function selectSectorInfo(int $sector_id): array|bool
    {
        throw new DeprecatedException('refactor usage to use sector relationship');
    }
}
