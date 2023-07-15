<?php declare(strict_types = 1);
/**
 * classes/Score.php from The Kabal Invasion.
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

namespace Tki\Actions;

use Illuminate\Support\Facades\DB;
use Tki\Models\BankAccount;
use Tki\Models\User;

class Score
{
    public static function updateScore(User $user): int
    {
        // Not currently used in calculation!
        // $base_ore = $tkireg->base_ore;
        // $base_goods = $tkireg->base_goods;
        // $base_organics = $tkireg->base_organics;

        // These are all SQL Queries, so treat them like them.
        $calc_hull              = "ROUND(POW(" . config('game.upgrade_factor') . ", hull))";
        $calc_engines           = "ROUND(POW(" . config('game.upgrade_factor') . ", engines))";
        $calc_power             = "ROUND(POW(" . config('game.upgrade_factor') . ", power))";
        $calc_computer          = "ROUND(POW(" . config('game.upgrade_factor') . ", computer))";
        $calc_sensors           = "ROUND(POW(" . config('game.upgrade_factor') . ", sensors))";
        $calc_beams             = "ROUND(POW(" . config('game.upgrade_factor') . ", beams))";
        $calc_torp_launchers    = "ROUND(POW(" . config('game.upgrade_factor') . ", torp_launchers))";
        $calc_shields           = "ROUND(POW(" . config('game.upgrade_factor') . ", shields))";
        $calc_armor             = "ROUND(POW(" . config('game.upgrade_factor') . ", armor))";
        $calc_cloak             = "ROUND(POW(" . config('game.upgrade_factor') . ", cloak))";
        $calc_levels            = "($calc_hull + $calc_engines + $calc_power + $calc_computer + $calc_sensors + $calc_beams + $calc_torp_launchers + $calc_shields + $calc_armor + $calc_cloak) * " . config('game.upgrade_cost');

        $calc_torps             = "ships.torps * " . config('game.torpedo_price');
        $calc_armor_pts         = "armor_pts * " . config('game.armor_price');
        $calc_ship_ore          = "ship_ore * " . config('game.ore_price');
        $calc_ship_organics     = "ship_organics * " . config('game.organics_price');
        $calc_ship_goods        = "ship_goods * " . config('game.goods_price');
        $calc_ship_energy       = "ship_energy * " . config('game.energy_price');
        $calc_ship_colonists    = "ship_colonists * " . config('game.colonist_price');
        $calc_ship_fighters     = "ship_fighters * " . config('game.fighter_price');
        $calc_equip             = "$calc_torps + $calc_armor_pts + $calc_ship_ore + $calc_ship_organics + $calc_ship_goods + $calc_ship_energy + $calc_ship_colonists + $calc_ship_fighters";

        $calc_dev_warpedit      = "dev_warpedit * " . config('game.dev_warpedit_price');
        $calc_dev_genesis       = "dev_genesis * " . config('game.dev_genesis_price');
        $calc_dev_beacon        = "dev_beacon * " . config('game.dev_beacon_price');
        $calc_dev_emerwarp      = "dev_emerwarp * " . config('game.dev_emerwarp_price');
        $calc_dev_escapepod     = "IF(dev_escapepod=true, " . config('game.dev_escapepod_price') . ", 0)";
        $calc_dev_fuelscoop     = "IF(dev_fuelscoop=true, " . config('game.dev_fuelscoop_price') . ", 0)";
        $calc_dev_lssd          = "IF(dev_lssd=true, " . config('game.dev_lssd_price') . ", 0)";
        $calc_minedeflector     = "dev_minedeflector * " . config('game.dev_minedeflector_price');
        $calc_dev               = "$calc_dev_warpedit + $calc_dev_genesis + $calc_dev_beacon + $calc_dev_emerwarp + $calc_dev_escapepod + $calc_dev_fuelscoop + $calc_minedeflector + $calc_dev_lssd";

        $calc_planet_goods      = "SUM(planets.organics) * " . config('game.organics_price') . "+ SUM(planets.ore) * " . config('game.ore_price') . "+ SUM(planets.goods) * " . config('game.goods_price') . "+ SUM(planets.energy) * " . config('game.energy_price');
        $calc_planet_cols       = "SUM(planets.colonists) * " . config('game.colonist_price');
        $calc_planet_defense    = "SUM(planets.fighters) * " . config('game.fighter_price') . "+ IF(planets.base='Y', " . config('game.base_credits') . "+ SUM(planets.torps) * " . config('game.torpedo_price') . ", 0)";
        $calc_planet_credits    = "SUM(planets.credits)";

        $planet = new \Tki\Types\Structs\Score(DB::table('planets')
            ->selectRaw("IF(COUNT(*)>0, $calc_planet_goods + $calc_planet_cols + $calc_planet_defense + $calc_planet_credits, 0) AS score")
            ->where('owner_id', $user->id)
            ->groupBy('id')
            ->first());

        $ship = new \Tki\Types\Structs\Score(DB::table('ships')
            ->selectRaw("IF(COUNT(*)>0, $calc_levels + $calc_equip + $calc_dev + $user->credits, 0) AS score")
            ->join('planets', 'planets.owner_id', '=', 'ships.owner_id')
            ->where('ships.owner_id', $user->id)
            ->whereNull('destroyed_at')
            ->groupBy('ships.id')
            ->first());

        $score = $ship->score + $planet->score + (new BankAccount)->selectIbankScore($user->id);
        if ($score < 0) $score = 0;
        $score = (int) round(sqrt($score));

        $user->update([
            'score' => $score,
        ]);

        return $score;
    }
}
