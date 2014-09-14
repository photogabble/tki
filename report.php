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
// File: report.php

require_once './common.php';

Bnt\Login::checkLogin($pdo_db, $lang, $langvars, $bntreg, $template);

// Database driven language entries
$langvars = Bnt\Translate::load($pdo_db, $lang, array('main', 'report', 'device', 'common', 'global_includes', 'global_funcs', 'footer', 'regional'));

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
Bnt\Db::logDbErrors($db, $result, __LINE__, __FILE__);
$playerinfo = $result->fields;

$shiptypes[0] = "tinyship.png";
$shiptypes[1] = "smallship.png";
$shiptypes[2] = "mediumship.png";
$shiptypes[3] = "largeship.png";
$shiptypes[4] = "hugeship.png";

$shipavg = Bnt\CalcLevels::avgTech($playerinfo, "ship");

if ($shipavg < 8)
{
    $shiplevel = 0;
}
elseif ($shipavg < 12)
{
    $shiplevel = 1;
}
elseif ($shipavg < 16)
{
    $shiplevel = 2;
}
elseif ($shipavg < 20)
{
    $shiplevel = 3;
}
else
{
    $shiplevel = 4;
}

$holds_used = $playerinfo['ship_ore'] + $playerinfo['ship_organics'] + $playerinfo['ship_goods'] + $playerinfo['ship_colonists'];
$holds_max = Bnt\CalcLevels::holds($playerinfo['hull'], $bntreg->level_factor);
$armor_pts_max = Bnt\CalcLevels::armor($playerinfo['armor'], $bntreg->level_factor);
$ship_fighters_max = Bnt\CalcLevels::fighters($playerinfo['computer'], $bntreg->level_factor);
$torps_max = Bnt\CalcLevels::torpedoes($playerinfo['torp_launchers'], $bntreg->level_factor);
$energy_max = Bnt\CalcLevels::energy($playerinfo['power'], $bntreg->level_factor);
$escape_pod = ($playerinfo['dev_escapepod'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no'];
$fuel_scoop = ($playerinfo['dev_fuelscoop'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no'];
$lssd = ($playerinfo['dev_lssd'] == 'Y') ? $langvars['l_yes'] : $langvars['l_no'];

// Clear variables array before use, and set array with all used variables in page
$variables = null;
$variables['body_class'] = 'bnt'; // No special CSS
$variables['lang'] = $lang;
$variables['color_header'] = $bntreg->color_header;
$variables['color_line1'] = $bntreg->color_line1;
$variables['color_line2'] = $bntreg->color_line2;
$variables['playerinfo_character_name'] = $playerinfo['character_name'];
$variables['playerinfo_ship_name'] = $playerinfo['ship_name'];
$variables['playerinfo_credits'] = $playerinfo['credits'];
$variables['playerinfo_hull'] = $playerinfo['hull'];
$variables['playerinfo_engines'] = $playerinfo['engines'];
$variables['playerinfo_computer'] = $playerinfo['computer'];
$variables['playerinfo_sensors'] = $playerinfo['sensors'];
$variables['playerinfo_armor'] = $playerinfo['armor'];
$variables['playerinfo_shields'] = $playerinfo['shields'];
$variables['playerinfo_beams'] = $playerinfo['beams'];
$variables['playerinfo_power'] = $playerinfo['power'];
$variables['playerinfo_torp_launchers'] = $playerinfo['torp_launchers'];
$variables['playerinfo_cloak'] = $playerinfo['cloak'];
$variables['shipavg'] = $shipavg;
$variables['holds_used'] = $holds_used;
$variables['holds_max'] = $holds_max;
$variables['playerinfo_ship_ore'] = $playerinfo['ship_ore'];
$variables['playerinfo_ship_organics'] = $playerinfo['ship_organics'];
$variables['playerinfo_ship_goods'] = $playerinfo['ship_goods'];
$variables['playerinfo_ship_energy'] = $playerinfo['ship_energy'];
$variables['playerinfo_ship_colonists'] = $playerinfo['ship_colonists'];
$variables['playerinfo_ship_fighters'] = $playerinfo['ship_fighters'];
$variables['playerinfo_armor_pts'] = $playerinfo['armor_pts'];
$variables['playerinfo_torps'] = $playerinfo['torps'];
$variables['torps_max'] = $torps_max;
$variables['energy_max'] = $energy_max;
$variables['armor_pts_max'] = $armor_pts_max;
$variables['ship_fighters_max'] = $ship_fighters_max;
$variables['playerinfo_dev_beacon'] = $playerinfo['dev_beacon'];
$variables['playerinfo_dev_warpedit'] = $playerinfo['dev_warpedit'];
$variables['playerinfo_dev_genesis'] = $playerinfo['dev_genesis'];
$variables['playerinfo_dev_minedeflector'] = $playerinfo['dev_minedeflector'];
$variables['playerinfo_dev_emerwarp'] = $playerinfo['dev_emerwarp'];
$variables['escape_pod'] = $escape_pod;
$variables['fuel_scoop'] = $fuel_scoop;
$variables['lssd'] = $lssd;
$variables['ship_img'] = $template->getVariables('template_dir') . "/images/" . $shiptypes[$shiplevel];
$variables['linkback'] = array("fulltext"=>$langvars['l_global_mmenu'], "link"=>"main.php");

// Now set a container for the variables and langvars and send them off to the template system
$variables['container'] = "variable";
$langvars['container'] = "langvar";

// Pull in footer variables from footer_t.php
require_once './footer_t.php';
$langvars = Bnt\Translate::load($pdo_db, $lang, array('main', 'report', 'device', 'common', 'global_includes', 'global_funcs', 'footer', 'regional', 'news'));
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('report.tpl');
