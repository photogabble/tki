<?php declare(strict_types = 1);
/**
 * classes/Zones/ZonesGateway.php from The Kabal Invasion.
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tki\Types\ZoneInfo;
use Tki\Types\ZonePermission;

/**
 * @property-read Universe[]|Collection $sectors
 * @property-read User|Team $owner
 * @property bool $is_team_zone
 * @property int $owner_id
 * @property ZonePermission $allow_beacon
 * @property ZonePermission $allow_attack
 * @property ZonePermission $allow_planetattack
 * @property ZonePermission $allow_warpedit
 * @property ZonePermission $allow_planet
 * @property ZonePermission $allow_trade
 * @property ZonePermission $allow_defenses
 * @property int $max_hull
 */
class Zone extends Model
{
    use HasFactory;

    protected $casts = [
        'allow_beacon' => ZonePermission::class,
        'allow_attack' => ZonePermission::class,
        'allow_planetattack' => ZonePermission::class,
        'allow_warpedit' => ZonePermission::class,
        'allow_planet' => ZonePermission::class,
        'allow_trade' => ZonePermission::class,
        'allow_defenses' => ZonePermission::class,
    ];

    public function sectors(): HasMany
    {
        return $this->hasMany(Universe::class);
    }

    public function owner(): BelongsTo
    {
        if ($this->team_zone === true) {
            return $this->belongsTo(Team::class);
        }
        return $this->belongsTo(User::class);
    }

    /**
     * Returns ZoneInfo from the perspective of User.
     * Refactored from zoneinfo.php.
     *
     * @param User $user
     * @return ZoneInfo
     */
    public function info(User $user): ZoneInfo
    {
        $name = ($this->id < 5)
            ? __('common.l_zname_'. $this->id)
            : $this->name;

        switch($this->id) {
            case 1:
                $owner = __('zoneinfo.l_zi_nobody');
                break;
            case 2:
                $owner = __('zoneinfo.l_zi_feds');
                break;
            case 3:
                $owner = __('zoneinfo.l_zi_traders');
                break;
            case 4:
                $owner = __('zoneinfo.l_zi_war');
                break;
            default:
                $owner = $this->owner->name;
        }

        $userCanEdit = false;

        if (
            ($this->team_zone && $user->team_id === $this->owner_id && $this->owner->creator === $user->id) ||
            (!$this->team_zone && $user->id === $this->owner_id)
        ) {
            $userCanEdit = true;
        }

        // TODO: implement isFriendly:
        //       Y: No danger, not a War Zone
        //       L: Is a War Zone, in a sector we had a battle
        //       N: Owned by team / player we are hostile towards

        return new ZoneInfo(
            $name,
            $owner,
            $userCanEdit,
            ZonePermission::Allow,
            $this->allow_beacon,
            $this->allow_attack,
            $this->allow_planetattack,
            $this->allow_warpedit,
            $this->allow_planet,
            $this->allow_trade,
            $this->allow_defenses,
            $this->max_hull,
            $user->ship->hull > $this->max_hull,
        );
    }

    /**
     * @param int $sector_id
     * @return Zone|null
     *@todo refactor usages to be Model aware
     */
    public function selectZoneInfo(int $sector_id): ?Zone
    {
        return Zone::where('sector_id', $sector_id)->first();
    }

    /**
     * @param int $zone
     * @return Zone|null
     *@todo refactor usage to be Model aware
     */
    public function selectZoneInfoByZone(int $zone): ?Zone
    {
        return Zone::find($zone);
    }

    /**
     * @param int $sector_id
     * @return Zone|null
     *@todo refactor usage to be Model aware
     */
    public function selectMatchingZoneInfo(int $sector_id): ?Zone
    {
        return Zone::join('universe', 'universe.sector_id', '=', $sector_id)
            ->where('zones.zone_id', '=', 'universe.zone_id')
            ->first();
    }
}
