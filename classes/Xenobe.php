<?php
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: classes/Xenobe.php
//
// FUTURE: These are horribly bad. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Bad;

class Xenobe
{
    public static function xenobeTrade($pdo_db, $db, $playerinfo, \Tki\Reg $tkireg)
    {
        // We need to get rid of this.. the bug causing it needs to be identified and squashed. In the meantime, we want functional xen's. :)
        $tkireg->ore_price = 11;
        $tkireg->organics_price = 5;
        $tkireg->goods_price = 15;

        // Obtain sector information
        $sectres = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($playerinfo['sector']));
        \Tki\Db::logDbErrors($pdo_db, $db, $sectres, __LINE__, __FILE__);
        $sectorinfo = $sectres->fields;

        // Obtain zone information
        $zoneres = $db->Execute("SELECT zone_id, allow_attack, allow_trade FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
        \Tki\Db::logDbErrors($pdo_db, $db, $zoneres, __LINE__, __FILE__);
        $zonerow = $zoneres->fields;

        // Make sure we can trade here
        if ($zonerow['allow_trade'] == "N")
        {
            return;
        }

        // Check for a port we can use
        if ($sectorinfo['port_type'] == "none")
        {
            return;
        }

        // Xenobe do not trade at energy ports since they regen energy
        if ($sectorinfo['port_type'] == "energy")
        {
            return;
        }

        // Check for negative credits or cargo
        if ($playerinfo['ship_ore'] < 0)
        {
            $playerinfo['ship_ore'] = $shipore = 0;
        }

        if ($playerinfo['ship_organics'] < 0)
        {
            $playerinfo['ship_organics'] = $shiporganics = 0;
        }

        if ($playerinfo['ship_goods'] < 0)
        {
            $playerinfo['ship_goods'] = $shipgoods = 0;
        }

        if ($playerinfo['credits'] < 0)
        {
            $playerinfo['credits'] = 0;
        }

        if ($sectorinfo['port_ore'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_organics'] <= 0)
        {
            return;
        }

        if ($sectorinfo['port_goods'] <= 0)
        {
            return;
        }

        //  Check Xenobe Credits & Cargo
        if ($playerinfo['ship_ore'] > 0)
        {
            $shipore = $playerinfo['ship_ore'];
        }

        if ($playerinfo['ship_organics'] > 0)
        {
            $shiporganics = $playerinfo['ship_organics'];
        }

        if ($playerinfo['ship_goods'] > 0)
        {
            $shipgoods = $playerinfo['ship_goods'];
        }

        // Make sure we have cargo or credits
        if (!$playerinfo['credits'] > 0 && !$playerinfo['ship_ore'] > 0 && !$playerinfo['ship_goods'] > 0 && !$playerinfo['ship_organics'] > 0)
        {
            return;
        }

        //  Make sure cargo is compatible
        if ($sectorinfo['port_type'] == "ore" && $shipore > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "organics" && $shiporganics > 0)
        {
            return;
        }

        if ($sectorinfo['port_type'] == "goods" && $shipgoods > 0)
        {
            return;
        }

        // Lets trade some cargo
        if ($sectorinfo['port_type'] == "ore") // Port ore
        {
            // Set the prices
            $tkireg->ore_price = $tkireg->ore_price - $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limitt * $tkireg->inventory_factor;
            $tkireg->organics_price = $tkireg->organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $tkireg->goods_price = $tkireg->goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            //  Set cargo buy/sell
            $amount_organics = $playerinfo['ship_organics'];
            $amount_goods = $playerinfo['ship_goods'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_ore = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg->level_factor);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_ore = min($amount_ore, $sectorinfo['port_ore']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_ore = min($amount_ore, floor(($playerinfo['credits'] + $amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price) / $tkireg->ore_price));

            // Buy / sell cargo
            $total_cost = round(($amount_ore * $tkireg->ore_price) - ($amount_organics * $tkireg->organics_price + $amount_goods * $tkireg->goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = $playerinfo['ship_ore'] + $amount_ore;
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);
            $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating = rating + 1, credits = ?, ship_ore = ?, ship_organics = ?, ship_goods = ? WHERE ship_id = ?;", array($newcredits, $newore, $neworganics, $newgoods, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result, __LINE__, __FILE__);
            $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = port_ore - ?, port_organics = port_organics + ?, port_goods = port_goods + ? WHERE sector_id = ?;", array($amount_ore, $amount_organics, $amount_goods, $sectorinfo['sector_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result2, __LINE__, __FILE__);
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe Trade Results: Sold $amount_organics Organics Sold $amount_goods Goods Bought $amount_ore Ore Cost $total_cost");
        }

        if ($sectorinfo['port_type'] == "organics") // Port organics
        {
            // Set the prices
            $tkireg->organics_price = $tkireg->organics_price - $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
            $tkireg->ore_price = $tkireg->ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limitt * $tkireg->inventory_factor;
            $tkireg->goods_price = $tkireg->goods_price + $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_goods = $playerinfo['ship_goods'];

            // SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT
            $amount_organics = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg->level_factor);

            // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL
            $amount_organics = min($amount_organics, $sectorinfo['port_organics']);

            // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY
            $amount_organics = min($amount_organics, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_goods * $tkireg->goods_price) / $tkireg->organics_price));

            // Buy / sell cargo
            $total_cost = round(($amount_organics * $tkireg->organics_price) - ($amount_ore * $tkireg->ore_price + $amount_goods * $tkireg->goods_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = $playerinfo['ship_organics'] + $amount_organics;
            $newgoods = max(0, $playerinfo['ship_goods'] - $amount_goods);
            $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating = rating + 1, credits = ?, ship_ore = ?, ship_organics = ?, ship_goods = ? WHERE ship_id = ?;", array($newcredits, $newore, $neworganics, $newgoods, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result, __LINE__, __FILE__);
            $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore = port_ore + ?, port_organics = port_organics - ?, port_goods = port_goods + ? WHERE sector_id = ?;", array($amount_ore, $amount_organics, $amount_goods, $sectorinfo['sector_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result2, __LINE__, __FILE__);
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe Trade Results: Sold $amount_goods Goods Sold $amount_ore Ore Bought $amount_organics Organics Cost $total_cost");
        }

        if ($sectorinfo['port_type']=="goods") // Port goods
        {
            // Set the prices
            $tkireg->goods_price = $tkireg->goods_price - $tkireg->goods_delta * $sectorinfo['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
            $tkireg->ore_price = $tkireg->ore_price + $tkireg->ore_delta * $sectorinfo['port_ore'] / $tkireg->ore_limitt * $tkireg->inventory_factor;
            $tkireg->organics_price = $tkireg->organics_price + $tkireg->organics_delta * $sectorinfo['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;

            // Set cargo buy / sell
            $amount_ore = $playerinfo['ship_ore'];
            $amount_organics = $playerinfo['ship_organics'];

            // Since we sell all other holds we set amount to be our total hold limit
            $amount_goods = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg->level_factor);

            // We adjust this to make sure it does not exceed what the port has to sell
            $amount_goods = min($amount_goods, $sectorinfo['port_goods']);

            // We adjust this to make sure it does not exceed what we can afford to buy
            $amount_goods = min($amount_goods, floor(($playerinfo['credits'] + $amount_ore * $tkireg->ore_price + $amount_organics * $tkireg->organics_price) / $tkireg->goods_price));

            // Buy / sell cargo
            $total_cost = round(($amount_goods * $tkireg->goods_price) - ($amount_organics * $tkireg->organics_price + $amount_ore * $tkireg->ore_price));
            $newcredits = max(0, $playerinfo['credits'] - $total_cost);
            $newore = max(0, $playerinfo['ship_ore'] - $amount_ore);
            $neworganics = max(0, $playerinfo['ship_organics'] - $amount_organics);
            $newgoods = $playerinfo['ship_goods'] + $amount_goods;
            $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating=rating+1, credits = ?, ship_ore = ?, ship_organics = ?, ship_goods = ? WHERE ship_id = ?;", array($newcredits, $newore, $neworganics, $newgoods, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result, __LINE__, __FILE__);
            $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore + ?, port_organics = port_organics + ?, port_goods = port_goods - ? WHERE sector_id = ?;", array($amount_ore, $amount_organics, $amount_goods, $sectorinfo['sector_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $trade_result2, __LINE__, __FILE__);
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe Trade Results: Sold $amount_ore Ore Sold $amount_organics Organics Bought $amount_goods Goods Cost $total_cost");
        }
    }

    public static function xenobeToPlanet($pdo_db, $db, $planet_id, \Tki\Reg $tkireg, $playerinfo, $langvars)
    {
        // Xenobe planet attack code
        global $planetinfo, $xenobeisdead;

        $resh = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}universe WRITE, {$db->prefix}planets WRITE, {$db->prefix}news WRITE, {$db->prefix}logs WRITE");
        \Tki\Db::logDbErrors($pdo_db, $db, $resh, __LINE__, __FILE__);

        $resultp = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=?", array($planet_id)); // Get target planet information
        \Tki\Db::logDbErrors($pdo_db, $db, $resultp, __LINE__, __FILE__);
        $planetinfo = $resultp->fields;

        $resulto = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($planetinfo['owner'])); // Get target player information
        \Tki\Db::logDbErrors($pdo_db, $db, $resulto, __LINE__, __FILE__);
        $ownerinfo = $resulto->fields;

        $base_factor = ($planetinfo['base'] == 'Y') ? $tkireg->base_defense : 0;

        // Planet beams
        $targetbeams = \Tki\CalcLevels::beams($ownerinfo['beams'] + $base_factor, $tkireg->level_factor);
        if ($targetbeams > $planetinfo['energy'])
        {
            $targetbeams = $planetinfo['energy'];
        }
        $planetinfo['energy'] -= $targetbeams;

        // Planet shields
        $targetshields = \Tki\CalcLevels::shields($ownerinfo['shields'] + $base_factor, $tkireg->level_factor);
        if ($targetshields > $planetinfo['energy'])
        {
            $targetshields = $planetinfo['energy'];
        }
        $planetinfo['energy'] -= $targetshields;

        // Planet torps
        $torp_launchers = round(pow($tkireg->level_factor, ($ownerinfo['torp_launchers'])+ $base_factor)) * 10;
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
        $attackerbeams = \Tki\CalcLevels::beams($playerinfo['beams'], $tkireg->level_factor);
        if ($attackerbeams > $playerinfo['ship_energy'])
        {
            $attackerbeams = $playerinfo['ship_energy'];
        }
        $playerinfo['ship_energy'] -= $attackerbeams;

        // Attacker shields
        $attackershields = \Tki\CalcLevels::shields($playerinfo['shields'], $tkireg->level_factor);
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
                $lost = $targetfighters;
                $targetfighters = 0;                                // Target loses all fighters
                $attackerbeams = $attackerbeams - $lost;            // Attacker loses beams equal to target fighters
            }
            else                                                    // Attacker beams less than or equal to target fighters
            {
                $targetfighters = $targetfighters - $attackerbeams; // Target loses fighters equal to attacker beams
                $attackerbeams = 0;                                 // Attacker loses all beams
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

        if ($attackerbeams > 0)                                     // Attacker has beams left - continue combat - Beams v. shields
        {
            if ($attackerbeams > $targetshields)                    // Attacker beams greater than target shields
            {
                $attackerbeams = $attackerbeams - $targetshields;   // Attacker loses beams equal to target shields
                $targetshields = 0;                                 // Target loses all shields
            }
            else                                                    // Attacker beams less than or equal to target shields
            {
                $targetshields = $targetshields - $attackerbeams;   // Target loses shields equal to attacker beams
                $attackerbeams = 0;                                 // Attacker loses all beams
            }
        }

        if ($targetbeams > 0)                                                // Target has beams left - continue combat - Beams v. shields
        {
            if ($targetbeams > $attackershields)                             // Target beams greater than attacker shields
            {
                $targetbeams = $targetbeams - $attackershields;              // Target loses beams equal to attacker shields
                $attackershields = 0;                                        // Attacker loses all shields
            }
            else                                                             // Target beams less than or equal to attacker shields
            {
                $attackershields = $attackershields - $targetbeams;          // Attacker loses sheilds equal to target beams
                $targetbeams = 0;                                            // Target loses all beams
            }
        }

        if ($targetbeams > 0)                                   // Target has beams left - continue combat - beams v. armor
        {
            if ($targetbeams > $attackerarmor)                  // Target beams greater than attacker armor
            {
                $targetbeams = $targetbeams - $attackerarmor;   // Target loses beams equal to attacker armor
                $attackerarmor = 0;                             // Attacker loses all armor (attacker destroyed)
            }
            else                                                // Target beams less than or equal to attacker armor
            {
                $attackerarmor = $attackerarmor - $targetbeams; // Attacker loses armor equal to target beams
                $targetbeams = 0;                               // Target loses all beams
            }
        }

        if ($targetfighters > 0 && $attackertorpdamage > 0)                 // Attacker fires torpedoes - target has fighters - torps v. fighters
        {
            if ($attackertorpdamage > $targetfighters)                      // Attacker fired torpedoes greater than target fighters
            {
                $lost = $targetfighters;
                $targetfighters = 0;                                        // Target loses all fighters
                $attackertorpdamage = $attackertorpdamage - $lost;          // Attacker loses fired torpedoes equal to target fighters
            }
            else                                                            // Attacker fired torpedoes less than or equal to half of the target fighters
            {
                $targetfighters = $targetfighters - $attackertorpdamage;    // Target loses fighters equal to attacker torpedoes fired
                $attackertorpdamage=0;                                      // Attacker loses all torpedoes fired
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
                $targettorpdmg = $targettorpdmg - $attackerarmor;   // Target loses fired torpedoes equal to attacker armor
                $attackerarmor = 0;                                 // Attacker loses all armor (Attacker destroyed)
            }
            else
            {                                                       // Target fired torpedoes less than or equal to half attacker armor
                $attackerarmor = $attackerarmor - $targettorpdmg;   // Attacker loses armor equal to the target torpedoes fired
                $targettorpdmg = 0;                                 // Target loses all torpedoes fired
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

        if ($attackertorps    < 0)
        {
            $attackertorps = 0;
        }

        if ($attackershields  < 0)
        {
            $attackershields = 0;
        }

        if ($attackerbeams    < 0)
        {
            $attackerbeams = 0;
        }

        if ($attackerarmor    < 0)
        {
            $attackerarmor = 0;
        }

        if ($targetfighters   < 0)
        {
            $targetfighters = 0;
        }

        if ($targettorps      < 0)
        {
            $targettorps = 0;
        }

        if ($targetshields    < 0)
        {
            $targetshields = 0;
        }

        if ($targetbeams      < 0)
        {
            $targetbeams = 0;
        }

        if (!$attackerarmor > 0) // Check if attackers ship destroyed
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Ship destroyed by planetary defenses on planet $planetinfo[name]");
            \Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, false);
            $xenobeisdead = 1;

            $free_ore = round($playerinfo['ship_ore'] / 2);
            $free_organics = round($playerinfo['ship_organics'] / 2);
            $free_goods = round($playerinfo['ship_goods'] / 2);
            $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $playerinfo['hull'])) + round(pow($tkireg->upgrade_factor, $playerinfo['engines']))+round(pow($tkireg->upgrade_factor, $playerinfo['power'])) + round(pow($tkireg->upgrade_factor, $playerinfo['computer'])) + round(pow($tkireg->upgrade_factor, $playerinfo['sensors']))+round(pow($tkireg->upgrade_factor, $playerinfo['beams'])) + round(pow($tkireg->upgrade_factor, $playerinfo['torp_launchers'])) + round(pow($tkireg->upgrade_factor, $playerinfo['shields'])) + round(pow($tkireg->upgrade_factor, $playerinfo['armor'])) + round(pow($tkireg->upgrade_factor, $playerinfo['cloak'])));
            $ship_salvage_rate = random_int(10, 20);
            $ship_salvage = $ship_value * $ship_salvage_rate / 100;
            $fighters_lost = $planetinfo['fighters'] - $targetfighters;

            // Log attack to planet owner
            \Tki\PlayerLog::writeLog($pdo_db, $db, $planetinfo['owner'], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Xenobe $playerinfo[character_name]|$free_ore|$free_organics|$free_goods|$ship_salvage_rate|$ship_salvage");

            // Update planet
            $resi = $db->Execute("UPDATE {$db->prefix}planets SET energy=?, fighters=fighters-?, torps=torps-?, ore=ore+?, goods=goods+?, organics=organics+?, credits=credits+? WHERE planet_id=?", array($planetinfo['energy'], $fighters_lost, $targettorps, $free_ore, $free_goods, $free_organics, $ship_salvage, $planetinfo['planet_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resi, __LINE__, __FILE__);
        }
        else  // Must have made it past planet defences
        {
            $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
            $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
            $target_fighters_lost = $planetinfo['ship_fighters'] - $targetfighters;
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Made it past defenses on planet $planetinfo[name]");

            // Update attackers
            $resj = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=?, ship_fighters=ship_fighters-?, torps=torps-?, armor_pts=armor_pts-? WHERE ship_id=?", array($playerinfo['ship_energy'], $fighters_lost, $attackertorps, $armor_lost, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resj, __LINE__, __FILE__);
            $playerinfo['ship_fighters'] = $attackerfighters;
            $playerinfo['torps'] = $attackertorps;
            $playerinfo['armor_pts'] = $attackerarmor;

            // Update planet
            $resk = $db->Execute("UPDATE {$db->prefix}planets SET energy=?, fighters=?, torps=torps-? WHERE planet_id=?", array($planetinfo['energy'], $targetfighters, $targettorps, $planetinfo['planet_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resk, __LINE__, __FILE__);
            $planetinfo['fighters'] = $targetfighters;
            $planetinfo['torps'] = $targettorps;

            // Now we must attack all ships on the planet one by one
            $resultps = $db->Execute("SELECT ship_id,ship_name FROM {$db->prefix}ships WHERE planet_id=? AND on_planet='Y'", array($planetinfo['planet_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultps, __LINE__, __FILE__);
            $shipsonplanet = $resultps->RecordCount();
            if ($shipsonplanet > 0)
            {
                while (!$resultps->EOF && $xenobeisdead < 1)
                {
                    $onplanet = $resultps->fields;
                    self::xenobeToShip($pdo_db, $db, $onplanet['ship_id'], $tkireg, $playerinfo, $langvars);
                    $resultps->MoveNext();
                }
            }

            $resultps = $db->Execute("SELECT ship_id,ship_name FROM {$db->prefix}ships WHERE planet_id=? AND on_planet='Y'", array($planetinfo['planet_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultps, __LINE__, __FILE__);
            $shipsonplanet = $resultps->RecordCount();
            if ($shipsonplanet == 0 && $xenobeisdead < 1)
            {
                // Must have killed all ships on the planet
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Defeated all ships on planet $planetinfo[name]");

                // Log attack to planet owner
                \Tki\PlayerLog::writeLog($pdo_db, $db, $planetinfo['owner'], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");

                // Update planet
                $resl = $db->Execute("UPDATE {$db->prefix}planets SET fighters=0, torps=0, base='N', owner=0, team=0 WHERE planet_id=?", array($planetinfo['planet_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $resl, __LINE__, __FILE__);

                \Tki\Ownership::calc($pdo_db, $db, $planetinfo['sector_id'], $tkireg->min_bases_to_own, $langvars);
            }
            else
            {
                // Must have died trying
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "We were KILLED by ships defending planet $planetinfo[name]");
                // Log attack to planet owner
                \Tki\PlayerLog::writeLog($pdo_db, $db, $planetinfo['owner'], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Xenobe $playerinfo[character_name]|0|0|0|0|0");
                // No salvage for planet because it went to the ship that won
            }
        }

        $resx = $db->Execute("UNLOCK TABLES");
        \Tki\Db::logDbErrors($pdo_db, $db, $resx, __LINE__, __FILE__);
    }

    public static function xenobeToShip($pdo_db, $db, $ship_id, \Tki\Reg $tkireg, $playerinfo, $langvars)
    {
        // Setup general variables
        global $attackerbeams;
        global $attackerfighters;
        global $attackershields;
        global $attackertorps;
        global $attackerarmor;
        global $attackertorpdamage;
        global $xenobeisdead;

        // Lookup target details
        $resa = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}universe WRITE, {$db->prefix}zones READ, {$db->prefix}planets READ, {$db->prefix}news WRITE, {$db->prefix}logs WRITE");
        \Tki\Db::logDbErrors($pdo_db, $db, $resa, __LINE__, __FILE__);
        $resultt = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($ship_id));
        \Tki\Db::logDbErrors($pdo_db, $db, $resultt, __LINE__, __FILE__);
        $targetinfo = $resultt->fields;

        // Verify not attacking another Xenobe
        // Added because the xenobe were killing each other off
        if (mb_strstr($targetinfo['email'], '@xenobe'))                       // He's a xenobe
        {
            $resb = $db->Execute("UNLOCK TABLES");
            \Tki\Db::logDbErrors($pdo_db, $db, $resb, __LINE__, __FILE__);

            return;
        }

        // Verify sector allows attack
        $sectres = $db->Execute("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id = ?;", array($targetinfo['sector']));
        \Tki\Db::logDbErrors($pdo_db, $db, $sectres, __LINE__, __FILE__);
        $sectrow = $sectres->fields;
        $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectrow['zone_id']));
        \Tki\Db::logDbErrors($pdo_db, $db, $zoneres, __LINE__, __FILE__);
        $zonerow = $zoneres->fields;
        if ($zonerow['allow_attack'] == "N")                        //  Dest link must allow attacking
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Attack failed, you are in a sector that prohibits attacks.");

            return;
        }

        // Use emergency warp device
        if ($targetinfo['dev_emerwarp'] > 0)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_ATTACK_EWD, "Xenobe $playerinfo[character_name]");
            $dest_sector = random_int(0, (int) $tkireg->max_sectors);
            $result_warp = $db->Execute("UPDATE {$db->prefix}ships SET sector = ?, dev_emerwarp = dev_emerwarp - 1 WHERE ship_id = ?;", array($dest_sector, $targetinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $result_warp, __LINE__, __FILE__);

            return;
        }

        // Setup attacker variables
        $attackerbeams = \Tki\CalcLevels::beams($playerinfo['beams'], $tkireg->level_factor);
        if ($attackerbeams > $playerinfo['ship_energy'])
        {
            $attackerbeams = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackerbeams;
        $attackershields = \Tki\CalcLevels::shields($playerinfo['shields'], $tkireg->level_factor);
        if ($attackershields > $playerinfo['ship_energy'])
        {
            $attackershields = $playerinfo['ship_energy'];
        }

        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackershields;
        $attackertorps = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
        if ($attackertorps > $playerinfo['torps'])
        {
            $attackertorps = $playerinfo['torps'];
        }

        $playerinfo['torps'] = $playerinfo['torps'] - $attackertorps;
        $attackertorpdamage = $tkireg->torp_dmg_rate * $attackertorps;
        $attackerarmor = $playerinfo['armor_pts'];
        $attackerfighters = $playerinfo['ship_fighters'];

        // Setup target variables
        $targetbeams = \Tki\CalcLevels::beams($targetinfo['beams'], $tkireg->level_factor);
        if ($targetbeams > $targetinfo['ship_energy'])
        {
            $targetbeams = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetbeams;
        $targetshields = \Tki\CalcLevels::shields($targetinfo['shields'], $tkireg->level_factor);
        if ($targetshields>$targetinfo['ship_energy'])
        {
            $targetshields = $targetinfo['ship_energy'];
        }

        $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetshields;
        $targettorpnum = round(pow($tkireg->level_factor, $targetinfo['torp_launchers']))*2;
        if ($targettorpnum > $targetinfo['torps'])
        {
            $targettorpnum = $targetinfo['torps'];
        }

        $targetinfo['torps'] = $targetinfo['torps'] - $targettorpnum;
        $targettorpdmg = $tkireg->torp_dmg_rate * $targettorpnum;
        $targetarmor = $targetinfo['armor_pts'];
        $targetfighters = $targetinfo['ship_fighters'];

        // Begin combat procedures
        if ($attackerbeams > 0 && $targetfighters > 0)                  // Attacker has beams - target has fighters - beams vs. fighters
        {
            if ($attackerbeams > round($targetfighters / 2))           // Attacker beams GT half target fighters
            {
                $lost = $targetfighters-(round($targetfighters/2));
                $targetfighters = $targetfighters - $lost;              // T loses half all fighters
                $attackerbeams = $attackerbeams - $lost;                // A loded beams EQ to half T fighters
            }
            else                                                        // Attacker beams LE half target fighters
            {
                $targetfighters = $targetfighters - $attackerbeams;     // T loses fighters EQ to A beams
                $attackerbeams = 0;                                     // A loses all beams
            }
        }

        if ($attackerfighters > 0 && $targetbeams > 0)                      // Target has beams - Attacker has fighters - beams vs. fighters
        {
            if ($targetbeams > round($attackerfighters / 2))               // Target beams GT half attacker fighters
            {
                $lost = $attackerfighters-(round($attackerfighters / 2));
                $attackerfighters = $attackerfighters - $lost;               // A loses half of all fighters
                $targetbeams = $targetbeams - $lost;                         // T loses beams EQ to half A fighters
            }
            else
            {                                                                 // Target beams LE half attacker fighters
                $attackerfighters = $attackerfighters - $targetbeams;         // A loses fighters EQ to T beams A loses fighters
                $targetbeams=0;                                               // T loses all beams
            }
        }

        if ($attackerbeams > 0)
        {                                                                   // Attacker has beams left - continue combat - Beams vs. shields
            if ($attackerbeams > $targetshields)                            // Attacker beams GT target shields
            {
                $attackerbeams = $attackerbeams - $targetshields;           // A loses beams EQ to T shields
                $targetshields=0;                                           // T loses all shields
            }
            else
            {                                                               // Attacker beams LE target shields
                $targetshields = $targetshields - $attackerbeams;           // T loses shields EQ to A beams
                $attackerbeams=0;                                           // A loses all beams
            }
        }
        if ($targetbeams > 0)
        {                                                                   // Target has beams left - continue combat - beams VS shields
            if ($targetbeams > $attackershields)
            {                                                               // Target beams GT Attacker shields
                $targetbeams = $targetbeams - $attackershields;             // T loses beams EQ to A shields
                $attackershields = 0;                                       // A loses all shields
            }
            else
            {                                                               // Target beams LE Attacker shields
                $attackershields = $attackershields - $targetbeams;         // A loses shields EQ to T beams
                $targetbeams = 0;                                           // T loses all beams
            }
        }

        if ($attackerbeams > 0)
        {                                                                   // Attacker has beams left - continue combat - beams VS armor
            if ($attackerbeams > $targetarmor)
            {                                                               // Attacker beams GT target armor
                $attackerbeams = $attackerbeams - $targetarmor;             // A loses beams EQ to T armor
                $targetarmor = 0;                                           // T loses all armor (T DESTROYED)
            }
            else
            {                                                               // Attacker beams LE target armor
                $targetarmor = $targetarmor - $attackerbeams;               // T loses armor EQ to A beams
                $attackerbeams = 0;                                         // A loses all beams
            }
        }

        if ($targetbeams > 0)
        {                                                                   // Target has beams left - continue combat - beams VS armor
            if ($targetbeams > $attackerarmor)
            {                                                               // Target beams GT Attacker armor
                $targetbeams = $targetbeams - $attackerarmor;               // T loses beams EQ to A armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
            else
            {                                                               // Target beams LE Attacker armor
                $attackerarmor = $attackerarmor - $targetbeams;             // A loses armor EQ to T beams
                $targetbeams = 0;                                           // T loses all beams
            }
        }
        if ($targetfighters > 0 && $attackertorpdamage > 0)
        {                                                                   // Attacker fires torps - target has fighters - torps VS fighters
            if ($attackertorpdamage > round($targetfighters / 2))
            {                                                               // Attacker fired torps GT half target fighters
                $lost = $targetfighters - (round($targetfighters / 2));
                $targetfighters = $targetfighters - $lost;                  // T loses half all fighters
                $attackertorpdamage = $attackertorpdamage - $lost;          // A loses fired torps EQ to half T fighters
            }
            else
            {                                                               // Attacker fired torps LE half target fighters
                $targetfighters = $targetfighters - $attackertorpdamage;    // T loses fighters EQ to A torps fired
                $attackertorpdamage = 0;                                    // A loses all torps fired
            }
        }
        if ($attackerfighters > 0 && $targettorpdmg > 0)
        {                                                                   // Target fires torps - Attacker has fighters - torps VS fighters
            if ($targettorpdmg > round($attackerfighters / 2))
            {                                                               // Target fired torps GT half Attacker fighters
                $lost = $attackerfighters - (round($attackerfighters / 2));
                $attackerfighters = $attackerfighters - $lost;               // A loses half all fighters
                $targettorpdmg = $targettorpdmg - $lost;                     // T loses fired torps EQ to half A fighters
            }
            else
            {                                                                // Target fired torps LE half Attacker fighters
                $attackerfighters = $attackerfighters - $targettorpdmg;      // A loses fighters EQ to T torps fired
                $targettorpdmg = 0;                                          // T loses all torps fired
            }
        }
        if ($attackertorpdamage > 0)
        {                                                                   // Attacker fires torps - continue combat - torps VS armor
            if ($attackertorpdamage > $targetarmor)
            {                                                               // Attacker fired torps GT half target armor
                $attackertorpdamage = $attackertorpdamage - $targetarmor;   // A loses fired torps EQ to T armor
                $targetarmor=0;                                             // T loses all armor (T DESTROYED)
            }
            else
            {                                                                // Attacker fired torps LE half target armor
                $targetarmor = $targetarmor - $attackertorpdamage;           // T loses armor EQ to A torps fired
                $attackertorpdamage = 0;                                     // A loses all torps fired
            }
        }
        if ($targettorpdmg > 0)
        {                                                                   // Target fires torps - continue combat - torps VS armor
            if ($targettorpdmg > $attackerarmor)
            {                                                               // Target fired torps GT half Attacker armor
                $targettorpdmg = $targettorpdmg - $attackerarmor;           // T loses fired torps EQ to A armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
            else
            {                                                               // Target fired torps LE half Attacker armor
                $attackerarmor = $attackerarmor - $targettorpdmg;           // A loses armor EQ to T torps fired
                $targettorpdmg = 0;                                         // T loses all torps fired
            }
        }
        if ($attackerfighters > 0 && $targetfighters > 0)
        {                                                                   // Attacker has fighters - target has fighters - fighters VS fighters
            if ($attackerfighters > $targetfighters)
            {                                                               // Attacker fighters GT target fighters
                $temptargfighters = 0;                                      // T will lose all fighters
            }
            else
            {                                                               // Attacker fighters LE target fighters
                $temptargfighters = $targetfighters - $attackerfighters;    // T will lose fighters EQ to A fighters
            }

            if ($targetfighters > $attackerfighters)
            {                                                               // Target fighters GT Attacker fighters
                $tempplayfighters = 0;                                      // A will lose all fighters
            }
            else
            {                                                               // Target fighters LE Attacker fighters
                $tempplayfighters = $attackerfighters - $targetfighters;    // A will lose fighters EQ to T fighters
            }

            $attackerfighters = $tempplayfighters;
            $targetfighters = $temptargfighters;
        }
        if ($attackerfighters > 0)
        {                                                                   // Attacker has fighters - continue combat - fighters VS armor
            if ($attackerfighters > $targetarmor)
            {                                                               // Attacker fighters GT target armor
                $targetarmor = 0;                                           // T loses all armor (T DESTROYED)
            }
            else
            {                                                               // Attacker fighters LE target armor
                $targetarmor = $targetarmor - $attackerfighters;            // T loses armor EQ to A fighters
            }
        }
        if ($targetfighters > 0)
        {                                                                   // Target has fighters - continue combat - fighters VS armor
            if ($targetfighters > $attackerarmor)
            {                                                               // Target fighters GT Attacker armor
                $attackerarmor = 0;                                         // A loses all armor (A DESTROYED)
            }
            else
            {                                                               // Target fighters LE Attacker armor
                $attackerarmor = $attackerarmor - $targetfighters;          // A loses armor EQ to T fighters
            }
        }

        // Fix negative value vars
        if ($attackerfighters < 0)
        {
            $attackerfighters = 0;
        }

        if ($attackertorps    < 0)
        {
            $attackertorps = 0;
        }

        if ($attackershields  < 0)
        {
            $attackershields = 0;
        }

        if ($attackerbeams    < 0)
        {
            $attackerbeams = 0;
        }

        if ($attackerarmor    < 0)
        {
            $attackerarmor = 0;
        }

        if ($targetfighters   < 0)
        {
            $targetfighters = 0;
        }

        if ($targettorpnum    < 0)
        {
            $targettorpnum = 0;
        }

        if ($targetshields    < 0)
        {
            $targetshields = 0;
        }

        if ($targetbeams      < 0)
        {
            $targetbeams = 0;
        }

        if ($targetarmor      < 0)
        {
            $targetarmor = 0;
        }

        // Desl with destroyed ships

        // Target ship was destroyed
        if (!$targetarmor > 0)
        {
            if ($targetinfo['dev_escapepod'] == "Y")
            // Target had no escape pod
            {
                $rating=round($targetinfo['rating'] / 2);
                $resc = $db->Execute("UPDATE {$db->prefix}ships SET hull = 0, engines = 0, power = 0, computer = 0, sensors = 0, beams = 0, torp_launchers = 0, torps = 0, armor = 0, armor_pts = 100, cloak = 0, shields = 0, sector = 0, ship_ore = 0, ship_organics = 0, ship_energy = 1000, ship_colonists = 0, ship_goods = 0, ship_fighters = 100, ship_damage = 0, on_planet='N', planet_id = 0, dev_warpedit = 0, dev_genesis = 0, dev_beacon = 0, dev_emerwarp = 0, dev_escapepod = 'N', dev_fuelscoop = 'N', dev_minedeflector = 0, ship_destroyed = 'N', rating = ?, dev_lssd='N' WHERE ship_id = ?;", array($rating, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $resc, __LINE__, __FILE__);
                \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_ATTACK_LOSE, "Xenobe $playerinfo[character_name]|Y");
            }
            else
            // Target had no pod
            {
                \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_ATTACK_LOSE, "Xenobe $playerinfo[character_name]|N");
                \Tki\Character::kill($pdo_db, $db, $targetinfo['ship_id'], $langvars, $tkireg, false);
            }

            if ($attackerarmor>0)
            {
                // Attacker still alive to salvage target
                $rating_change=round($targetinfo['rating'] * $tkireg->rating_combat_factor);
                $free_ore = round($targetinfo['ship_ore'] / 2);
                $free_organics = round($targetinfo['ship_organics'] / 2);
                $free_goods = round($targetinfo['ship_goods'] / 2);
                $free_holds = \Tki\CalcLevels::holds($playerinfo['hull'], $tkireg->level_factor) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
                if ($free_holds > $free_goods)
                {                                                        // Figure out what we can carry
                    $salv_goods = $free_goods;
                    $free_holds = $free_holds - $free_goods;
                }
                elseif ($free_holds > 0)
                {
                    $salv_goods = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_goods = 0;
                }

                if ($free_holds > $free_ore)
                {
                    $salv_ore = $free_ore;
                    $free_holds = $free_holds - $free_ore;
                }
                elseif ($free_holds > 0)
                {
                    $salv_ore = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_ore = 0;
                }

                if ($free_holds > $free_organics)
                {
                    $salv_organics = $free_organics;
                    $free_holds = $free_holds - $free_organics;
                }
                elseif ($free_holds > 0)
                {
                    $salv_organics = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_organics = 0;
                }

                $ship_value = $tkireg->upgrade_cost * (round(pow($tkireg->upgrade_factor, $targetinfo['hull']))+round(pow($tkireg->upgrade_factor, $targetinfo['engines']))+round(pow($tkireg->upgrade_factor, $targetinfo['power']))+round(pow($tkireg->upgrade_factor, $targetinfo['computer']))+round(pow($tkireg->upgrade_factor, $targetinfo['sensors']))+round(pow($tkireg->upgrade_factor, $targetinfo['beams']))+round(pow($tkireg->upgrade_factor, $targetinfo['torp_launchers']))+round(pow($tkireg->upgrade_factor, $targetinfo['shields']))+round(pow($tkireg->upgrade_factor, $targetinfo['armor']))+round(pow($tkireg->upgrade_factor, $targetinfo['cloak'])));
                $ship_salvage_rate = random_int (10, 20);
                $ship_salvage = $ship_value * $ship_salvage_rate / 100;
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Attack successful, $targetinfo[character_name] was defeated and salvaged for $ship_salvage credits.");
                $resd = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore = ship_ore + ?, ship_organics = ship_organics + ?, ship_goods = ship_goods + ?, credits = credits + ? WHERE ship_id = ?;", array($salv_ore, $salv_organics, $salv_goods, $ship_salvage, $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $resd, __LINE__, __FILE__);
                $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
                $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
                $energy = $playerinfo['ship_energy'];
                $rese = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, torps = torps - ?, armor_pts = armor_pts - ?, rating = rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $attackertorps, $armor_lost, $rating_change, $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $rese, __LINE__, __FILE__);
            }
        }

        // Target and attacker live
        if ($targetarmor > 0 && $attackerarmor > 0)
        {
            $rating_change = round($targetinfo['rating'] * .1);
            $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
            $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
            $energy = $playerinfo['ship_energy'];
            $target_rating_change = round($targetinfo['rating'] / 2);
            $target_armor_lost = $targetinfo['armor_pts'] - $targetarmor;
            $target_fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
            $target_energy = $targetinfo['ship_energy'];
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Attack failed, $targetinfo[character_name] survived.");
            \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_ATTACK_WIN, "Xenobe $playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");
            $resf = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, torps = torps - ? , armor_pts = armor_pts - ?, rating=rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $attackertorps, $armor_lost, $rating_change, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resf, __LINE__, __FILE__);
            $resg = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ?, ship_fighters = ship_fighters - ?, armor_pts=armor_pts - ?, torps=torps - ?, rating = ? WHERE ship_id = ?;", array($target_energy, $target_fighters_lost, $target_armor_lost, $targettorpnum, $target_rating_change, $targetinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resg, __LINE__, __FILE__);
        }

        // Attacker ship destroyed
        if (!$attackerarmor > 0)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "$targetinfo[character_name] destroyed your ship!");
            \Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, false);
            $xenobeisdead = 1;
            if ($targetarmor > 0)
            {
                // Target still alive to salvage attacker
                $rating_change = round($playerinfo['rating'] * $tkireg->rating_combat_factor);
                $free_ore = round($playerinfo['ship_ore'] / 2);
                $free_organics = round($playerinfo['ship_organics'] / 2);
                $free_goods = round($playerinfo['ship_goods'] / 2);
                $free_holds = \Tki\CalcLevels::holds($targetinfo['hull'], $tkireg->level_factor) - $targetinfo['ship_ore'] - $targetinfo['ship_organics'] - $targetinfo['ship_goods'] - $targetinfo['ship_colonists'];
                if ($free_holds > $free_goods)
                {                                                        // Figure out what target can carry
                    $salv_goods = $free_goods;
                    $free_holds = $free_holds - $free_goods;
                }
                elseif ($free_holds > 0)
                {
                    $salv_goods = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_goods = 0;
                }

                if ($free_holds > $free_ore)
                {
                    $salv_ore = $free_ore;
                    $free_holds = $free_holds - $free_ore;
                }
                elseif ($free_holds > 0)
                {
                    $salv_ore = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_ore = 0;
                }

                if ($free_holds > $free_organics)
                {
                    $salv_organics = $free_organics;
                    $free_holds = $free_holds - $free_organics;
                }
                elseif ($free_holds > 0)
                {
                    $salv_organics = $free_holds;
                    $free_holds = 0;
                }
                else
                {
                    $salv_organics = 0;
                }

                $ship_value = $tkireg->upgrade_cost*(round(pow($tkireg->upgrade_factor, $playerinfo['hull']))+round(pow($tkireg->upgrade_factor, $playerinfo['engines']))+round(pow($tkireg->upgrade_factor, $playerinfo['power']))+round(pow($tkireg->upgrade_factor, $playerinfo['computer']))+round(pow($tkireg->upgrade_factor, $playerinfo['sensors']))+round(pow($tkireg->upgrade_factor, $playerinfo['beams']))+round(pow($tkireg->upgrade_factor, $playerinfo['torp_launchers']))+round(pow($tkireg->upgrade_factor, $playerinfo['shields']))+round(pow($tkireg->upgrade_factor, $playerinfo['armor']))+round(pow($tkireg->upgrade_factor, $playerinfo['cloak'])));
                $ship_salvage_rate = random_int(10, 20);
                $ship_salvage = $ship_value * $ship_salvage_rate / 100;
                \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_ATTACK_WIN, "Xenobe $playerinfo[character_name]|$armor_lost|$fighters_lost");
                \Tki\PlayerLog::writeLog($pdo_db, $db, $targetinfo['ship_id'], LOG_RAW, "You destroyed the Xenobe ship and salvaged $salv_ore units of ore, $salv_organics units of organics, $salv_goods units of goods, and salvaged $ship_salvage_rate% of the ship for $ship_salvage credits.");
                $resh = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore = ship_ore + ?, ship_organics = ship_organics + ?, ship_goods = ship_goods + ?, credits = credits + ? WHERE ship_id = ?;", array($salv_ore, $salv_organics, $salv_goods, $ship_salvage, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $resh, __LINE__, __FILE__);
                $armor_lost = $targetinfo['armor_pts'] - $targetarmor;
                $fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
                $energy = $targetinfo['ship_energy'];
                $resi = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy = ? , ship_fighters = ship_fighters - ?, torps = torps - ?, armor_pts = armor_pts - ?, rating=rating - ? WHERE ship_id = ?;", array($energy, $fighters_lost, $targettorpnum, $armor_lost, $rating_change, $targetinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $resi, __LINE__, __FILE__);
            }
        }
        $resj = $db->Execute("UNLOCK TABLES");
        \Tki\Db::logDbErrors($pdo_db, $db, $resj, __LINE__, __FILE__);
    }

    public static function xenobeToSecDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, \Tki\Reg $tkireg)
    {
        // Check for sector defenses
        if ($targetlink > 0)
        {
            $resultf = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='F' ORDER BY quantity DESC", array($targetlink));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultf, __LINE__, __FILE__);
            $i = 0;
            $total_sector_fighters = 0;
            $defences = array();
            if ($resultf instanceof \adodb\ADORecordSet)
            {
                while (!$resultf->EOF)
                {
                    $defences[$i] = $resultf->fields;
                    $total_sector_fighters += $defences[$i]['quantity'];
                    $i++;
                    $resultf->MoveNext();
                }
            }

            $resultm = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='M'", array($targetlink));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultm, __LINE__, __FILE__);
            $i = 0;
            $total_sector_mines = 0;
            if ($resultm instanceof \adodb\ADORecordSet)
            {
                while (!$resultm->EOF)
                {
                    $defences[$i] = $resultm->fields;
                    $total_sector_mines += $defences[$i]['quantity'];
                    $i++;
                    $resultm->MoveNext();
                }
            }

            if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0)) // Dest link has defenses so lets attack them
            {
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "ATTACKING SECTOR DEFENCES $total_sector_fighters fighters and $total_sector_mines mines.");
                $targetfighters = $total_sector_fighters;
                $playerbeams = \Tki\CalcLevels::beams($playerinfo['beams'], $tkireg->level_factor);
                if ($playerbeams > $playerinfo['ship_energy'])
                {
                    $playerbeams = $playerinfo['ship_energy'];
                }

                $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $playerbeams;
                $playershields = \Tki\CalcLevels::shields($playerinfo['shields'], $tkireg->level_factor);
                if ($playershields > $playerinfo['ship_energy'])
                {
                    $playershields = $playerinfo['ship_energy'];
                }

                $playertorpnum = round(pow($tkireg->level_factor, $playerinfo['torp_launchers'])) * 2;
                if ($playertorpnum > $playerinfo['torps'])
                {
                    $playertorpnum = $playerinfo['torps'];
                }

                $playertorpdmg = $tkireg->torp_dmg_rate * $playertorpnum;
                $playerarmor = $playerinfo['armor_pts'];
                $playerfighters = $playerinfo['ship_fighters'];
                $totalmines = $total_sector_mines;
                if ($totalmines > 1)
                {
                    $roll = random_int(1, (int) $totalmines);
                }
                else
                {
                    $roll = 1;
                }

                $totalmines = $totalmines - $roll;
                $playerminedeflect = $playerinfo['ship_fighters']; // Xenobe keep as many deflectors as fighters

                // Combat - Beams v fighters
                if ($targetfighters > 0 && $playerbeams > 0)
                {
                    if ($playerbeams > round($targetfighters / 2))
                    {
                        $temp = round($targetfighters / 2);
                        $targetfighters = $temp;
                        $playerbeams = $playerbeams - $temp;
                    }
                    else
                    {
                        $targetfighters = $targetfighters - $playerbeams;
                        $playerbeams = 0;
                    }
                }

                // Torpedoes v. fighters
                if ($targetfighters > 0 && $playertorpdmg > 0)
                {
                    if ($playertorpdmg > round($targetfighters / 2))
                    {
                        $temp = round($targetfighters / 2);
                        $targetfighters = $temp;
                        $playertorpdmg = $playertorpdmg - $temp;
                    }
                    else
                    {
                        $targetfighters = $targetfighters - $playertorpdmg;
                        $playertorpdmg = 0;
                    }
                }

                // Fighters v. fighters
                if ($playerfighters > 0 && $targetfighters > 0)
                {
                    if ($playerfighters > $targetfighters)
                    {
                        echo $langvars['l_sf_destfightall'];
                        $temptargfighters = 0;
                    }
                    else
                    {
                        $temptargfighters = $targetfighters - $playerfighters;
                    }

                    if ($targetfighters > $playerfighters)
                    {
                        $tempplayfighters = 0;
                    }
                    else
                    {
                        $tempplayfighters = $playerfighters - $targetfighters;
                    }

                    $playerfighters = $tempplayfighters;
                    $targetfighters = $temptargfighters;
                }

                // There are still fighters, so armor v. fighters
                if ($targetfighters > 0)
                {
                    if ($targetfighters > $playerarmor)
                    {
                        $playerarmor = 0;
                    }
                    else
                    {
                        $playerarmor = $playerarmor - $targetfighters;
                    }
                }

                // Get rid of the sector fighters that died
                $fighterslost = $total_sector_fighters - $targetfighters;
                \Tki\Fighters::destroy($pdo_db, $db, $targetlink, $fighterslost);

                // Message the defence owner with what happened
                $langvars['l_sf_sendlog'] = str_replace("[player]", "Xenobe $playerinfo[character_name]", $langvars['l_sf_sendlog']);
                $langvars['l_sf_sendlog'] = str_replace("[lost]", $fighterslost, $langvars['l_sf_sendlog']);
                $langvars['l_sf_sendlog'] = str_replace("[sector]", $targetlink, $langvars['l_sf_sendlog']);
                \Tki\SectorDefense::messageDefenseOwner($pdo_db, $db, $targetlink, $langvars['l_sf_sendlog']);

                // Update Xenobe after comnbat
                $armor_lost = $playerinfo['armor_pts'] - $playerarmor;
                $fighters_lost = $playerinfo['ship_fighters'] - $playerfighters;
                $energy = $playerinfo['ship_energy'];
                $update1 = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=?, ship_fighters=ship_fighters-?, armor_pts=armor_pts-?,torps=torps-? WHERE ship_id=?", array($energy, $fighters_lost, $armor_lost, $playertorpnum, $playerinfo['ship_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $update1, __LINE__, __FILE__);

                // Check to see if Xenobe is dead
                if ($playerarmor < 1)
                {
                    $langvars['l_sf_sendlog2'] = str_replace("[player]", "Xenobe " . $playerinfo['character_name'], $langvars['l_sf_sendlog2']);
                    $langvars['l_sf_sendlog2'] = str_replace("[sector]", $targetlink, $langvars['l_sf_sendlog2']);
                    \Tki\SectorDefense::messageDefenseOwner($pdo_db, $db, $targetlink, $langvars['l_sf_sendlog2']);
                    \Tki\Bounty::cancel($pdo_db, $db, $playerinfo['ship_id']);
                    \Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, false);
                    $xenobeisdead = 1;

                    return;
                }

                // Xenobe is still alive, so he hits mines, and logs it
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_playerinfo_character_name]", "Xenobe " . $playerinfo['character_name'], $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_roll]", $roll, $langvars['l_chm_hehitminesinsector']);
                $langvars['l_chm_hehitminesinsector'] = str_replace("[chm_sector]", $targetlink, $langvars['l_chm_hehitminesinsector']);
                \Tki\SectorDefense::messageDefenseOwner($pdo_db, $db, $targetlink, $langvars['l_chm_hehitminesinsector']);

                // Deflectors v. mines
                if (!($playerminedeflect >= $roll))
                {
                    $mines_left = $roll - $playerminedeflect;

                    // Shields v. mines
                    if ($playershields >= $mines_left)
                    {
                        $update2 = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=ship_energy-? WHERE ship_id=?", array($mines_left, $playerinfo['ship_id']));
                        \Tki\Db::logDbErrors($pdo_db, $db, $update2, __LINE__, __FILE__);
                    }
                    else
                    {
                        $mines_left = $mines_left - $playershields;

                        // Armor v. mines
                        if ($playerarmor >= $mines_left)
                        {
                            $update2 = $db->Execute("UPDATE {$db->prefix}ships SET armor_pts=armor_pts-?, ship_energy=0 WHERE ship_id=?", array($mines_left, $playerinfo['ship_id']));
                            \Tki\Db::logDbErrors($pdo_db, $db, $update2, __LINE__, __FILE__);
                        }
                        else
                        {
                            // Xenobe dies, logs the fact that he died
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_playerinfo_character_name]", "Xenobe " . $playerinfo['character_name'], $langvars['l_chm_hewasdestroyedbyyourmines']);
                            $langvars['l_chm_hewasdestroyedbyyourmines'] = str_replace("[chm_sector]", $targetlink, $langvars['l_chm_hewasdestroyedbyyourmines']);
                            \Tki\SectorDefense::messageDefenseOwner($pdo_db, $db, $targetlink, $langvars['l_chm_hewasdestroyedbyyourmines']);

                            // Actually kill the Xenobe now
                            \Tki\Bounty::cancel($pdo_db, $db, $playerinfo['ship_id']);
                            \Tki\Character::kill($pdo_db, $db, $playerinfo['ship_id'], $langvars, $tkireg, false);
                            $xenobeisdead = 1;
                            // Lets get rid of the mines now and return out of this function
                            \Tki\Mines::explode($pdo_db, $db, $targetlink, $roll);

                            return;
                        }
                    }
                }
                \Tki\Mines::explode($pdo_db, $db, $targetlink, $roll); // Dispose of the mines now
            }
            else
            {
                // This was called without any sector defences to attack
                return;
            }
        }
    }

    public static function xenobeMove($pdo_db, $db, $playerinfo, $targetlink, $langvars, $tkireg)
    {
        // Obtain a target link
        if ($targetlink == $playerinfo['sector'])
        {
            $targetlink = 0;
        }

        $linkres = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start=?", array($playerinfo['sector']));
        \Tki\Db::logDbErrors($pdo_db, $db, $linkres, __LINE__, __FILE__);
        if ($linkres instanceof \adodb\ADORecordSet)
        {
            while (!$linkres->EOF)
            {
                $row = $linkres->fields;

                // Obtain sector information
                $sectres = $db->Execute("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id=?", array($row['link_dest']));
                \Tki\Db::logDbErrors($pdo_db, $db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=?", array($sectrow['zone_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $zoneres, __LINE__, __FILE__);
                $zonerow = $zoneres->fields;
                if ($zonerow['allow_attack'] == "Y") // Dest link must allow attacking
                {
                    $setlink = random_int(0, 2);                        // 33% Chance of replacing destination link with this one
                    if ($setlink == 0 || !$targetlink > 0)           // Unless there is no dest link, choose this one
                    {
                        $targetlink = $row['link_dest'];
                    }
                }
                $linkres->MoveNext();
            }
        }

        if (!$targetlink > 0) // If there is no acceptable link, use a worm hole.
        {
            $wormto = random_int(1, (int) ($tkireg->max_sectors - 15));  // Generate a random sector number
            $limitloop = 1;                             // Limit the number of loops
            while (!$targetlink > 0 && $limitloop < 15)
            {
                // Obtain sector information
                $sectres = $db->Execute("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id=?", array($wormto));
                \Tki\Db::logDbErrors($pdo_db, $db, $sectres, __LINE__, __FILE__);
                $sectrow = $sectres->fields;

                $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=?", array($sectrow['zone_id']));
                \Tki\Db::logDbErrors($pdo_db, $db, $zoneres, __LINE__, __FILE__);
                $zonerow = $zoneres->fields;
                if ($zonerow['allow_attack'] == "Y")
                {
                    $targetlink = $wormto;
                    \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Used a wormhole to warp to a zone where attacks are allowed.");
                }
                $wormto++;
                $wormto++;
                $limitloop++;
            }
        }

        if ($targetlink > 0) // Check for sector defenses
        {
            $resultf = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='F' ORDER BY quantity DESC", array($targetlink));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultf, __LINE__, __FILE__);
            $i = 0;
            $total_sector_fighters = 0;
            $defences = array();
            if ($resultf instanceof \adodb\ADORecordSet)
            {
                while (!$resultf->EOF)
                {
                    $defences[$i] = $resultf->fields;
                    $total_sector_fighters += $defences[$i]['quantity'];
                    $i++;
                    $resultf->MoveNext();
                }
            }

            $resultm = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='M'", array($targetlink));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultm, __LINE__, __FILE__);
            $i = 0;
            $total_sector_mines = 0;
            if ($resultm instanceof \adodb\ADORecordSet)
            {
                while (!$resultm->EOF)
                {
                    $defences[$i] = $resultm->fields;
                    $total_sector_mines += $defences[$i]['quantity'];
                    $i++;
                    $resultm->MoveNext();
                }
            }

            if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0)) // If destination link has defences
            {
                if ($playerinfo['aggression'] == 2 || $playerinfo['aggression'] == 1)
                {
                    self::xenobeToSecDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg); // Attack sector defences

                    return;
                }
                else
                {
                    \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Move failed, the sector is defended by $total_sector_fighters fighters and $total_sector_mines mines.");

                    return;
                }
            }
        }

        if ($targetlink > 0) // Move to target link
        {
            $stamp = date("Y-m-d H:i:s");
            $move_result = $db->Execute("UPDATE {$db->prefix}ships SET last_login=?, turns_used=turns_used+1, sector=? WHERE ship_id=?", array($stamp, $targetlink, $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $move_result, __LINE__, __FILE__);
            if (!$move_result)
            {
                $error = $db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Move failed with error: $error ");
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Move failed due to lack of target link."); // We have no target link for some reason
            $targetlink = $playerinfo['sector'];         // Reset target link so it is not zero
        }
    }

    public static function xenobeHunter($pdo_db, $db, $playerinfo, $xenobeisdead, $langvars, $tkireg)
    {
        $rescount = $db->Execute("SELECT COUNT(*) AS num_players FROM {$db->prefix}ships WHERE ship_destroyed='N' AND email NOT LIKE '%@xenobe' AND ship_id > 1");
        \Tki\Db::logDbErrors($pdo_db, $db, $rescount, __LINE__, __FILE__);
        $rowcount = $rescount->fields;
        $topnum = min(10, $rowcount['num_players']);

        // If we have killed all the players in the game then stop here.
        if ($topnum < 1)
        {
            return;
        }

        $res = $db->SelectLimit("SELECT * FROM {$db->prefix}ships WHERE ship_destroyed='N' AND email NOT LIKE '%@xenobe' AND ship_id > 1 ORDER BY score DESC", $topnum);
        \Tki\Db::logDbErrors($pdo_db, $db, $res, __LINE__, __FILE__);

        // Choose a target from the top player list
        $i = 1;
        $targetnum = random_int(1, $topnum);
        while (!$res->EOF)
        {
            if ($i == $targetnum)
            {
                $targetinfo = $res->fields;
            }
            $i++;
            $res->MoveNext();
        }

        // Make sure we have a target
        if (!$targetinfo)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Hunt Failed: No Target ");

            return;
        }

        // Jump to target sector
        $sectres = $db->Execute("SELECT sector_id, zone_id FROM {$db->prefix}universe WHERE sector_id=?", array($targetinfo['sector']));
        \Tki\Db::logDbErrors($pdo_db, $db, $sectres, __LINE__, __FILE__);
        $sectrow = $sectres->fields;

        $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=?", array($sectrow['zone_id']));
        \Tki\Db::logDbErrors($pdo_db, $db, $zoneres, __LINE__, __FILE__);
        $zonerow = $zoneres->fields;

        // Only travel there if we can attack in the target sector
        if ($zonerow['allow_attack'] == "Y")
        {
            $stamp = date("Y-m-d H:i:s");
            $move_result = $db->Execute("UPDATE {$db->prefix}ships SET last_login=?, turns_used=turns_used+1, sector=? WHERE ship_id=?", array($stamp, $targetinfo['sector'], $playerinfo['ship_id']));
            \Tki\Db::logDbErrors($pdo_db, $db, $move_result, __LINE__, __FILE__);
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe used a wormhole to warp to sector $targetinfo[sector] where he is hunting player $targetinfo[character_name].");
            if (!$move_result)
            {
                $error = $db->ErrorMsg();
                \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Move failed with error: $error ");

                return;
            }

            // Check for sector defences
            $resultf = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? AND defence_type ='F' ORDER BY quantity DESC", array($targetinfo['sector']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultf, __LINE__, __FILE__);
            $i = 0;
            $total_sector_fighters = 0;
            $defences = array();
            if ($resultf instanceof \adodb\ADORecordSet)
            {
                while (!$resultf->EOF)
                {
                    $defences[$i] = $resultf->fields;
                    $total_sector_fighters += $defences[$i]['quantity'];
                    $i++;
                    $resultf->MoveNext();
                }
            }

            $resultm = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? AND defence_type ='M'", array($targetinfo['sector']));
            \Tki\Db::logDbErrors($pdo_db, $db, $resultm, __LINE__, __FILE__);
            $i = 0;
            $total_sector_mines = 0;
            if ($resultm instanceof \adodb\ADORecordSet)
            {
                while (!$resultm->EOF)
                {
                    $defences[$i] = $resultm->fields;
                    $total_sector_mines += $defences[$i]['quantity'];
                    $i++;
                    $resultm->MoveNext();
                }
            }

            if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0)) // Destination link has defences
            {
                // Attack sector defences
                $targetlink = $targetinfo['sector'];
                self::xenobeToSecDef($pdo_db, $db, $langvars, $playerinfo, $targetlink, $tkireg);
            }

            if ($xenobeisdead > 0)
            {
                return; // Sector defenses killed the Xenobe
            }

            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe launching an attack on $targetinfo[character_name]."); // Attack the target

            if ($targetinfo['planet_id'] > 0) // Is player target on a planet?
            {
                self::xenobeToPlanet($pdo_db, $db, $targetinfo['planet_id'], $tkireg, $playerinfo, $langvars); // Yes, so move to that planet
            }
            else
            {
                self::xenobeToShip($pdo_db, $db, $targetinfo['ship_id'], $tkireg, $playerinfo, $langvars); // Not on a planet, so move to the ship
            }
        }
        else
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe hunt failed, target $targetinfo[character_name] was in a no attack zone (sector $targetinfo[sector]).");
        }
    }

    public static function xenobeRegen($pdo_db, $db, $playerinfo, $xen_unemployment, $tkireg)
    {
        // Xenobe Unempoyment Check
        $playerinfo['credits'] = $playerinfo['credits'] + $xen_unemployment;
        $maxenergy = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg->level_factor); // Regenerate energy
        if ($playerinfo['ship_energy'] <= ($maxenergy - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['ship_energy'] = $playerinfo['ship_energy'] + round(($maxenergy - $playerinfo['ship_energy']) / 2); // Regen half of remaining energy
            $gene = "regenerated Energy to $playerinfo[ship_energy] units,";
        }

        $maxarmor = \Tki\CalcLevels::armor($playerinfo['armor'], $tkireg->level_factor); // Regenerate armor
        if ($playerinfo['armor_pts'] <= ($maxarmor - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['armor_pts'] = $playerinfo['armor_pts'] + round(($maxarmor - $playerinfo['armor_pts']) / 2); // Regen half of remaining armor
            $gena = "regenerated Armor to $playerinfo[armor_pts] points,";
        }

        // Buy fighters & torpedos at 6 credits per fighter
        $available_fighters = \Tki\CalcLevels::fighters($playerinfo['computer'], $tkireg->level_factor) - $playerinfo['ship_fighters'];
        if (($playerinfo['credits'] > 5) && ($available_fighters > 0))
        {
            if (round($playerinfo['credits'] / 6) > $available_fighters)
            {
                $purchase = ($available_fighters * 6);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $available_fighters;
                $genf = "purchased $available_fighters fighters for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 6) <= $available_fighters)
            {
                $purchase = (round($playerinfo['credits'] / 6));
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $purchase;
                $genf = "purchased $purchase fighters for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Xenobe pay 3 credits per torpedo
        $available_torpedoes = \Tki\CalcLevels::torpedoes($playerinfo['torp_launchers'], $tkireg->level_factor) - $playerinfo['torps'];
        if (($playerinfo['credits'] > 2) && ($available_torpedoes > 0))
        {
            if (round($playerinfo['credits'] / 3) > $available_torpedoes)
            {
                $purchase = ($available_torpedoes * 3);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['torps'] = $playerinfo['torps'] + $available_torpedoes;
                $gent = "purchased $available_torpedoes torpedoes for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 3) <= $available_torpedoes)
            {
                $purchase = (round($playerinfo['credits'] / 3));
                $playerinfo['torps'] = $playerinfo['torps'] + $purchase;
                $gent = "purchased $purchase torpedoes for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Update Xenobe record
        $resg = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=?, armor_pts=?, ship_fighters=?, torps=?, credits=? WHERE ship_id=?", array($playerinfo['ship_energy'], $playerinfo['armor_pts'], $playerinfo['ship_fighters'], $playerinfo['torps'], $playerinfo['credits'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $db, $resg, __LINE__, __FILE__);
        if (!$gene === null || !$gena === null || !$genf === null || !$gent === null)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $db, $playerinfo['ship_id'], LOG_RAW, "Xenobe $gene $gena $genf $gent and has been updated.");
        }
    }
}
