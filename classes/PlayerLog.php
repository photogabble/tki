<?php declare(strict_types = 1);
/**
 * classes/PlayerLog.php from The Kabal Invasion.
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

// TODO change namespace to App\Models
// TODO Move file to app\Models\Log.php (delete Log.php and move this there)
namespace Tki;

use Illuminate\Database\Eloquent\Model;

class PlayerLog extends Model
{
    public static function writeLog(int $ship_id, int $log_type, ?string $data = null): void
    {
        if (!is_null($data)) $data = addslashes($data);

        // Write log_entry to the player's log - identified by player's ship_id.
        static::query()
            ->create([
                'ship_id' => $ship_id,
                'type' => $log_type,
                'data' => $data
            ]);
    }
}
