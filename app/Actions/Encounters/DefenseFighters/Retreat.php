<?php declare(strict_types=1);
/**
 * Actions/Encounters/DefenseFighters/Retreat.php from The Kabal Invasion.
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

use Tki\Actions\Encounters\EncounterOption;

final class Retreat extends EncounterOption
{
    public function execute(): bool
    {
        $this->encounter->persistData([
            'messages' => [
                __('check_defenses.l_chf_youretreatback'),
            ],
        ]);

        $this->user->spendTurns(2);
        $this->user->ship->moveTo($this->encounter->movement->previous_id, $this->encounter->movement->mode);
        return true;
    }
}