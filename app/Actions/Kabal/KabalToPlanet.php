<?php declare(strict_types = 1);
/**
 * classes/KabalToPlanet.php from The Kabal Invasion.
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

namespace Tki\Actions\Kabal;

use Tki\Actions;
use Tki\Actions\Character;
use Tki\Helpers;
use Tki\Registry;
use Tki\Types\LogEnums;

class KabalToPlanet
{
    public static function planet(\PDO $pdo_db, string $lang, int $planet_id, Registry $tkireg, array $playerinfo): void
    {
        // Get planetinfo from database
        $planets_gateway = new \Tki\Models\Planet($pdo_db);
        $planetinfo = $planets_gateway->selectPlanetInfoByPlanet($planet_id);
        if (empty($planetinfo))
        {
            die("No valid planet info in KabalToPlanet initial DB call");
        }

        $sql = "SELECT * FROM ::prefix::ships WHERE ship_id = :ship_id"; // Get target player information
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $planetinfo['owner'], \PDO::PARAM_INT);
        $stmt->execute();
        $ownerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;
        $character_object = new Character();

        // Planet beams
        $targetbeams = Helpers\CalcLevels::abstractLevels($ownerinfo['beams'] + $base_factor, $tkireg);
        if ($targetbeams > $planetinfo['energy'])
        {
            $targetbeams = $planetinfo['energy'];
        }

        $planetinfo['energy'] -= $targetbeams;

        // Planet shields
        $targetshields = Helpers\CalcLevels::abstractLevels($ownerinfo['shields'] + $base_factor, $tkireg);
        if ($targetshields > $planetinfo['energy'])
        {
            $targetshields = $planetinfo['energy'];
        }

        $planetinfo['energy'] -= $targetshields;

        // Planet torps
        $torp_launchers = round(pow($tkireg->level_factor, ($ownerinfo['torp_launchers']) + $base_factor)) * 10;
        $torps = $planetinfo['torps'];
        $targettorps = $torp_launchers;

        if ($torp_launchers > $torps)
        {
            $targettorps = $torps;
        }

        $planetinfo['torps'] -= $targettorps;
        $targettorpdmg = $tkireg->torp_dmg_rate * $targettorps;

        // Planet fighters
        $targetfighters = $planetinfo['fighters'];

        // Attacker beams
        $attackerbeams = Helpers\CalcLevels::abstractLevels($playerinfo['beams'], $tkireg);
        if ($attackerbeams > $playerinfo['ship_energy'])
        {
            $attackerbeams = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] -= $attackerbeams;

        // Attacker shields
        $attackershields = Helpers\CalcLevels::abstractLevels($playerinfo['shields'], $tkireg);
        if ($attackershields > $playerinfo['ship_energy'])
        {
            $attackershields = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] -= $attackershields;

        // Attacker torps
        $attackertorps = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
        if ($attackertorps > $playerinfo['torps'])
        {
            $attackertorps = $playerinfo['torps'];
        }

        $playerinfo['torps'] -= $attackertorps;
        $attackertorpdamage = $tkireg->torp_dmg_rate * $attackertorps;

        // Attacker fighters
        $attackerfighters = $playerinfo['ship_fighters'];

        // Attacker armor
        $attackerarmor = $playerinfo['armor_pts'];

        // Begin combat
        if ($attackerbeams > 0 && $targetfighters > 0)              // Attacker has beams - Target has fighters - Beams v. fighters
        {
            if ($attackerbeams > $targetfighters)                   // Attacker beams beat target fighters
            {
                $targetfighters = 0;                                // Target loses all fighters
            }
            else                                                    // Attacker beams less than or equal to target fighters
            {
                $targetfighters = $targetfighters - $attackerbeams; // Target loses fighters equal to attacker beams
            }
        }

        if ($attackerfighters > 0 && $targetbeams > 0)                          // Target has beams - attacker has fighters - Beams v. fighters
        {
            if ($targetbeams > round($attackerfighters / 2))                   // Target beams greater than half attacker fighters
            {
                $lost = $attackerfighters - (round($attackerfighters / 2));    // Attacker loses half of all fighters
                $attackerfighters = $attackerfighters - $lost;
                $targetbeams = $targetbeams - $lost;                            // Target loses beams equal to half of attackers fighters
            }
            else
            {                                                              // Target beams are less than half of attackers fighters
                $attackerfighters = $attackerfighters - $targetbeams;      // Attacker loses fighters equal to target beams
                $targetbeams = 0;                                          // Target loses all beams
            }
        }

        if ($targetbeams > 0)                                                // Target has beams left - continue combat - Beams v. shields
        {
            if ($targetbeams > $attackershields)                             // Target beams greater than attacker shields
            {
                $targetbeams = $targetbeams - $attackershields;              // Target loses beams equal to attacker shields
            }
            else                                                             // Target beams less than or equal to attacker shields
            {
                $targetbeams = 0;                                            // Target loses all beams
            }
        }

        if ($targetbeams > 0)                                   // Target has beams left - continue combat - beams v. armor
        {
            if ($targetbeams > $attackerarmor)                  // Target beams greater than attacker armor
            {
                $attackerarmor = 0;                             // Attacker loses all armor (attacker destroyed)
            }
            else                                                // Target beams less than or equal to attacker armor
            {
                $attackerarmor = $attackerarmor - $targetbeams; // Attacker loses armor equal to target beams
            }
        }

        if ($targetfighters > 0 && $attackertorpdamage > 0)                 // Attacker fires torpedoes - target has fighters - torps v. fighters
        {
            if ($attackertorpdamage > $targetfighters)                      // Attacker fired torpedoes greater than target fighters
            {
                $targetfighters = 0;                                        // Target loses all fighters
            }
            else                                                            // Attacker fired torpedoes less than or equal to half of the target fighters
            {
                $targetfighters = $targetfighters - $attackertorpdamage;    // Target loses fighters equal to attacker torpedoes fired
            }
        }

        if ($attackerfighters > 0 && $targettorpdmg > 0)                        // Target fires torpedoes - attacker has fighters - torpedoes v. fighters
        {
            if ($targettorpdmg > round($attackerfighters / 2))                 // Target fired torpedoes greater than half of attackers fighters
            {
                $lost = $attackerfighters - (round($attackerfighters / 2));
                $attackerfighters = $attackerfighters - $lost;                  // Attacker loses half of all fighters
                $targettorpdmg = $targettorpdmg - $lost;                        // Target loses fired torpedoes equal to half of attacker fighters
            }
            else
            {                                                                   // Target fired torpedoes less than or equal to half of attacker fighters
                $attackerfighters = $attackerfighters - $targettorpdmg;         // Attacker loses fighters equal to target torpedoes fired
                $targettorpdmg = 0;                                             // Tartget loses all torpedoes fired
            }
        }

        if ($targettorpdmg > 0)                                     // Target fires torpedoes - continue combat - torpedoes v. armor
        {
            if ($targettorpdmg > $attackerarmor)                    // Target fired torpedoes greater than half of attacker armor
            {
                $attackerarmor = 0;                                 // Attacker loses all armor (Attacker destroyed)
            }
            else
            {                                                       // Target fired torpedoes less than or equal to half attacker armor
                $attackerarmor = $attackerarmor - $targettorpdmg;   // Attacker loses armor equal to the target torpedoes fired
            }
        }

        if ($attackerfighters > 0 && $targetfighters > 0)                    // Attacker has fighters - target has fighters - fighters v. fighters
        {
            if ($attackerfighters > $targetfighters)                         // Attacker fighters greater than target fighters
            {
                $temptargfighters = 0;                                       // Target will lose all fighters
            }
            else                                                             // Attacker fighters less than or equal to target fighters
            {                                                                // Attackers fighters less than or equal to target fighters
                $temptargfighters = $targetfighters - $attackerfighters;     // Target will loose fighters equal to attacker fighters
            }

            if ($targetfighters > $attackerfighters)
            {                                                                // Target fighters greater than attackers fighters
                $tempplayfighters = 0;                                       // Attackerwill loose ALL fighters
            }
            else
            {                                                                // Target fighters less than or equal to attackers fighters
                $tempplayfighters = $attackerfighters - $targetfighters;     // Attacker will loose fighters equal to target fighters
            }

            $attackerfighters = $tempplayfighters;
            $targetfighters = $temptargfighters;
        }

        if ($targetfighters > 0)                                            // Target has fighters - continue combat - fighters v. armor
        {
            if ($targetfighters > $attackerarmor)
            {                                                               // Target fighters greater than attackers armor
                $attackerarmor = 0;                                         // attacker loses all armor (attacker destroyed)
            }
            else
            {                                                               // Target fighters less than or equal to attackers armor
                $attackerarmor = $attackerarmor - $targetfighters;          // attacker loses armor equal to target fighters
            }
        }

        // Fix negative values
        if ($attackerfighters < 0)
        {
            $attackerfighters = 0;
        }

        if ($attackertorps < 0)
        {
            $attackertorps = 0;
        }

        if ($attackerarmor < 0)
        {
            $attackerarmor = 0;
        }

        if ($targetfighters < 0)
        {
            $targetfighters = 0;
        }

        if ($targettorps < 0)
        {
            $targettorps = 0;
        }

        if (!$attackerarmor > 0) // Check if attackers ship destroyed
        {
            \Tki\Models\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Ship destroyed by planetary defenses on planet $planetinfo[name]");
            $character_object->kill($pdo_db, $lang, $playerinfo['ship_id'], $tkireg);

            $free_ore = round($playerinfo['ship_ore'] / 2);
            $free_organics = round($playerinfo['ship_organics'] / 2);
            $free_goods = round($playerinfo['ship_goods'] / 2);
            $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $playerinfo['hull'])) + round(pow($tkireg->upgrade_factor, $playerinfo['engines'])) + round(pow($tkireg->upgrade_factor, $playerinfo['power'])) + round(pow($tkireg->upgrade_factor, $playerinfo['computer'])) + round(pow($tkireg->upgrade_factor, $playerinfo['sensors'])) + round(pow($tkireg->upgrade_factor, $playerinfo['beams'])) + round(pow($tkireg->upgrade_factor, $playerinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $playerinfo['shields'])) + round(pow($tkireg->upgrade_factor, $playerinfo['armor'])) + round(pow($tkireg->upgrade_factor, $playerinfo['cloak'])));
            $ship_salvage_rate = random_int(10, 20);
            $ship_salvage = $ship_value * $ship_salvage_rate / 100;
            $fighters_lost = $planetinfo['fighters'] - $targetfighters;

            // Log attack to planet owner
            \Tki\Models\PlayerLog::writeLog($pdo_db, $planetinfo['owner'], LogEnums::PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Kabal $playerinfo[character_name]|$free_ore|$free_organics|$free_goods|$ship_salvage_rate|$ship_salvage");

            // Update planet
            $sql = "UPDATE ::prefix::planets SET energy = :planet_energy, " .
                   "fighters = fighters - :fighters_lost, " .
                   "torps = torps - :targettorps, " .
                   "ore = ore + :free_ore, " .
                   "goods = goods + :free_goods, " .
                   "organics = organics + :free_organics, " .
                   "credits = credits + :ship_salvage, " .
                   "WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_energy', $planetinfo['energy'], \PDO::PARAM_INT);
            $stmt->bindParam(':fighters_lost', $fighters_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':targettorps', $targettorps, \PDO::PARAM_INT);
            $stmt->bindParam(':free_ore', $free_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':free_goods', $free_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':free_organics', $free_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_salvage', $ship_salvage, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        }
        else  // Must have made it past planet defenses
        {
            $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
            $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
            \Tki\Models\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Made it past defenses on planet $planetinfo[name]");

            // Update attackers
            $sql = "UPDATE ::prefix::ships SET ship_energy = :ship_energy, " .
                   "ship_fighters = ship_fighters - :fighters_lost, " .
                   "torps = torps - :attackertorps, " .
                   "armor_pts = armor_pts - :armor_lost, " .
                   "WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_energy', $playerinfo['ship_energy'], \PDO::PARAM_INT);
            $stmt->bindParam(':fighters_lost', $fighters_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':attackertorps', $attackertorps, \PDO::PARAM_INT);
            $stmt->bindParam(':armor_lost', $armor_lost, \PDO::PARAM_INT);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $playerinfo['ship_fighters'] = $attackerfighters;
            $playerinfo['torps'] = $attackertorps;
            $playerinfo['armor_pts'] = $attackerarmor;

            // Update planet
            $sql = "UPDATE ::prefix::planets SET energy = :planet_energy, " .
                   "fighters = fighters - :targetfighters, " .
                   "torps = torps - :targettorps, " .
                   "WHERE planet_id = :planet_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_energy', $planetinfo['energy'], \PDO::PARAM_INT);
            $stmt->bindParam(':targetfighters', $targetfighters, \PDO::PARAM_INT);
            $stmt->bindParam(':targettorps', $targettorps, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
            $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $planetinfo['fighters'] = $targetfighters;
            $planetinfo['torps'] = $targettorps;

            // Now we must attack all ships on the planet one by one
            $sql = "SELECT ship_id, ship_name FROM ::prefix::ships WHERE planet_id = :planetinfo_planet_id AND on_planet = 'Y'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planetinfo_planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $shiplist = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $i = 0;

            if ($shiplist !== false)
            {
                foreach ($shiplist as $onplanet)
                {
                    Actions\Kabal\KabalToShip::ship($pdo_db, $lang, $onplanet[$i]['ship_id'], $tkireg, $playerinfo);
                    $i++;
                }
            }

            $sql = "SELECT ship_id, ship_name FROM ::prefix::ships WHERE planet_id = :planetinfo_planet_id AND on_planet = 'Y'";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $ships_present = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (is_array($ships_present))
            {
                $shipsonplanet = count($ships_present);
            }
            else
            {
                $shipsonplanet = 0;
            }

            if ($shipsonplanet == 0)
            {
                // Must have killed all ships on the planet
                \Tki\Models\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Defeated all ships on planet $planetinfo[name]");

                // Log attack to planet owner
                \Tki\Models\PlayerLog::writeLog($pdo_db, $planetinfo['owner'], LogEnums::PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");

                // Update planet
                $sql = "UPDATE ::prefix::planets SET fighters = :fighters, " .
                       "torps = :torps, " .
                       "base = :base, " .
                       "owner = :owner, " .
                       "team = :team " .
                       "WHERE planet_id = :planet_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindValue(':fighers', 0, \PDO::PARAM_INT);
                $stmt->bindValue(':torps', 0, \PDO::PARAM_INT);
                $stmt->bindValue(':base', 'N', \PDO::PARAM_STR);
                $stmt->bindValue(':team', 0, \PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $planetinfo['planet_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                Helpers\Ownership::calc($pdo_db, $lang, $planetinfo['sector_id'], $tkireg);
            }
            else
            {
                // Must have died trying
                \Tki\Models\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "We were KILLED by ships defending planet $planetinfo[name]");
                // Log attack to planet owner
                \Tki\Models\PlayerLog::writeLog($pdo_db, $planetinfo['owner'], LogEnums::PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Kabal $playerinfo[character_name]|0|0|0|0|0");
                // No salvage for planet because it went to the ship that won
            }
        }
    }
}
