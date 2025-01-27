<?php
// declare(strict_types = 1);
/**
 * traderoute.php from The Kabal Invasion.
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

require_once './common.php';
$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('bounty', 'common',
                                'footer', 'insignias', 'regional',
                                'traderoutes', 'universal'));
$title = $langvars['l_tdr_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$portfull = null; // This fixes an error of undefined variables on 1518

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$result = $old_db->Execute("SELECT * FROM {$old_db->prefix}traderoutes WHERE owner = ?;", array($playerinfo['ship_id']));
Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
$num_traderoutes = $result->RecordCount();

$traderoutes = array();
$i = 0;
while (!$result->EOF)
{
    $i = array_push($traderoutes, $result->fields);
    // $traderoutes[$i] = $result->fields;
    // $i++;
    $result->MoveNext();
}

$freeholds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
$maxholds = Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg);
$maxenergy = Tki\CalcLevels::energy($playerinfo['power'], $tkireg);
$admin_log = new Tki\AdminLog();
if ($playerinfo['ship_colonists'] < 0 || $playerinfo['ship_ore'] < 0 || $playerinfo['ship_organics'] < 0 || $playerinfo['ship_goods'] < 0 || $playerinfo['ship_energy'] < 0 || $freeholds < 0)
{
    if ($playerinfo['ship_colonists'] < 0 || $playerinfo['ship_colonists'] > $maxholds)
    {
        $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_colonists]|colonists|$maxholds");
        $playerinfo['ship_colonists'] = 0;
    }

    if ($playerinfo['ship_ore'] < 0 || $playerinfo['ship_ore'] > $maxholds)
    {
        $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_ore]|ore|$maxholds");
        $playerinfo['ship_ore'] = 0;
    }

    if ($playerinfo['ship_organics'] < 0 || $playerinfo['ship_organics'] > $maxholds)
    {
        $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_organics]|organics|$maxholds");
        $playerinfo['ship_organics'] = 0;
    }

    if ($playerinfo['ship_goods'] < 0 || $playerinfo['ship_goods'] > $maxholds)
    {
        $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_goods]|goods|$maxholds");
        $playerinfo['ship_goods'] = 0;
    }

    if ($playerinfo['ship_energy'] < 0 || $playerinfo['ship_energy'] > $maxenergy)
    {
        $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_energy]|energy|$maxenergy");
        $playerinfo['ship_energy'] = 0;
    }

    if ($freeholds < 0)
    {
        $freeholds = 0;
    }

    $sql = "UPDATE ::prefix::ships SET ship_ore = :ship_ore, ship_organics = :ship_organics, ship_goods = :ship_goods, ship_energy = :ship_energy, ship_colonists = :ship_colonists WHERE ship_id = :ship_id";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':ship_ore', $playerinfo['ship_ore'], \PDO::PARAM_INT);
    $stmt->bindParam(':ship_organics', $playerinfo['ship_organics'], \PDO::PARAM_INT);
    $stmt->bindParam(':ship_goods', $playerinfo['ship_goods'], \PDO::PARAM_INT);
    $stmt->bindParam(':ship_energy', $playerinfo['ship_energy'], \PDO::PARAM_INT);
    $stmt->bindParam(':ship_colonists', $playerinfo['ship_colonists'], \PDO::PARAM_INT);
    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
    $result = $stmt->execute();

    Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
}

// Default to 1 run if we don't get a valid repeat value.
$tr_repeat = 1;

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$tr_repeat = null;
$tr_repeat = filter_input(INPUT_POST, 'tr_repeat', FILTER_VALIDATE_INT);
if (($tr_repeat === null) || (strlen(trim($tr_repeat)) === 0))
{
    $tr_repeat = 1;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);
if (($command === null) || (strlen(trim($command)) === 0))
{
    $command = false;
}

$engage = null;
$engage = filter_input(INPUT_GET, 'engage', FILTER_VALIDATE_INT);
if (($engage === null) || (strlen(trim($engage)) === 0))
{
    $engage = false;
}

$ptype1 = null;
$ptype1 = filter_input(INPUT_POST, 'ptype1', FILTER_SANITIZE_STRING);
if (($ptype1 === null) || (strlen(trim($ptype1)) === 0))
{
    $ptype1 = false;
}

$ptype2 = null;
$ptype2 = filter_input(INPUT_POST, 'ptype2', FILTER_SANITIZE_STRING);
if (($ptype2 === null) || (strlen(trim($ptype2)) === 0))
{
    $ptype2 = false;
}

$port_id1 = null;
$port_id1 = filter_input(INPUT_POST, 'port_id1', FILTER_SANITIZE_STRING);
if (($port_id1 === null) || (strlen(trim($port_id1)) === 0))
{
    $port_id1 = false;
}

$port_id2 = null;
$port_id2 = filter_input(INPUT_POST, 'port_id2', FILTER_SANITIZE_STRING);
if (($port_id2 === null) || (strlen(trim($port_id2)) === 0))
{
    $port_id2 = false;
}


$team_planet_id1 = filter_input(INPUT_POST, 'team_planet_id1', FILTER_SANITIZE_NUMBER_INT);
if (($team_planet_id1 === null) || (strlen(trim($team_planet_id1)) === 0))
{
    $team_planet_id1 = false;
}

$team_planet_id2 = filter_input(INPUT_POST, 'team_planet_id2', FILTER_SANITIZE_NUMBER_INT);
if (($team_planet_id2 === null) || (strlen(trim($team_planet_id2)) === 0))
{
    $team_planet_id2 = false;
}

$planet_id1 = null;
$planet_id1 = filter_input(INPUT_POST, 'planet_id1', FILTER_SANITIZE_NUMBER_INT);
if (($planet_id1 === null) || (strlen(trim($planet_id1)) === 0))
{
    $planet_id1 = null;
}

$planet_id2 = null;
$planet_id2 = filter_input(INPUT_POST, 'planet_id2', FILTER_SANITIZE_NUMBER_INT);
if (($planet_id2 === null) || (strlen(trim($planet_id2)) === 0))
{
    $planet_id2 = null;
}

$move_type = null;
$move_type = filter_input(INPUT_POST, 'move_type', FILTER_SANITIZE_STRING);
if (($move_type === null) || (strlen(trim($move_type)) === 0))
{
    $move_type = false;
}

$circuit_type = null;
$circuit_type = filter_input(INPUT_POST, 'circuit_type', FILTER_SANITIZE_NUMBER_INT);
if (($circuit_type === null) || (strlen(trim($circuit_type)) === 0))
{
    $circuit_type = 2;
}

$editing = null;
$editing = filter_input(INPUT_POST, 'editing', FILTER_SANITIZE_STRING);
if (($editing === null) || (strlen(trim($editing)) === 0))
{
    $editing = false;
}

$traderoute_id = null;
$traderoute_id = filter_input(INPUT_GET, 'traderoute_id', FILTER_SANITIZE_STRING);
if (($traderoute_id === null) || (strlen(trim($traderoute_id)) === 0))
{
    $traderoute_id = false;
}

$confirm = null;
$confirm = filter_input(INPUT_GET, 'confirm', FILTER_SANITIZE_STRING);
if (($confirm === null) || (strlen(trim($confirm)) === 0))
{
    $confirm = false;
}

if ($command == 'new')
{
    // Displays new trade route form
    \Tki\TraderouteBuildNew::new($pdo_db, $lang, $tkireg, $tkitimer, $template, $num_traderoutes, $playerinfo, null);
}
elseif ($command == 'edit')
{
    // Displays new trade route form, edit
    \Tki\TraderouteBuildNew::new($pdo_db, $lang, $tkireg, $tkitimer, $template, $num_traderoutes, $playerinfo, $traderoute_id);
}
elseif ($command == 'create')
{
    // Enters new route in db
    \Tki\TraderouteBuildCreate::create($pdo_db, $lang, $tkireg, $tkitimer, $template, $playerinfo, $num_traderoutes, $ptype1, $ptype2, $port_id1, $port_id2, $team_planet_id1, $team_planet_id2, $move_type, $circuit_type, $editing, $planet_id1, $planet_id2);
}
elseif ($command == 'delete')
{
    // Displays delete info
    \Tki\TraderouteDelete::prime($pdo_db, $lang, $langvars, $tkireg, $tkitimer, $template, $playerinfo, $confirm, $traderoute_id);
}
elseif ($command == 'settings')
{
    // Global traderoute settings form
    \Tki\TraderouteSettings::before($pdo_db, $lang, $tkireg, $tkitimer, $template, $playerinfo);
}
elseif ($command == 'setsettings')
{
    // Enters settings in db
    \Tki\TraderouteSettings::after($pdo_db, $playerinfo, $colonists, $fighters, $torps, $energy);

    // Display outcome
    \Tki\TraderouteSettings::afterOutput($pdo_db, $lang, $tkireg, $tkitimer, $template);
}
elseif ($engage !== null)
{
    // Perform trade route
    while ($tr_repeat > 0)
    {
        // Get playerinfo from database
        $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
        $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);
        \Tki\Traderoute::engage($pdo_db, $lang, $tr_repeat, $tkireg, $tkitimer, $playerinfo, $engage, $traderoutes, $portfull, $template);
        $tr_repeat--;
    }
}

if ($command != 'delete')
{
    $langvars['l_tdr_newtdr'] = str_replace("[here]", "<a href='traderoute.php?command=new'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_newtdr']);
    echo "<p>" . $langvars['l_tdr_newtdr'] . "<p>";
    $langvars['l_tdr_modtdrset'] = str_replace("[here]", "<a href='traderoute.php?command=settings'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_modtdrset']);
    echo "<p>" . $langvars['l_tdr_modtdrset'] . "<p>";
}
else
{
    $langvars['l_tdr_confdel'] = str_replace("[here]", "<a href='traderoute.php?command=delete&amp;confirm=yes&amp;traderoute_id=" . $traderoute_id . "'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_confdel']);
    echo "<p>" . $langvars['l_tdr_confdel'] . "<p>";
}

$port1 = null;
$port2 = null;
$planet1 = null;
$planet2 = null;

if ($num_traderoutes == 0)
{
    echo $langvars['l_tdr_noactive'] . "<p>";
}
else
{
    echo '<table border=1 cellspacing=1 cellpadding=2 width="100%" align="center">' .
         '<tr bgcolor=' . $tkireg->color_line2 . '><td align="center" colspan=7><strong><font color=white>
         ';

    if ($command != 'delete')
    {
        echo $langvars['l_tdr_curtdr'];
    }
    else
    {
        echo $langvars['l_tdr_deltdr'];
    }

    echo "</font></strong>" .
         "</td></tr>" .
         "<tr align='center' bgcolor='" . $tkireg->color_line2 . "'>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_src'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_srctype'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_dest'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_desttype'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_move'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_circuit'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_change'] . "</strong></font></td>" .
         "</tr>";

    $i = 0;
    $curcolor = $tkireg->color_line1;
    while ($i < $num_traderoutes)
    {
        echo "<tr bgcolor='" . $curcolor . "'>";
        if ($curcolor == $tkireg->color_line1)
        {
            $curcolor = $tkireg->color_line2;
        }
        else
        {
            $curcolor = $tkireg->color_line1;
        }

        echo "<td><font size=2 color=white>";
        if ($traderoutes[$i]['source_type'] == 'P')
        {
            echo "&nbsp;" . $langvars['l_tdr_portin'] . " <a href=rsmove.php?engage=1&destination=" . $traderoutes[$i]['source_id'] . ">" . $traderoutes[$i]['source_id'] . "</a></font></td>";
        }
        else
        {
            $result = $old_db->Execute("SELECT name, sector_id FROM {$old_db->prefix}planets WHERE planet_id=?;", array($traderoutes[$i]['source_id']));
            Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
            if ($result)
            {
                $planet1 = $result->fields;
                echo "&nbsp;" . $langvars['l_tdr_planet'] . " <strong>$planet1[name]</strong>" . $langvars['l_tdr_within'] . "<a href=\"rsmove.php?engage=1&destination=" . $planet1['sector_id'] . "\">" . $planet1['sector_id'] . "</a></font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_nonexistance'] . "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['source_type'] == 'P')
        {
            // Get sectorinfo from database
            $sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
            $port1 = $sectors_gateway->selectSectorInfo($traderoutes[$i]['source_id']);
            echo "&nbsp;" . Tki\Ports::getType($pdo_db, $lang, $port1['port_type']) . "</font></td>";
        }
        else
        {
            if (empty($planet1))
            {
                echo "&nbsp;" . $langvars['l_tdr_na'] . "</font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_cargo'] . "</font></td>";
            }
        }

        echo "<td><font size=2 color=white>";

        if ($traderoutes[$i]['dest_type'] == 'P')
        {
            echo "&nbsp;" . $langvars['l_tdr_portin'] . " <a href=\"rsmove.php?engage=1&destination=" . $traderoutes[$i]['dest_id'] . "\">" . $traderoutes[$i]['dest_id'] . "</a></font></td>";
        }
        else
        {
            $result = $old_db->Execute("SELECT name, sector_id FROM {$old_db->prefix}planets WHERE planet_id=?;", array($traderoutes[$i]['dest_id']));
            Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);
            if ($result)
            {
                $planet2 = $result->fields;
                echo "&nbsp;" . $langvars['l_tdr_planet'] . " <strong>$planet2[name]</strong>" . $langvars['l_tdr_within'] . "<a href=\"rsmove.php?engage=1&destination=" . $planet2['sector_id'] . "\">" . $planet2['sector_id'] . "</a></font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_nonexistance'] . "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['dest_type'] == 'P')
        {
            // Get sectorinfo from database
            $sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
            $port2 = $sectors_gateway->selectSectorInfo($traderoutes[$i]['dest_id']);
            echo "&nbsp;" . Tki\Ports::getType($pdo_db, $lang, $port2['port_type']) . "</font></td>";
        }
        else
        {
            if (empty($planet2))
            {
                echo "&nbsp;" . $langvars['l_tdr_na'] . "</font></td>";
            }
            else
            {
                echo "&nbsp;";
                if ($playerinfo['trade_colonists'] == 'N' && $playerinfo['trade_fighters'] == 'N' && $playerinfo['trade_torps'] == 'N')
                {
                    echo $langvars['l_tdr_none'];
                }
                else
                {
                    if ($playerinfo['trade_colonists'] == 'Y')
                    {
                        echo $langvars['l_tdr_colonists'];
                    }

                    if ($playerinfo['trade_fighters'] == 'Y')
                    {
                        if ($playerinfo['trade_colonists'] == 'Y')
                        {
                            echo ", ";
                        }

                        echo $langvars['l_tdr_fighters'];
                    }

                    if ($playerinfo['trade_torps'] == 'Y')
                    {
                        echo "<br>" . $langvars['l_tdr_torps'];
                    }
                }

                echo "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['move_type'] == 'R')
        {
            echo "&nbsp;RS, ";

            if ($traderoutes[$i]['source_type'] == 'P')
            {
                $src = $port1;
            }
            else
            {
                $src = $planet1['sector_id'];
            }

            if ($traderoutes[$i]['dest_type'] == 'P')
            {
                $dst = $port2;
            }
            else
            {
                $dst = $planet2['sector_id'];
            }

            $dist = \Tki\TraderouteDistance::calc($pdo_db, $traderoutes[$i]['source_type'], $traderoutes[$i]['dest_type'], $src, $dst, $traderoutes[$i]['circuit'], $playerinfo, $tkireg);

            $langvars['l_tdr_escooped_temp'] = str_replace("[tdr_dist_triptime]", $dist['triptime'], $langvars['l_tdr_escooped']);
            $langvars['l_tdr_escooped2_temp'] = str_replace("[tdr_dist_scooped]", $dist['scooped'], $langvars['l_tdr_escooped2']);
            echo $langvars['l_tdr_escooped_temp'] . "<br>" . $langvars['l_tdr_escooped2_temp'];

            echo "</font></td>";
        }
        else
        {
            echo "&nbsp;" . $langvars['l_tdr_warp'];

            if ($traderoutes[$i]['circuit'] == '1')
            {
                echo ", 2 " . $langvars['l_tdr_turns'];
            }
            else
            {
                echo ", 4 " . $langvars['l_tdr_turns'];
            }

            echo "</font></td>";
        }

        echo "<td align='center'><font size=2 color=white>";

        if ($traderoutes[$i]['circuit'] == '1')
        {
            echo "&nbsp;1 " . $langvars['l_tdr_way'] . "</font></td>";
        }
        else
        {
            echo "&nbsp;2 " . $langvars['l_tdr_ways'] . "</font></td>";
        }

        echo "<td align='center'><font size=2 color=white>";
        echo "<a href=\"traderoute.php?command=edit&traderoute_id=" . $traderoutes[$i]['traderoute_id'] . "\">";
        echo $langvars['l_edit'] . "</a><br><a href=\"traderoute.php?command=delete&traderoute_id=" . $traderoutes[$i]['traderoute_id'] . "\">";
        echo $langvars['l_tdr_del'] . "</a></font></td></tr>";

        $i++;
    }

    echo "</table><p>";
}

echo "<div style='text-align:left;'>\n";
Tki\Text::gotoMain($pdo_db, $lang);
echo "</div>\n";

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
