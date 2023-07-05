<?php declare(strict_types = 1);
/**
 * Actions/CheckDefenses.php from The Kabal Invasion.
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

use Tki\Models\SectorDefense;
use Tki\Types\EncounterType;
use Tki\Models\MovementLog;
use Tki\Models\PlayerLog;
use Tki\Models\Encounter;
use Tki\Models\User;
use Tki\Actions;
use Tki\Helpers;
use Tki\Types;

class CheckDefenses
{
    /**
     * @deprecated use Fight
     */
    public static function sectorFighters(): void {
        throw new \Exception('Deprecated');
    }

    /**
     * Determine if there is a DefenseFighters encounter.
     *
     * I have refactored the majority of this methods functionality into EncounterActivity
     * classes. These can be found in Actions\Encounters\DefenseFighters.
     *
     * @param MovementLog $movement
     * @param User $player
     * @return bool
     */
    public static function fighters(MovementLog $movement, User $player): bool
    {
        $total_sec_fighters = 0;

        // Get sector defense info from database
        $defenses = SectorDefense::fighters($movement->sector_id);

        foreach ($defenses as $defense){
            // Can't be attacked if portion of defense was deployed by player
            if ($defense->owner_id === $player->id) return false;

            // Can't be attacked if defense is deployed by someone on the same team
            // All sector defenses must be owned by members of the same team.
            if ($defense->owner->team_id === $player->team_id && !is_null($player->team_id)) return false;

            $total_sec_fighters += $defense->quantity;
        }

        if ($total_sec_fighters > 0) {
            // Create Encounter with options for player to choose what happens next
            $movement->encounter()->save(new Encounter([
                'user_id' => $player->id,
                'sector_id' => $movement->sector_id,
                'type' => EncounterType::DefenseFighters,
            ]));
            return true;
        }

        // Clean up any sectors that have used up all mines or fighters
        SectorDefense::query()
            ->where('quantity', '<=', '0')
            ->delete();

        return false;
    }

    /**
     * Determine if there is a DefenseMines encounter.
     *
     * @param MovementLog $movement
     * @param User $player
     * @return bool
     * @throws \Exception
     */
    public static function mines(MovementLog $movement, User $player): bool
    {
        // Compute the ship average. If it's too low then the ship will not hit mines.
        $shipAvg = Helpers\CalcLevels::avgTech($player->ship->toArray());
        if ($shipAvg < config('game.mine_hullsize')) return false;

        // Get sector defense info from database
        $defenses = SectorDefense::mines($movement->sector_id);

        $total_sector_mines = 0;

        foreach ($defenses as $defense){
            // Can't be attacked if portion of defense was deployed by player
            if ($defense->owner_id === $player->id) return false;

            // Can't be attacked if defense is deployed by someone on the same team
            // All sector defenses must be owned by members of the same team.
            if ($defense->owner->team_id === $player->team_id && !is_null($player->team_id)) return false;

            $total_sector_mines += $defense->quantity;
        }

        // The mines will attack if 4 conditions are met
        //    1) There is at least 1 group of mines in the sector
        //    2) There is at least 1 mine in the sector
        //    3) You are not the owner or on the team of the owner - team null doesn't count
        //    4) You ship is at least mine_hullsize (settable in config/game.php) big

        if ($total_sector_mines === 0) return false;

        /**
         * Player hit mines, create encounter and calculate log entries
         * @var Encounter $encounter
         */
        $encounter = $movement->encounter()->save(new Encounter([
            'user_id' => $player->id,
            'sector_id' => $movement->sector_id,
            'type' => EncounterType::DefenseMines,
        ]));

        // Choose % of mines that attack, always at least 5% of the deployed mines or at the very least 1 mine.
        // Very unlucky players will be hit by all mines.
        $pren = (random_int(5, 100) / 100);
        $roll = (int) round($pren * $total_sector_mines - 1) + 1;

        $encounterData = [
            'attacking_mines' => $roll,
            'messages' => [],
        ];

        // You are hit. Tell the player and put it in the log
        $encounterData['messages'][] = __('check_defenses.l_chm_youhitsomemines', ['qty' => $roll]);
        PlayerLog::writeLog($player->id, Types\LogEnums::HIT_MINES, "$roll|$movement->sector_id");

        // Tell the owner that their mines were hit
        Actions\SectorDefense::messageDefenseOwner($defenses, __('check_defenses.l_chm_hehitminesinsector', [
            'player_character_name' => $player->name,
            'qty' => $roll,
            'sector' => $movement->sector_id
        ]));

        $ship = $player->ship;

        // If the player has enough mine deflectors then subtract the amount and return
        if ($ship->dev_minedeflector >= $roll) {
            $encounterData['messages'][] = __('check_defenses.l_chm_youlostminedeflectors', ['qty' => $roll]);

            $ship->decrement('dev_minedeflector', $roll);
            $encounter->persistData($encounterData);
            Actions\Mines::explode($movement->sector_id, $roll);

            return true;
        }

        if ($ship->dev_minedeflector > 0) {
            $encounterData['messages'][] = __('check_defenses.l_chm_youlostallminedeflectors');
        } else {
            $encounterData['messages'][] = __('check_defenses.l_chm_youhadnominedeflectors');
        }

        // Mine Deflectors reduce the attack qty
        $mines_left = $roll - $ship->dev_minedeflector;

        // Ship either had no mine deflectors installed, or had fewer than needed to deflect attack.
        $ship->dev_minedeflector = 0;

        // Shields up
        $playershields = Helpers\CalcLevels::abstractLevels($ship->shields);
        if ($playershields > $ship->ship_energy) {
            $playershields = $ship->ship_energy;
        }

        // Shields have absorbed the attack. Subtract energy used by shields and return.
        if ($playershields >= $mines_left) {
            $encounterData['messages'][] = __('check_defenses.l_chm_yourshieldshitforminesdmg', ['qty'=>$mines_left]);

            $ship->ship_energy = max(0, $ship->ship_energy - $mines_left);
            $ship->save();

            if ($playershields == $mines_left) {
                $encounterData['messages'][] = __('check_defenses.l_chm_yourshieldsaredown');
            }

            $encounter->persistData($encounterData);
            Actions\Mines::explode($movement->sector_id, $roll);

            return true;
        }

        // When shields fail, the energy reserve is wiped out
        $ship->ship_energy = 0;

        // Direct hit, down to armor
        $encounterData['messages'][] = __('check_defenses.l_chm_youlostallyourshields');
        $mines_left = $mines_left - $playershields;

        if ($ship->armor_pts >= $mines_left) {
            $encounterData['messages'][] = __('check_defenses.l_chm_yourarmorhitforminesdmg', ['qty' => $mines_left]);
            $encounterData['messages'][] = __('check_defenses.l_chm_yourhullisbreached');
            $ship->armor_pts = max(0, $ship->armor_pts - $mines_left);

            $ship->save();
            $encounter->persistData($encounterData);
            Actions\Mines::explode($movement->sector_id, $roll);

            return true;
        }

        // BOOM, players ship is no more.

        $pod = $ship->dev_escapepod ? 'Y' : 'N';
        PlayerLog::writeLog($player->id, Types\LogEnums::SHIP_DESTROYED_MINES, "$movement->sector_id|$pod");

        Actions\SectorDefense::messageDefenseOwner(
            $defenses,
            __('check_defenses.l_chm_hewasdestroyedbyyourmines', [
                'player_character_name' => $player->name,
                'sector' => $movement->sector_id
            ])
        );

        $encounterData['messages'][] = __('check_defenses.l_chm_yourshiphasbeendestroyed');

        // Survival
        if ($ship->dev_escapepod) {
            $encounterData['messages'][] = __('check_defenses.l_chm_luckescapepod');
            // Listener on the ShipDestroyed event will place them into escape pod if ship had one
        } else {
            // Or they lose!
            // In BNT this would act to remove all the players activity from the game that is
            // all bounties placed, all planets owned, etc. Trade Wars logs the player out
            // and bans them for a day. I like Eve Online's approach, where the player is
            // reincarnated via clone but loose XP.

            // TODO $encounterData['messages']
        }

        // Destroyed method also saves the changes made to Ship Model. In doing so a ShipDestroyed event is
        // dispatched and that gets picked up for placing the player into their escape pod,
        // or reincarnating them.
        $ship->setDestroyed();

        $ship->save();
        $encounter->persistData($encounterData);
        Actions\Mines::explode($movement->sector_id, $roll);

        return false;
    }
}
