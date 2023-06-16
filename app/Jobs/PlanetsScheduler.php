<?php declare(strict_types = 1);
/**
 * scheduler/sched_planets.php from The Kabal Invasion.
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

use Illuminate\Support\Facades\DB;
use Tki\Models\Planet;

class PlanetsScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 2;
    }

    protected function run(): void
    {
        /** @var Planet[] $ownedPlanets */
        $ownedPlanets = Planet::query()
            ->whereNotNull('owner_id')
            ->get();

        DB::beginTransaction();

        foreach($ownedPlanets as $planet) {
            $production = floor(min($planet->colonists, config('game.colonist_limit')) * config('game.colonist_production_rate'));
            $organics_production = floor($production * config('game.organics_prate') * $planet->prod_organics / 100.0);
            $organics_production -= floor($production * config('game.organics_consumption'));

            $starvation = 0;

            if ($planet->organics + $organics_production < 0) {
                $organics_production = -$planet->organics;
                $starvation = floor($planet->colonists * config('game.starvation_death_rate'));

                if (!is_null($planet->owner_id)) {
                    \Tki\Models\PlayerLog::writeLog($planet->owner_id, \Tki\Types\LogEnums::STARVATION, "$planet->sector_id|$starvation");
                }
            }

            $ore_production = floor($production * config('game.ore_prate') * $planet->prod_ore / 100.0);
            $goods_production = floor($production * config('game.goods_prate') * $planet->prod_goods / 100.0);
            $energy_production = floor($production * config('game.energy_prate') * $planet->prod_energy / 100.0);
            $reproduction = floor(($planet->colonists - $starvation) * config('game.colonist_reproduction_rate'));

            if (($planet->colonists + $reproduction - $starvation) > config('game.colonist_limit')) {
                $reproduction = config('game.colonist_limit' - $planet->colonists);
            }

            $total_percent = $planet->prod_organics + $planet->prod_ore + $planet->prod_goods + $planet->prod_energy;

            $fighter_production = 0;
            $torp_production = 0;

            if (!is_null($planet->owner_id)) {
                $fighter_production = floor($production * config('game.fighter_prate') * $planet->prod_fighters / 100.0);
                $torp_production = floor($production * config('game.torpedo_prate') * $planet->prod_torp / 100.0);
                $total_percent += $planet->prod_fighters + $planet->prod_torp;
            }

            $credits_production = floor($production * config('game.credits_prate') * (100.0 - $total_percent) / 100.0);

            $planet->update([
                'organics' => $planet->organics + $organics_production,
                'ore' => $planet->ore + $ore_production,
                'goods' => $planet->goods + $goods_production,
                'energy' => $planet->energy + $energy_production,
                'colonists' => $planet->colonists + $reproduction - $starvation,
                'torps' => $planet->torps + $torp_production,
                'fighters' => $planet->fighters + $fighter_production,
                'credits' => $planet->credits * config('game.interest_rate') + $credits_production,
            ]);
        }

        DB::commit();

        // Limit max credits on Planets without a base
        if (config('game.sched_planet_valid_credits')) {
            Planet::query()
                ->where('credits', '>', config('game.max_credits_without_base'))
                ->where('base', false)
                ->update([
                    'credits' => config('game.max_credits_without_base')
                ]);
        }

        // l_sched_planets_updated
    }
}
