<?php declare(strict_types = 1);
/**
 * scheduler/sched_tow.php from The Kabal Invasion.
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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Tki\Models\Ship;
use Tki\Models\Universe;
use Tki\Models\Zone;

class TowScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 2;
    }

    public function maxCatchup(): int
    {
        return 1;
    }

    protected function run(): void
    {
        Log::info(__('scheduler.l_sched_tow_title'));
        Log::info(__('scheduler.l_sched_tow_note'));

        /** @var Ship[]|Collection $ships */
        $ships = Ship::query()
            ->join(Universe::class, 'ships.sector_id', '=', 'universes.id')
            ->join(Zone::class, 'universe.zone_id', '=', 'zones.id')
            ->where('max_hull', '<>', 0)
            ->whereNull('destroyed_at')
            ->whereRaw('((ships.hull + ships.engines + ships.computer + ships.beams + ships.torp_launchers + ships.shields + ships.armor) /7) > max_hull')
            ->select(['ships.id', 'character_name', 'hull', 'sector_id', 'universe.zone_id', 'max_hull'])
            ->get();

        if ($ships->count() === 0) {
            Log::info(__('l_sched_tow_none'));
            return;
        }

        Log::info(__('scheduler.l_sched_tow_number', ['number' => $ships->count()]));

        foreach ($ships as $ship) {
            Log::info(__('scheduler.l_sched_tow_who', ['character' => $ship->character_name, 'sector' => $ship->sector_id]));

            $oldsector = $ship->sector_id;
            $newsector = random_int(0, config('game.max_sectors') - 1);
            Log::info(__('scheduler.l_sched_tow_where', ['sector' => $newsector]));

            $ship->update([
                'sector_id' => $newsector,
                'cleared_defenses' => ' '
            ]);

            \Tki\Models\PlayerLog::writeLog($ship->id, \Tki\Types\LogEnums::TOW, "$oldsector|$newsector|$ship->max_hull");
            \Tki\Models\MovementLog::writeLog($ship->id, $newsector);
        }

        Log::info(__('scheduler.l_sched_tow_none'));
    }
}