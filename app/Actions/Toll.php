<?php declare(strict_types = 1);
/**
 * Actions/Toll.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2023 Simon Dann, The Kabal Invasion development team, Ron Harwood, and the BNT development team
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

namespace Tki\Actions;

use Tki\Models\PlayerLog;
use Tki\Types\LogEnums;

class Toll
{
    /**
     * This function distributes the toll paid by a player entering a system with Fighter defenses
     * between all players who added fighters to the systems defense. The split is always equal.
     *
     * @param int $sector
     * @param int $toll
     * @param int $total_fighters
     * @return void
     */
    public static function distribute(int $sector, int $toll, int $total_fighters): void
    {
        $defensePresent = \Tki\Models\SectorDefense::fighters($sector);

        foreach ($defensePresent as $defense) {
            $tollShare = round(($defense->quantity / $total_fighters) * $toll);

            // TODO: This should go into their iBank account as a transaction
            $defense->owner->increment('credits', $tollShare);
            PlayerLog::writeLog(
                $defense->owner->ship_id,
                LogEnums::TOLL_RECV,
                "$tollShare|$sector"
            );
        }
    }
}
