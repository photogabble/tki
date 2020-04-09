<?php declare(strict_types = 1);
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
// File: genesis.php

// If anyone who's coded this thing is willing to update it to
// support multiple planets, go ahead. I suggest removing this
// code completely from here and putting it in the planet menu
// instead. Easier to manage, makes more sense too.

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('genesis', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
$title = $langvars['l_gns_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db); // Build a sector gateway object to handle the SQL calls
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

// Get planetinfo from database
$planets_gateway = new \Tki\Planets\PlanetsGateway($pdo_db); // Build a planet gateway object to handle the SQL calls
$planetinfo = $planets_gateway->selectPlanetInfo($playerinfo['sector']);
if ($planetinfo !== null && $planetinfo !== false)
{
    $num_planets = count($planetinfo);
}
else
{
    $num_planets = 0;
}

// Generate Planetname
$planetname = substr($playerinfo['character_name'], 0, 1) . substr($playerinfo['ship_name'], 0, 1) . "-" . $playerinfo['sector'] . "-" . ($num_planets + 1);

echo "<h1>" . $title . "</h1>\n";

$destroy = null;
if (array_key_exists('destroy', $_GET))
{
    $destroy = $_GET['destroy'];
}

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_gns_turn'];
}
elseif ($playerinfo['on_planet'] == 'Y')
{
    echo $langvars['l_gns_onplanet'];
}
elseif ($num_planets >= $tkireg->max_planets_sector)
{
    echo $langvars['l_gns_full'];
}
elseif ($sectorinfo['sector_id'] >= $tkireg->max_sectors)
{
    echo "Invalid sector<br>\n";
}
elseif ($playerinfo['dev_genesis'] < 1)
{
    echo $langvars['l_gns_nogenesis'];
}
else
{
    $res = $db->Execute("SELECT allow_planet, team_zone, owner FROM {$db->prefix}zones WHERE zone_id = ?;", array($sectorinfo['zone_id']));
    Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_planet'] == 'N')
    {
        echo $langvars['l_gns_forbid'];
    }
    elseif ($zoneinfo['allow_planet'] == 'L')
    {
        if ($zoneinfo['team_zone'] == 'N')
        {
            if ($playerinfo['team'] == 0 && $zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                echo $langvars['l_gns_bforbid'];
            }
            else
            {
                $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id = ?;", array($zoneinfo['owner']));
                Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                $ownerinfo = $res->fields;
                if ($ownerinfo['team'] != $playerinfo['team'])
                {
                    echo $langvars['l_gns_bforbid'];
                }
                else
                {
                    $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(null, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
                    Tki\Db::logDbErrors($pdo_db, $update1, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=:ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $result = $stmt->execute();
                    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    echo $langvars['l_gns_pcreate'];
                }
            }
        }
        elseif ($playerinfo['team'] != $zoneinfo['owner'])
        {
            echo $langvars['l_gns_bforbid'];
        }
        else
        {
            $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(null, $playerinfo['sector'], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
            Tki\Db::logDbErrors($pdo_db, $update1, __LINE__, __FILE__);

            $sql = "UPDATE ::prefix::ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=:ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            echo $langvars['l_gns_pcreate'];
        }
    }
    else
    {
        $update1 = $db->Execute("INSERT INTO {$db->prefix}planets VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);", array(null, $playerinfo['sector'], $planetname, 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo['ship_id'], 0, 'N', 'N', $tkireg->default_prod_organics, $tkireg->default_prod_ore, $tkireg->default_prod_goods, $tkireg->default_prod_energy, $tkireg->default_prod_fighters, $tkireg->default_prod_torp, 'N'));
        Tki\Db::logDbErrors($pdo_db, $update1, __LINE__, __FILE__);

        $sql = "UPDATE ::prefix::ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=:ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $result = $stmt->execute();
        Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
        echo $langvars['l_gns_pcreate'];
    }
}

echo "<br><br>";

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
