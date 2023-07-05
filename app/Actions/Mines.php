<?php declare(strict_types = 1);
/**
 * Actions/Mines.php from The Kabal Invasion.
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

class Mines
{
    public static function explode(int $sector, int $num_mines): void
    {
        $defenses = SectorDefense::mines($sector);
        if ($defenses->count() === 0 || $num_mines <= 0) return;

        foreach ($defenses as $defense) {
            if ($defense->quantity >= $num_mines) {
                $defense->update(['quantity' => min(0, $defense->quantity - $num_mines)]);
                return;
            } else {
                $num_mines -= $defense->quantity;
                $defense->delete();
            }
        }
    }
}
