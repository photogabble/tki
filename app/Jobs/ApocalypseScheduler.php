<?php declare(strict_types = 1);
/**
 * scheduler/sched_apocalypse.php from The Kabal Invasion.
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

use App\Models\Planet;
use Exception;

class ApocalypseScheduler extends ScheduledTask
{

    /**
     * If there are more than 200 planets in the universe that have a population greater than the set
     * doomsday value then pick one of those planets to have a terrible day.
     *
     * @todo Create news item for each apocalypse
     * @todo Add more dreadful things
     * @throws Exception
     */
    public function run(): void
    {
        // Log: l_sched_apoc_title
        // Log: l_sched_apoc_begins ???

        $potentiallyDoomed = Planet::query()
            ->where('colonists', '>', config('scheduler.doomsday_value'))
            ->get();

        if ($potentiallyDoomed->count() === 0) return; // Nobody dies

        $chance = 9;

        // Increase the chance it will happen if we have lots of planets meeting the criteria
        // TODO make this a percentage of total planets
        if ($potentiallyDoomed->count() > 200) $chance = 7;

        // Chance something bad will happen
        $affliction = random_int(1, $chance);

        // Get a doomed planet at random and ruin their day, return on null
        if (!$actuallyDoomed = $potentiallyDoomed->random(1)->first()) return;

        if ($affliction < 3) {
            if ($affliction === 1) {
                // Space Plague
                $actuallyDoomed->colonists = floor($actuallyDoomed->colonists - $actuallyDoomed->colonists * config('game.space_plague_kills'));
            } else {
                // Plasma storm
                $actuallyDoomed->energy = 0;
            }
            $actuallyDoomed->save();
        }
    }

    public function periodMinutes(): int
    {
        return 15;
    }
}
