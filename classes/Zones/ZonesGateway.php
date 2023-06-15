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

// TODO: Rename Zone and move to app/Models

use Illuminate\Database\Eloquent\Model;

class ZonesGateway extends Model
{
    /**
     * @todo refactor usages to be Model aware
     * @param int $sector_id
     * @return ZonesGateway|null
     */
    public function selectZoneInfo(int $sector_id): ?ZonesGateway
    {
        return ZonesGateway::where('sector_id', $sector_id)->first();
    }

    /**
     * @todo refactor usage to be Model aware
     * @param int $zone
     * @return ZonesGateway|null
     */
    public function selectZoneInfoByZone(int $zone): ?ZonesGateway
    {
        return ZonesGateway::find($zone);
    }

    /**
     * @todo refactor usage to be Model aware
     * @param int $sector_id
     * @return ZonesGateway|null
     */
    public function selectMatchingZoneInfo(int $sector_id): ?ZonesGateway
    {
        return ZonesGateway::join('universe', 'universe.sector_id', '=', $sector_id)
            ->where('zones.zone_id', '=', 'universe.zone_id')
            ->first();
    }
}
