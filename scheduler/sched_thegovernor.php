<?php declare(strict_types = 1);
/**
 * scheduler/sched_thegovernor.php from The Kabal Invasion.
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

namespace App\Jobs;

use App\Models\Ship;
use Illuminate\Support\Facades\DB;

class GovenorScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 1;
    }

    public function maxCatchup(): int
    {
        return 1;
    }

    /**
     * A lot of the checks that the original `sched_thegovenor.php` was doing are no longer
     * necessary due to updates I have made to the schema. Planet credits for example are
     * now an unsigned int and can't go into negative values.
     *
     * @return void
     */
    protected function run(): void
    {
        // l_sched_gov_title

        // A lot of the checks that the original `sched_thegovenor.php` was doing are no longer
        // necessary due to updates I have made to the schema. Planet credits for example are
        // now an unsigned int and can't go into negative values.

        // l_sched_gov_valid_fits

        // TODO: Only check I think needs to be ported is the one for ships max fighters,
        //       max torps and max armor pts

        /** @var Ship[] $ships */
        $ships = Ship::all();

        DB::beginTransaction();

        foreach ($ships as $ship) {
            $ship_fighters_max = \Tki\CalcLevels::abstractLevels($ship->computer, $tkireg);
            $torps_max = \Tki\CalcLevels::abstractLevels($ship->torp_launchers, $tkireg);
            $armor_pts_max = \Tki\CalcLevels::abstractLevels($ship->armor, $tkireg);

            // Checking Fighters
            if ($ship->ship_fighters > $ship_fighters_max) {
                $ship->ship_fighters = $ship_fighters_max;
            }

            // Checking Torpedoes
            if ($ship->torps > $torps_max) {
                $ship->torps = $torps_max;
            }

            // Checking Armor Points
            if ($ship->armor_pts > $armor_pts_max) {
                $ship->armor_pts = $armor_pts_max;
            }

            // Checking Credits
            if ($ship->credits > 100000000000000000000) {
                $ship->credits = 100000000000000000000;
            }

            $ship->save();
        }

        DB::commit();
    }
}
