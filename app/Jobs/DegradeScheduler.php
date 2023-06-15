<?php declare(strict_types = 1);
/**
 * scheduler/sched_degrade.php from The Kabal Invasion.
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

use Tki\Models\Planet;
use Tki\Models\SectorDefense;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

class DegradeScheduler extends ScheduledTask
{
    /**
     * Degrades deployed defense if it has no supporting planets, otherwise it consumes
     * the required energy from supporting planets.
     *
     * @return void
     */
    protected function run(): void
    {
        // l_sched_degrade_title

        /** @var SectorDefense[] $found */
        $found = SectorDefense::query()
            ->with('ship')
            ->where('defense_type', 'F')
            ->get();

        foreach ($found as $sectorDefense) {
            // Get planets within the sector that are owned by the player who deployed the defense
            // or the team they belong to.

            /** @var Planet[]|Collection $planets */
            $planets = Planet::query()
                ->where('sector_id', $sectorDefense->sector_id)
                ->where('energy', '>', 0)
                ->where(function(Builder $builder) use ($sectorDefense){
                    // TODO: check this is equivilant to (owner = ? OR (team_id = ? AND ? <> 0))
                    $builder->where('owner', $sectorDefense->ship->id);
                    if ($sectorDefense->ship->team_id) {
                        $builder->orWhere('team_id', $sectorDefense->ship->team_id);
                    }
                })->get();

            if ($planets->count() === 0) {
                // Degrade the defense if quantity > 0
                if ($sectorDefense->quantity > 0) {
                    $degradeAmount = max($sectorDefense->quantity * config('game.defense_degrade_rate'), 1);
                    $sectorDefense->decrement('quantity', $degradeAmount);

                    \Tki\PlayerLog::writeLog($sectorDefense->ship_id, \Tki\LogEnums::DEFENSE_DEGRADE, $sectorDefense->sector_id . '|' . $degradeAmount);
                }
                continue;
            }

            $energyRequired = round($sectorDefense->quantity * config('game.energy_per_fighter'));
            $energyAvailable = $planets->reduce(function(int $available, Planet $planet) {
                $available += $planet->energy;
                return $available;
            }, 0);

            if ($energyAvailable >= $energyRequired) {
                foreach ($planets as $planet) {
                    // Consume energy from supporting planets
                    $planet->update([
                        'energy' => $planet->energy - max(round($energyRequired * ($planet->energy / $energyAvailable)), 1)
                    ]);
                }

                continue;
            }

            // Not enough energy to operate, degrade defenses
            $degradeAmount = max($sectorDefense->quantity * config('game.defense_degrade_rate'), 1);
            $sectorDefense->decrement('quantity', $degradeAmount);

            \Tki\PlayerLog::writeLog($sectorDefense->ship_id, \Tki\LogEnums::DEFENSE_DEGRADE, $sectorDefense->sector_id . '|' . $degradeAmount);
        }

        // Clean up any expired defense
        SectorDefense::query()
            ->where('quantity', '<=', 0)
            ->delete();
    }

    public function periodMinutes(): int
    {
        return 5;
    }
}
