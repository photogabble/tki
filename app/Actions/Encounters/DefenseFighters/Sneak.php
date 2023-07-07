<?php declare(strict_types=1);
/**
 * Actions/Encounters/DefenseFighters/Sneak.php from The Kabal Invasion.
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
 * ---
 *
 * This class is the refactored result of CheckDefenses::fighters
 *
 */

namespace Tki\Actions\Encounters\DefenseFighters;

use Tki\Actions\CheckDefenses;
use Tki\Actions\Encounters\EncounterOption;
use Tki\Helpers\Scan;
use Tki\Models\SectorDefense;

final class Sneak extends EncounterOption
{
    public function execute(array $payload): bool
    {
        $sector = $this->user->ship->sector_id;
        $fighters = SectorDefense::fighters($sector);

        // The legacy code would use the sensors value from the owner of the first fighters
        // record to be returned. Because more than one member of the same team can deploy
        // fighters into an area I feel that taking an average sensors value of all players
        // to have deployed fighters is a much more fair way of weighting things.
        $sensorsAvg = $fighters->reduce(function(array $carry, SectorDefense $defense){
            $carry['count']++;

            if (!is_null($defense->owner->ship)) {
                $carry['total']+=$defense->owner->ship->sensors;
            }

            return $carry;
        },['count' => 0, 'total' => 0]);

        $sensors = $sensorsAvg['total'] / $sensorsAvg['count'];
        $success = Scan::success($sensors, $this->user->ship->cloak);

        if ($success < 5) $success = 5;
        if ($success > 95) $success = 95;

        $roll = random_int(1, 100);

        if ($roll < $success) {
            // Sector defenses detect incoming ship
            $this->encounter->persistData([
                'messages' => [
                    __('check_defenses.l_chf_thefightersdetectyou'),
                ],
            ]);

            return CheckDefenses::fighters($this->encounter->movement, $this->user);
        }

        // Sector defenses don't detect incoming ship

        return false;
    }

    public function can(): bool
    {
        return true; // TODO: This should return false if the player hasn't cloaking technology
    }
}