<?php declare(strict_types = 1);
/**
 * classes/Logs/LogsGateway.php from The Kabal Invasion.
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

namespace Tki\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PlayerLog extends Model
{
    /**
     * @todo refactor usage to be Collection aware
     * @param int $ship_id
     * @param string $startdate
     * @return Collection
     */
    public function selectLogsInfo(int $ship_id, string $startdate): Collection
    {
        return PlayerLog::where('ship_id', $ship_id)
            ->where('created_at', 'LIKE', "$startdate%")
            ->get();
    }

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
