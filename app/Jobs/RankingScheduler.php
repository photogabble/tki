<?php declare(strict_types = 1);
/**
 * scheduler/sched_ranking.php from The Kabal Invasion.
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

namespace Tki\Jobs;

use Tki\Models\Ship;

// FUTURE: Use a single FetchAll to grab all users at once, process as an array, and switch to PDO, improve debug/output handling

class RankingScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 30;
    }

    public function maxCatchup(): int
    {
        return 1;
    }

    protected function run(): void
    {
        /** @var Ship[] $ships */
        $ships = Ship::query()
            ->where('ship_destroyed', false)
            ->get();

        foreach ($ships as $ship) {
            // TODO Implement Tki\Score::updateScore
            // Tki\Score::updateScore($pdo_db, $res->fields['ship_id'], $tkireg, $playerinfo);
        }
    }
}
