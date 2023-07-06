<?php declare(strict_types=1);
/**
 * app/Types/EventType.php from The Kabal Invasion.
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
 * Certain player actions have a likelihood of resulting in Encounters, for example
 * both Warping or RealSpace moving into a sector can result in both a DefenseFighters
 * and DefenseMines encounter.
 *
 * Having a ship that's too big for a sector will result in a Tow encounter if the
 * player stays in the sector too long. Hostile encounters can be both PvP and PvE
 * related. All the aforementioned can result in the Death encounter.
 *
 * Quest and TravellingTrader encounters are both benign, the Player can simply
 * dismiss them although both if accepted can prove to be most useful.
 *
 * Encounters can be stacked, for example logging in to discover a Hostile encounter
 * can result in it being dismissed only to then be presented with the Death encounter.
 *
 * The player could also, for example; enter a sector and encounter DefenseFighters,
 * pay their ransom only to then be presented with the TravellingTrader or Quest
 * encounter.
 *
 * All encounters block player actions until the encounter stack is emptied.
 *
 */

namespace Tki\Types;

use Tki\Actions\Encounters\EncounterFactory;
use Tki\Actions\Encounters\Navigation;
use Tki\Models\Encounter;

enum EncounterType : string {
    case Navigation = 'N';
    case TravellingTrader = 'TT'; // Offers goods for sale
    case Quest = 'Q'; // Has a quest for the player to complete for a reward
    case Hostile = 'H'; // PvE, PvP, etc. This just shows the log of the battle.
    case DefenseFighters = 'DF';  // Options: retreat, pay, fight or cloak
    case DefenseMines = 'DM'; // Kaboom
    case Tow = 'T'; // You have been towed out of sector, maybe there was a fine?
    case Death = 'D'; // Oh no...

    public function class(Encounter $model) : ?EncounterFactory
    {
        $class = match ($this) {
            EncounterType::Navigation => Navigation::class,
            default => null,
        };

        if (!$class) return null;

        return new $class($model);
    }
}