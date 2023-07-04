<?php declare(strict_types=1);
/**
 * Actions/Encounters/DefenseFighters/Fight.php from The Kabal Invasion.
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

use Tki\Actions\Bounty;
use Tki\Actions\Encounters\EncounterOption;
use Tki\Actions\Fighters;
use Tki\Helpers\CalcLevels;
use Tki\Models\PlayerLog;
use Tki\Models\SectorDefense;
use Tki\Types\LogEnums;

final class Fight extends EncounterOption
{
    /**
     * This method is the refactored result of CheckDefenses::sectorFighters
     * from the legacy codebase.
     *
     * @todo replace with a turn based system (automatic?) giving more detail on the resulting battle
     * @return bool
     */
    public function execute(): bool
    {
        $targetFighters = SectorDefense::fightersCount($this->encounter->sector_id);
        if ($targetFighters === 0) return true;
        $totalFighters = $targetFighters;

        $encounterData = [
            'messages' => [],
        ];

        $sector = $this->encounter->sector_id;

        $ship = $this->user->ship;
        $encounterData['messages'][] = __('sector_fighters.l_sf_attacking');

        $playerBeams = CalcLevels::abstractLevels($ship->beams);
        $playerEnergy = $ship->ship_energy;

        // TODO: Is there a bug here where if there isn't enough energy for the beams
        //       then some of the beams should be disabled?
        $playerEnergy -= $playerBeams;
        $playerEnergy = min(0, $playerEnergy);

        $playerTorpCount = round(pow(config('game.level_factor'), $ship->torp_launchers)) * 2;
        if ($playerTorpCount > $ship->torps) {
            $playerTorpCount = $ship->torps;
        }

        $playerTorpDmg = config('game.torp_dmg_rate') * $playerTorpCount;
        $playerArmor = $ship->armor_pts;
        $playerFighters = $ship->ship_fighters;

        // If available attack first with Beams
        if ($playerBeams > 0) {
            $hit = round($targetFighters / 2);
            if ($playerBeams > $hit) {
                $lost = $targetFighters - $hit;
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfight', ['qty' => $lost]);
                $targetFighters = $hit;
                $playerBeams -= $lost;
            } else {
                $targetFighters -= $playerBeams;
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfight', ['qty' => $playerBeams]);
                $playerBeams = 0;
            }
        }


        // If available attack with Torpedoes
        if ($targetFighters > 0 && $playerTorpDmg > 0) {
            // TODO: there is a bug here where torps used aren't deducted...
            $encounterData['messages'][] = __('sector_fighters.l_sf_torphit');
            $hit = round($targetFighters / 2);
            if ($playerTorpDmg > $hit) {
                $lost = $targetFighters - $hit;
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfightt', ['qty' => $lost]);
                $targetFighters = $hit;
            } else {
                $targetFighters -= $playerTorpDmg;
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfightt', ['qty' => $playerTorpDmg]);
            }
        }

        // If available attack with Fighters
        if ($targetFighters > 0 && $playerFighters > 0) {
            $encounterData['messages'][] = __('sector_fighters.l_sf_fighthit');

            if ($playerFighters > $targetFighters) {
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfightall');
                $temptArgFighters = 0;
            } else {
                $encounterData['messages'][] = __('sector_fighters.l_sf_destfightt2', ['qty' => $playerFighters]);
                $temptArgFighters = $targetFighters - $playerFighters;
            }

            if ($targetFighters > $playerFighters) {
                $encounterData['messages'][] = __('sector_fighters.l_sf_lostfight');
                $tempPlayFighters = 0;
            } else {
                $encounterData['messages'][] = __('sector_fighters.l_sf_lostfight2', ['qty' => $targetFighters]);
                $tempPlayFighters = $playerFighters - $targetFighters;
            }

            $playerFighters = $tempPlayFighters;
            $targetFighters = $temptArgFighters;
        }

        // TODO: There seems some disparity here between Mines and Fighters with Mines going through
        //       shields before armor.

        if ($targetFighters > 0) {
            if ($targetFighters > $playerArmor) {
                $playerArmor = 0;
                $encounterData['messages'][] = __('sector_fighters.l_sf_armorbreach');
            } else {
                $playerArmor -= $targetFighters;
                $encounterData['messages'][] = __('sector_fighters.l_sf_armorbreach2', ['qty' => $targetFighters]);
            }
        }

        $fightersLost = $totalFighters - $targetFighters;
        Fighters::destroy($this->encounter->sector_id, $fightersLost);

        \Tki\Actions\SectorDefense::messageDefenseOwner(
            SectorDefense::fighters($this->encounter->sector_id),
            __('sector_fighters.l_sf_sendlog',[
                'player' => $this->user->name,
                'qty' => $fightersLost,
                'sector' => $this->encounter->sector_id
            ])
        );

        PlayerLog::writeLog($this->user->id, LogEnums::DEFS_DESTROYED_F, "$fightersLost|$sector");


        // BOOM, players ship is no more.
        if ($playerArmor < 0) {
            $encounterData['messages'][] = __('sector_fighters.l_sf_shipdestroyed');

            $pod = $ship->dev_escapepod ? 'Y' : 'N';

            \Tki\Actions\SectorDefense::messageDefenseOwner(
                SectorDefense::fighters($this->encounter->sector_id),
                __('sector_fighters.l_sf_sendlog2',[
                    'player' => $this->user->name,
                    'sector' => $this->encounter->sector_id
                ])
            );

            PlayerLog::writeLog($this->user->id, LogEnums::DEFS_KABOOM, "$sector|$pod");

            if ($ship->dev_escapepod){
                $encounterData['messages'][] = __('sector_fighters.l_sf_escape');
            } else {
                // TODO killed messages
            }

            $ship->setDestroyed();
            $this->encounter->persistData($encounterData);
            return true;
        }

        // Survives

        $armorLost = $ship->armor_pts - $playerArmor;
        $fightersLost = $ship->ship_fighters - $playerFighters;

        $ship->ship_fighters = max(0, $ship->ship_fighters - $fightersLost);
        $ship->ship_energy = max(0, $playerEnergy);
        $ship->armor_pts = max(0, $ship->armor_pts - $armorLost);
        $ship->torps = max(0, $ship->torps - $playerTorpCount);
        $ship->save();

        // TODO: need to actually calc torps used

        $encounterData['messages'][] = __('sector_fighters.l_sf_lreport',[
            'armor' => $armorLost,
            'torps' => $playerTorpCount,
            'fighters' => $fightersLost,
        ]);

        $this->encounter->persistData($encounterData);
        return true;
    }
}