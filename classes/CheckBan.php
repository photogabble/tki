<?php declare(strict_types = 1);
/**
 * classes/CheckBan.php from The Kabal Invasion.
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

// Returns a Bool false when no account info or no ban found.
// Returns an array which contains the ban information when it has found something.
// Calling code needs to act on the returned information (bool false or array of ban info).

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

// TODO: if kept this is to be moved to app/Helpers although it feels like it should be a method on User

class CheckBan
{
    public static function isBanned(\PDO $pdo_db, array $playerinfo): ?array
    {
        $request = Request::createFromGlobals();

        // Check for IP Ban
        $sql = "SELECT * FROM ::prefix::bans WHERE ban_mask = :ban_mask1 OR ban_mask = :ban_mask2";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ban_mask1', $playerinfo['ip_address'], \PDO::PARAM_STR);
        $stmt->bindParam(':ban_mask2', $playerinfo['ip_address'], \PDO::PARAM_STR);
        $stmt->execute();
        $ipban_count = $stmt->rowCount();
        $ipbans_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($ipban_count > 0)
        {
            // Ok, we have a ban record matching the players current IP Address, so return the BanType.
            return (array) $ipbans_res->fields;
        }

        // Check for ID watch, ban, lock, 24h ban, etc. Linked to the platyers ShipID.
        $sql = "SELECT * FROM ::prefix::bans WHERE ban_ship = :ban_ship";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ban_ship', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $idban_count = $stmt->rowCount();
        $idbans_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($idban_count > 0)
        {
            // Now return the highest ban type (i.e. worst type of ban)
            $ban_type = array('ban_type' => 0);
            while (!$idbans_res->EOF)
            {
                if ($idbans_res->fields['ban_type'] > $ban_type['ban_type'])
                {
                    $ban_type = $idbans_res->fields;
                }

                $idbans_res->MoveNext();
            }

            return (array) $ban_type;
        }

        // Check for multi-ban (IP, ID)
        $remote_ip = $request->server->get('REMOTE_ADDR');
        $sql = "SELECT * FROM ::prefix::bans WHERE " .
               "ban_mask = :ban_mask1 OR ban_mask = :ban_mask2 OR ban_ship = :ban_ship";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ban_mask1', $playerinfo['ip_address'], \PDO::PARAM_STR);
        $stmt->bindParam(':ban_mask2', $remote_ip, \PDO::PARAM_STR);
        $stmt->bindParam(':ban_ship', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $multiban_count = $stmt->rowCount();
        $multiban_res = $stmt->fetch();
        Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        if ($multiban_count > 0)
        {
            // Ok, we have a ban record matching the players current IP Address or their ShipID, so return the BanType.
            return (array) $multiban_res->fields;
        }

        // Well we got here, and we haven't found any bans, so we return null.
        return null;
    }
}
