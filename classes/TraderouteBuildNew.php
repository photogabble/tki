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
// File: classes/TraderouteBuildNew.php

namespace Tki;

class TraderouteBuildNew
{
    public static function new(\PDO $pdo_db, $db, string $lang, Reg $tkireg, Smarty $template, int $num_traderoutes, array $playerinfo, ?int $traderoute_id = null): void
    {
        $langvars = \Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer'));
        $editroute = null;

        if ($traderoute_id !== null)
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE traderoute_id = ?;", array($traderoute_id));
            \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

            if (!$result || $result->EOF)
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_editerr']);
            }

            $editroute = $result->fields;

            if ($editroute['owner'] != $playerinfo['ship_id'])
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_notowner']);
            }
        }

        if ($num_traderoutes >= $tkireg->max_traderoutes_player && ($editroute === null))
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, '<p>' . $langvars['l_tdr_maxtdr'].'<p>');
        }

        echo "<p><font size=3 color=blue><strong>";

        if ($editroute === null)
        {
            echo $langvars['l_tdr_createnew'];
        }
        else
        {
            echo $langvars['l_tdr_editinga'] . " ";
        }

        echo $langvars['l_tdr_traderoute'] . "</strong></font><p>";

        // Get Planet info Team and Personal

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner = ? ORDER BY sector_id", array($playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

        $num_planets = $result->RecordCount();
        $i = 0;
        $planets = array();
        while (!$result->EOF)
        {
            $planets[$i] = $result->fields;

            if ($planets[$i]['name'] === null)
            {
                $planets[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        $result = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE team = ? AND team <> 0 AND owner <> ? ORDER BY sector_id", array($playerinfo['team'], $playerinfo['ship_id']));
        \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

        $num_team_planets = $result->RecordCount();
        $i = 0;
        $planets_team = array();
        while (!$result->EOF)
        {
            $planets_team[$i] = $result->fields;

            if ($planets_team[$i]['name'] === null)
            {
                $planets_team[$i]['name'] = $langvars['l_tdr_unnamed'];
            }

            $i++;
            $result->MoveNext();
        }

        // Display Current Sector
        echo $langvars['l_tdr_cursector'] . " " . $playerinfo['sector'] . "<br>";

        // Start of form for starting location
        echo "
            <form accept-charset='utf-8' action=traderoute.php?command=create method=post>
            <table border=0><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selspoint'] . " <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"port\"
            ";

        if (($editroute === null) || ($editroute !== null) && $editroute['source_type'] == 'P')
        {
            echo " checked";
        }

        echo "
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id1 size=20 align='center'
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'P')
        {
            echo " value=\"$editroute[source_id]\"";
        }

        echo "
            ></td>
            </tr><tr>
            ";

        // Personal Planet
        echo "
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"planet\"
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id1>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Team Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Team " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype1\" value=\"team_planet\"
            ";

        if (($editroute !== null) && $editroute['source_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=team_planet_id1>
            ';

        if ($num_team_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$i]['planet_id'] == $editroute['source_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$i]['planet_id'] . ">" . $planets_team[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        echo "
            </select>
            </tr>";

        // Begin Ending point selection
        echo "
            <tr><td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selendpoint'] . " : <br>&nbsp;</strong></font></td>
            <tr>
            <td align=right><font size=2>" . $langvars['l_tdr_port'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"port\"
            ";

        if (($editroute === null) || ($editroute !== null && $editroute['dest_type'] == 'P'))
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<input type=text name=port_id2 size=20 align="center"
            ';

        if ($editroute !== null && $editroute['dest_type'] == 'P')
        {
            echo " value=\"$editroute[dest_id]\"";
        }

        echo "
            ></td>
            </tr>";

        // Personal Planet
        echo "
            <tr>
            <td align=right><font size=2>Personal " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"planet\"
            ";

        if ($editroute !== null && $editroute['dest_type'] == 'L')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=planet_id2>
            ';

        if ($num_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_planets)
            {
                echo "<option ";

                if ($planets[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets[$i]['planet_id'] . ">" . $planets[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        // Team Planet
        echo "
            </tr><tr>
            <td align=right><font size=2>Team " . $langvars['l_tdr_planet'] . " : </font></td>
            <td><input type=radio name=\"ptype2\" value=\"team_planet\"
            ";

        if ($editroute !== null && $editroute['dest_type'] == 'C')
        {
            echo " checked";
        }

        echo '
            ></td>
            <td>&nbsp;&nbsp;<select name=team_planet_id2>
            ';

        if ($num_team_planets == 0)
        {
            echo "<option value=none>" . $langvars['l_tdr_none'] . "</option>";
        }
        else
        {
            $i = 0;
            while ($i < $num_team_planets)
            {
                echo "<option ";

                if ($planets_team[$i]['planet_id'] == $editroute['dest_id'])
                {
                    echo "selected ";
                }

                echo "value=" . $planets_team[$i]['planet_id'] . ">" . $planets_team[$i]['name'] . " " . $langvars['l_tdr_insector'] . " " . $planets_team[$i]['sector_id'] . "</option>";
                $i++;
            }
        }

        echo "
            </select>
            </tr>";

        echo "
            </select>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selmovetype'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"move_type\" value=\"realspace\"
            ";

        if ($editroute === null || ($editroute !== null && $editroute['move_type'] == 'R'))
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_realspace'] . "&nbsp;&nbsp<font size=2><input type=radio name=\"move_type\" value=\"warp\"
            ";

        if ($editroute !== null && $editroute['move_type'] == 'W')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_warp'] . "</font></td>
            </tr><tr>
            <td align=right><font size=2><strong>" . $langvars['l_tdr_selcircuit'] . " : </strong></font></td>
            <td colspan=2 valign=top><font size=2><input type=radio name=\"circuit_type\" value=\"1\"
            ";

        if (($editroute === null) || ($editroute !== null) && $editroute['circuit'] == '1')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_oneway'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=\"circuit_type\" value=\"2\"
            ";

        if ($editroute !== null && $editroute['circuit'] == '2')
        {
            echo " checked";
        }

        echo "
            >&nbsp;" . $langvars['l_tdr_bothways'] . "</font></td>
            </tr><tr>
            <td>&nbsp;
            </tr><tr>
            <td><td><td align='center'>
            ";

        if ($editroute === null)
        {
            echo "<input type=submit value=\"" . $langvars['l_tdr_create'] . "\">";
        }
        else
        {
            echo "<input type=hidden name=editing value=$editroute[traderoute_id]>";
            echo "<input type=submit value=\"" . $langvars['l_tdr_modify'] . "\">";
        }

        $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);

        echo "
            </table>
            " . $langvars['l_tdr_returnmenu'] . "<br>
            </form>
            ";

        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotoMain($pdo_db, $lang);
        echo "</div>\n";

        $footer = new \Tki\Footer;
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }
}