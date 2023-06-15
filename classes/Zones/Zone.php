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

namespace Tki\Zones; // Domain Entity organization pattern, zones objects

// TODO: move to app/Models

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Universe[]|Collection $sectors
 */
class Zone extends Model
{
    use HasFactory;

    public function sectors(): HasMany
    {
        return $this->hasMany(Universe::class);
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
