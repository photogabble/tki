<?php declare(strict_types = 1);
/**
 * scheduler/sched_ports.php from The Kabal Invasion.
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

use Tki\Models\Universe;
use Illuminate\Support\Facades\Log;

class PortsScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 1;
    }

    protected function run(): void
    {
        Log::info(__('scheduler.l_sched_ports_title'));

        //
        // Regen Ore
        //

        // Regenerate Ore in Commodities Ports
        Log::info(__('scheduler.l_sched_ports_addore'));
        Universe::query()
            ->where('port_type', 'ore')
            ->where('port_ore', '<', config('game.ore_limit'))
            ->increment(
                'port_ore',
                config('game.ore_rate') * $this->multiplier * config('game.port_regenrate')
            );

        // Regenerate Ore in all Ore ports
        // Note: Ore ports regen twice
        Log::info(__('scheduler.l_sched_ports_addore_ore'));
        Universe::query()
            ->where('port_type', '!=', 'special')
            ->where('port_type', '!=', 'none')
            ->where('port_ore', '<', config('game.ore_limit'))
            ->increment(
                'port_ore',
                config('game.ore_rate') * $this->multiplier * config('game.port_regenrate')
            );

        //
        // Regen Organics
        //

        // Regenerate Organics in all commodities ports
        Log::info(__('scheduler.l_sched_ports_addorg'));
        Universe::query()
            ->where('port_type', 'organics')
            ->where('port_organics', '<', config('game.organics_limit'))
            ->increment(
                'port_organics',
                config('game.organics_rate') * $this->multiplier * config('game.port_regenrate')
            );

        // Regenerate Organics in all organics ports
        Log::info(__('scheduler.l_sched_ports_addorg_org'));
        Universe::query()
            ->where('port_type', '!=', 'special')
            ->where('port_type', '!=', 'none')
            ->where('port_organics', '<', config('game.organics_limit'))
            ->increment(
                'port_organics',
                config('game.organics_rate') * $this->multiplier * config('game.port_regenrate')
            );

        //
        // Regen Goods
        //

        // Regenerate Goods in all commodities ports
        Log::info(__('scheduler.l_sched_ports_addgoods'));
        Universe::query()
            ->where('port_type', 'goods')
            ->where('port_goods', '<', config('game.goods_limit'))
            ->increment(
                'port_goods',
                config('game.goods_rate') * $this->multiplier * config('game.port_regenrate')
            );

        // Regenerate Goods in all goods ports
        Log::info(__('scheduler.l_sched_ports_addgoods_goods'));
        Universe::query()
            ->where('port_type', '!=', 'special')
            ->where('port_type', '!=', 'none')
            ->where('port_goods', '<', config('game.goods_limit'))
            ->increment(
                'port_goods',
                config('game.goods_rate') * $this->multiplier * config('game.port_regenrate')
            );

        //
        // Regen Energy
        //

        // Regenerate Energy in all commodities ports
        Log::info(__('scheduler.l_sched_ports_addenergy'));
        Universe::query()
            ->where('port_type', 'energy')
            ->where('port_energy', '<', config('game.energy_limit'))
            ->increment(
                'port_energy',
                config('game.energy_rate') * $this->multiplier * config('game.port_regenrate')
            );

        // Regenerate Energy in all Energy ports
        Log::info(__('scheduler.l_sched_ports_addenergy_energy'));
        Universe::query()
            ->where('port_type', '!=', 'special')
            ->where('port_type', '!=', 'none')
            ->where('port_energy', '<', config('game.energy_limit'))
            ->increment(
                'port_energy',
                config('game.energy_rate') * $this->multiplier * config('game.port_regenrate')
            );

        // Now check to see if any ports are over max, if so correct them.

        Log::info(__('scheduler.l_sched_ports_energy_cap'));
        Universe::query()
            ->where('port_energy', '>', config('game.energy_limit'))
            ->update(['port_energy' => config('game.energy_limit')]);

        Log::info(__('scheduler.l_sched_ports_goods_cap'));
        Universe::query()
            ->where('port_goods', '>', config('game.goods_limit'))
            ->update(['port_energy' => config('game.goods_limit')]);

        Log::info(__('scheduler.l_sched_ports_organics_cap'));
        Universe::query()
            ->where('port_organics', '>', config('game.organics_limit'))
            ->update(['port_energy' => config('game.organics_limit')]);

        Log::info(__('scheduler.l_sched_ports_ore_cap'));
        Universe::query()
            ->where('port_ore', '>', config('game.ore_limit'))
            ->update(['port_energy' => config('game.ore_limit')]);
    }
}
