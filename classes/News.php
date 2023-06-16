<?php declare(strict_types = 1);
/**
 * classes/News.php from The Kabal Invasion.
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

// FUTURE: Add validity checking for the format of $day

namespace Tki;

/**
 * @todo unsure if needed anymore, delete if not.
 * @deprecated  ???
 */
class News
{
    public static function previousDay(string $day): string
    {
        // Convert the formatted date into a timestamp
        $day = (int) strtotime($day);

        // Subtract one day in seconds from the timestamp
        $day = $day - 86400;

        // Return the final version formatted as YYYY/MM/DD
        $date = date('Y/m/d', $day);
        return $date;
    }

    public static function nextDay(string $day): string
    {
        // Convert the formatted date into a timestamp
        $day = (int) strtotime($day);

        // Add one day in seconds to the timestamp
        $day = $day + 86400;

        // Return the final version formatted as YYYY/MM/DD
        $date = date('Y/m/d', $day);
        return $date;
    }
}
