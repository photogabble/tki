<?php declare(strict_types = 1);
/**
 * classes/Traderoute.php from The Kabal Invasion.
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

namespace Tki;

class Traderoute
{
    public static function engage(\PDO $pdo_db, string $lang, int $tr_repeat, Registry $tkireg, Timer $tkitimer, array $playerinfo, int $engage, array $traderoutes, ?int $portfull, Smarty $template): void
    {
        // Registry values can't be directly updated
        $ore_price = $tkireg->ore_price;
        $organics_price = $tkireg->organics_price;
        $goods_price = $tkireg->goods_price;
        $energy_price = $tkireg->energy_price;

        $langvars = Translate::load($pdo_db, $lang, array('regional', 'traderoutes'));
        $traderoute = array();
        $source = array();
        $dest = array();

        // Added below initializations, for traderoute bug
        $ore_buy = 0;
        $goods_buy = 0;
        $organics_buy = 0;
        $energy_buy = 0;
        $colonists_buy = 0;
        $fighters_buy = 0;
        $torps_buy = 0;

        foreach ($traderoutes as $testroute)
        {
            if ($testroute['traderoute_id'] == $engage)
            {
                $traderoute = $testroute;
            }
        }

        if (!isset($traderoute))
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_engagenonexist']);
        }

        if (array_key_exists('owner', $traderoute))
        {
            if ($traderoute['owner'] !== $playerinfo['ship_id'])
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notowntdr']);
            }
        }

        // Source check
        if ($traderoute['source_type'] == 'P')
        {
            // Retrieve port info here, we'll need it later anyway
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id = :traderoute_source_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $traderoute['source_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $source = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($source))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invalidspoint']);
            }

            if ($traderoute['source_id'] != $playerinfo['sector'])
            {
                $langvars['l_tdr_inittdr'] = str_replace("[tdr_source_id]", $traderoute['source_id'], $langvars['l_tdr_inittdr']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_inittdr']);
            }
        }
        elseif ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')  // Get data from planet table
        {
            $sql = "SELECT * FROM ::prefix::planets WHERE planet_id = :traderoute_source_id AND (owner = :playerinfo_ship_id OR (team <> 0 AND team = :playerinfo_team";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':traderoute_source_id', $traderoute['source_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':playerinfo_ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':playerinfo_team', $playerinfo['team'], \PDO::PARAM_INT);
            $stmt->execute();
            $source = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($source['sector_id'] != $playerinfo['sector'])
            {
                // Check for valid owned source planet
                $langvars['l_tdr_inittdrsector'] = str_replace("[tdr_source_sector_id]", $source['sector_id'], $langvars['l_tdr_inittdrsector']);
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_inittdrsector']);
            }

            if ($traderoute['source_type'] == 'L')
            {
                if ($source['owner'] != $playerinfo['ship_id'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $source['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $source['sector_id'], $langvars['l_tdr_notyourplanet']);
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notyourplanet']);
                }
            }
            elseif ($traderoute['source_type'] == 'C')   // Check to make sure player and planet are in the same team.
            {
                if ($source['team'] != $playerinfo['team'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $source['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $source['sector_id'], $langvars['l_tdr_notyourplanet']);
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notyourplanet']);
                }
            }

            // Store starting port info, we'll need it later
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id = :source_sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':source_sector_id', $source['source_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $sourceport = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($sourceport))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invalidssector']);
            }
        }

        // Destination Check
        if ($traderoute['dest_type'] == 'P')
        {
            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id = :traderoute_dest_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':traderoute_dest_id', $traderoute['dest_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $dest = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($dest))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddport']);
            }
        }
        elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))  // Get data from planet table
        {
            // Check for valid owned source planet
            // This now only returns planets that the player owns or planets that belong to the team and set as team planets..
            $sql = "SELECT * FROM ::prefix::planets WHERE planet_id = :traderoute_dest_id AND (owner = :playerinfo_ship_id OR (team <> 0 AND team = :playerinfo_team))";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':traderoute_dest_id', $traderoute['dest_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':playerinfo_ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':playerinfo_team', $playerinfo['team'], \PDO::PARAM_INT);
            $stmt->execute();
            $dest = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($dest))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddplanet']);
            }

            if ($traderoute['dest_type'] == 'L')
            {
                if ($dest['owner'] != $playerinfo['ship_id'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $dest['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $dest['sector_id'], $langvars['l_tdr_notyourplanet']);
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notyourplanet']);
                }
            }
            elseif ($traderoute['dest_type'] == 'C')   // Check to make sure player and planet are in the same team.
            {
                if ($dest['team'] != $playerinfo['team'])
                {
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_name]", $dest['name'], $langvars['l_tdr_notyourplanet']);
                    $langvars['l_tdr_notyourplanet'] = str_replace("[tdr_source_sector_id]", $dest['sector_id'], $langvars['l_tdr_notyourplanet']);
                    \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_notyourplanet']);
                }
            }

            $sql = "SELECT * FROM ::prefix::universe WHERE sector_id = :dest_sector_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':dest_sector_id', $dest['sector_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $destport = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (empty($destport))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_invaliddsector']);
            }
        }

        if (!isset($sourceport))
        {
            $sourceport = $source;
        }

        if (!isset($destport))
        {
            $destport = $dest;
        }

        // Warp or real space and generate distance
        $dist = \Tki\TraderouteDistance::calc($pdo_db, 'P', 'P', $sourceport, $destport, $traderoute['circuit'], $playerinfo, $tkireg);
        if ($traderoute['move_type'] == 'W')
        {
            $dist = \Tki\TraderouteDistance::warpCalc($pdo_db, $lang, $tkireg, $tkitimer, $template, $traderoute, $source, $dest);
        }

        // Check if player has enough turns
        if ($playerinfo['turns'] < $dist['triptime'])
        {
            $langvars['l_tdr_moreturnsneeded'] = str_replace("[tdr_dist_triptime]", $dist['triptime'], $langvars['l_tdr_moreturnsneeded']);
            $langvars['l_tdr_moreturnsneeded'] = str_replace("[tdr_playerinfo_turns]", $playerinfo['turns'], $langvars['l_tdr_moreturnsneeded']);
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_moreturnsneeded']);
        }

        // Sector defense check
        $hostile = 0;

        $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :source_sector_id AND ship_id <> :playerinfo_ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':source_sector_id', $source['sector_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':playerinfo_ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $fighters_owner = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($destport))
        {
            $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
            $nsfighters = $players_gateway->selectPlayerInfoById($fighters_owner['ship_id']);

            if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                $hostile = 1;
            }
        }

        $sql = "SELECT * FROM ::prefix::sector_defense WHERE sector_id = :dest_sector_id AND ship_id <> :playerinfo_ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':dest_sector_id', $dest['sector_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':playerinfo_ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $fighters_owner = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($destport))
        {
            $players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
            $nsfighters = $players_gateway->selectPlayerInfoById($fighters_owner['ship_id']);

            if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
            {
                $hostile = 1;
            }
        }

        if ($hostile > 0 && $playerinfo['hull'] > $tkireg->mine_hullsize)
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_hostdef']);
        }

        // Special port nothing to do
        if ($traderoute['source_type'] == 'P' && $source['port_type'] == 'special' && $playerinfo['trade_colonists'] == 'N' && $playerinfo['trade_fighters'] == 'N' && $playerinfo['trade_torps'] == 'N')
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_globalsetbuynothing']);
        }

        // Check if zone allows trading  SRC
        if ($traderoute['source_type'] == 'P')
        {
            // Get zoneinfo from database
            $zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db);
            $zoneinfo = $zones_gateway->selectMatchingZoneInfo($traderoute['source_id']);

            if (empty($zoneinfo))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_empty_zone_info']);
            }

            if ($zoneinfo['allow_trade'] == 'N')
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nosrcporttrade']);
            }
            elseif ($zoneinfo['allow_trade'] == 'L')
            {
                if ($zoneinfo['team_zone'] == 'N')
                {
                    $sql = "SELECT team FROM ::prefix::ships WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_id', $zoneinfo['owner'], \PDO::PARAM_INT);
                    $stmt->execute();
                    $ownerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if (($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0) || ($playerinfo['team'] != $ownerinfo['team']))
                    {
                        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_tradesrcportoutsider']);
                    }
                }
                else
                {
                    if ($playerinfo['team'] != $zoneinfo['owner'])
                    {
                        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_tradesrcportoutsider']);
                    }
                }
            }
        }

        // Check if zone allows trading  DEST
        if ($traderoute['dest_type'] == 'P')
        {
            // Get zoneinfo from database
            $zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db);
            $zoneinfo = $zones_gateway->selectMatchingZoneInfo($traderoute['dest_id']);

            if (empty($zoneinfo))
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_empty_zone_info']);
            }

            if ($zoneinfo['allow_trade'] == 'N')
            {
                \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_nodestporttrade']);
            }
            elseif ($zoneinfo['allow_trade'] == 'L')
            {
                if ($zoneinfo['team_zone'] == 'N')
                {
                    $sql = "SELECT team FROM ::prefix::ships WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_id', $zoneinfo['owner'], \PDO::PARAM_INT);
                    $stmt->execute();
                    $ownerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if (($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0) || ($playerinfo['team'] != $ownerinfo['team']))
                    {
                        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_tradedestportoutsider']);
                    }
                }
                else
                {
                    if ($playerinfo['team'] != $zoneinfo['owner'])
                    {
                        \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, $langvars['l_tdr_tradedestportoutsider']);
                    }
                }
            }
        }

        \Tki\TraderouteResults::tableTop($pdo_db, $lang, $tkireg);
        // Determine if source is a planet or a port
        if ($traderoute['source_type'] == 'P')
        {
            echo $langvars['l_tdr_portin'] . " " . $source['sector_id'];
        }
        elseif (($traderoute['source_type'] == 'L') || ($traderoute['source_type'] == 'C'))
        {
            echo $langvars['l_tdr_planet'] . " " . $source['name'] . " in " . $sourceport['sector_id'];
        }

        \Tki\TraderouteResults::source();

        // Determine if destination is a planet or a port
        if ($traderoute['dest_type'] == 'P')
        {
            echo $langvars['l_tdr_portin'] . " " . $dest['sector_id'];
        }
        elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))
        {
            echo $langvars['l_tdr_planet'] . " " . $dest['name'] . " in " . $destport['sector_id'];
        }

        \Tki\TraderouteResults::destination($tkireg);

        $sourcecost = 0;

        // Source is port
        if ($traderoute['source_type'] == 'P')
        {
            // Special port section (begin)
            if ($source['port_type'] == 'special')
            {
                $total_credits = $playerinfo['credits'];

                if ($playerinfo['trade_colonists'] == 'Y')
                {
                    $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
                    $colonists_buy = $free_holds;

                    if ($playerinfo['credits'] < $tkireg->colonist_price * $colonists_buy)
                    {
                        $colonists_buy = $playerinfo['credits'] / $tkireg->colonist_price;
                    }

                    if ($colonists_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                    }

                    $sourcecost -= $colonists_buy * $tkireg->colonist_price;
                    $total_credits -= $colonists_buy * $tkireg->colonist_price;
                }
                else
                {
                    $colonists_buy = 0;
                }

                if ($playerinfo['trade_fighters'] == 'Y')
                {
                    $free_fighters = \Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
                    $fighters_buy = $free_fighters;

                    if ($total_credits < $fighters_buy * $tkireg->fighter_price)
                    {
                        $fighters_buy = $total_credits / $tkireg->fighter_price;
                    }

                    if ($fighters_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                    }

                    $sourcecost -= $fighters_buy * $tkireg->fighter_price;
                    $total_credits -= $fighters_buy * $tkireg->fighter_price;
                }
                else
                {
                    $fighters_buy = 0;
                }

                if ($playerinfo['trade_torps'] == 'Y')
                {
                    $free_torps = \Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
                    $torps_buy = $free_torps;

                    if ($total_credits < $torps_buy * $tkireg->torpedo_price)
                    {
                        $torps_buy = $total_credits / $tkireg->torpedo_price;
                    }

                    if ($torps_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                    }

                    $sourcecost -= $torps_buy * $tkireg->torpedo_price;
                }
                else
                {
                    $torps_buy = 0;
                }

                if ($torps_buy == 0 && $colonists_buy == 0 && $fighters_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $sql = "UPDATE ::prefix::ships SET ship_colonists = ship_colonists + :colonists_buy, ship_fighters = ship_fighters + :fighters_buy, torps = torps + :torps_buy, ship_energy = ship_energy + :dist_scooped WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':colonists_buy', $colonists_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':fighters_buy', $fighters_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':torps_buy', $torps_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':dist_scooped', $dist['scooped1'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }
            }
            // Normal port section
            else
            {
                // Sells commodities

                if ($source['port_type'] != 'ore')
                {
                    $ore_price = $ore_price + $tkireg->ore_delta * $source['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
                    $ore_buy = $playerinfo['ship_ore'];
                    if ($source['port_ore'] - $playerinfo['ship_ore'] < 0)
                    {
                        $ore_buy = $source['port_ore'];
                        $portfull = 1;
                    }

                    $sourcecost += $ore_buy * $ore_price;
                    if ($ore_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }
                    }

                    $playerinfo['ship_ore'] -= $ore_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'goods')
                {
                    $goods_price = $goods_price + $tkireg->goods_delta * $source['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
                    $goods_buy = $playerinfo['ship_goods'];
                    if ($source['port_goods'] - $playerinfo['ship_goods'] < 0)
                    {
                        $goods_buy = $source['port_goods'];
                        $portfull = 1;
                    }

                    $sourcecost += $goods_buy * $goods_price;
                    if ($goods_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }
                    }

                    $playerinfo['ship_goods'] -= $goods_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'organics')
                {
                    $organics_price = $organics_price + $tkireg->organics_delta * $source['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
                    $organics_buy = $playerinfo['ship_organics'];
                    if ($source['port_organics'] - $playerinfo['ship_organics'] < 0)
                    {
                        $organics_buy = $source['port_organics'];
                        $portfull = 1;
                    }

                    $sourcecost += $organics_buy * $organics_price;
                    if ($organics_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }
                    }

                    $playerinfo['ship_organics'] -= $organics_buy;
                }

                $portfull = 0;
                if ($source['port_type'] != 'energy' && $playerinfo['trade_energy'] == 'Y')
                {
                    $energy_price = $energy_price + $tkireg->energy_delta * $source['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
                    $energy_buy = $playerinfo['ship_energy'];
                    if ($source['port_energy'] - $playerinfo['ship_energy'] < 0)
                    {
                        $energy_buy = $source['port_energy'];
                        $portfull = 1;
                    }

                    $sourcecost += $energy_buy * $energy_price;
                    if ($energy_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }
                    }

                    $playerinfo['ship_energy'] -= $energy_buy;
                }

                $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

                // Time to buy
                if ($source['port_type'] == 'ore')
                {
                    $ore_price = $ore_price - $tkireg->ore_delta * $source['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
                    $ore_buy = $free_holds;
                    if ($playerinfo['credits'] + $sourcecost < $ore_buy * $ore_price)
                    {
                        $ore_buy = ($playerinfo['credits'] + $sourcecost) / $ore_price;
                    }

                    if ($source['port_ore'] < $ore_buy)
                    {
                        $ore_buy = $source['port_ore'];
                        if ($source['port_ore'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "(" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($ore_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                    }

                    $playerinfo['ship_ore'] += $ore_buy;
                    $sourcecost -= $ore_buy * $ore_price;

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $source['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'goods')
                {
                    $goods_price = $goods_price - $tkireg->goods_delta * $source['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
                    $goods_buy = $free_holds;
                    if ($playerinfo['credits'] + $sourcecost < $goods_buy * $goods_price)
                    {
                        $goods_buy = ($playerinfo['credits'] + $sourcecost) / $goods_price;
                    }

                    if ($source['port_goods'] < $goods_buy)
                    {
                        $goods_buy = $source['port_goods'];
                        if ($source['port_goods'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($goods_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                    }

                    $playerinfo['ship_goods'] += $goods_buy;
                    $sourcecost -= $goods_buy * $goods_price;

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $source['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'organics')
                {
                    $organics_price = $organics_price - $tkireg->organics_delta * $source['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
                    $organics_buy = $free_holds;

                    if ($playerinfo['credits'] + $sourcecost < $organics_buy * $organics_price)
                    {
                        $organics_buy = ($playerinfo['credits'] + $sourcecost) / $organics_price;
                    }

                    if ($source['port_organics'] < $organics_buy)
                    {
                        $organics_buy = $source['port_organics'];
                        if ($source['port_organics'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($organics_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                    }

                    $playerinfo['ship_organics'] += $organics_buy;
                    $sourcecost -= $organics_buy * $organics_price;

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $source['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($source['port_type'] == 'energy')
                {
                    $energy_price = $energy_price - $tkireg->energy_delta * $source['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
                    $energy_buy = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'] - $dist['scooped1'];

                    if ($playerinfo['credits'] + $sourcecost < $energy_buy * $energy_price)
                    {
                        $energy_buy = ($playerinfo['credits'] + $sourcecost) / $energy_price;
                    }

                    if ($source['port_energy'] < $energy_buy)
                    {
                        $energy_buy = $source['port_energy'];
                        if ($source['port_energy'] == 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                        }
                    }

                    if ($energy_buy != 0)
                    {
                        echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                    }

                    $playerinfo['ship_energy'] += $energy_buy;
                    $sourcecost -= $energy_buy * $energy_price;

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $source['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($dist['scooped1'] > 0)
                {
                    $playerinfo['ship_energy'] += $dist['scooped1'];
                    if ((int) $playerinfo['ship_energy'] > \Tki\CalcLevels::energy($playerinfo['power'], $tkireg))
                    {
                        $playerinfo['ship_energy'] = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg);
                    }
                }

                if ($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $sql = "UPDATE ::prefix::ships SET ship_ore = :ship_ore, ship_goods = :ship_goods, ship_organics = :ship_organics, ship_energy = :ship_energy  WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ship_ore', $playerinfo['ship_ore'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_goods', $playerinfo['ship_goods'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_organics', $playerinfo['ship_organics'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_energy', $dist['ship_energy'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }
            }
        }
        // Source is planet
        elseif (($traderoute['source_type'] == 'L') || ($traderoute['source_type'] == 'C'))
        {
            $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            if ($traderoute['dest_type'] == 'P')
            {
                // Pick stuff up to sell at port
                if (($playerinfo['ship_id'] == $source['owner']) || ($playerinfo['team'] == $source['team']))
                {
                    if ($source['goods'] > 0 && $free_holds > 0 && $dest['port_type'] != 'goods')
                    {
                        $goods_buy = $source['goods'];
                        if ($source['goods'] > $free_holds)
                        {
                            $goods_buy = $free_holds;
                        }

                        $free_holds -= $goods_buy;
                        $playerinfo['ship_goods'] += $goods_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                    }
                    else
                    {
                        $goods_buy = 0;
                    }

                    if ($source['ore'] > 0 && $free_holds > 0 && $dest['port_type'] != 'ore')
                    {
                        $ore_buy = $source['ore'];
                        if ($source['ore'] > $free_holds)
                        {
                            $ore_buy = $free_holds;
                        }

                        $free_holds -= $ore_buy;
                        $playerinfo['ship_ore'] += $ore_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                    }
                    else
                    {
                        $ore_buy = 0;
                    }

                    if ($source['organics'] > 0 && $free_holds > 0 && $dest['port_type'] != 'organics')
                    {
                        $organics_buy = $source['organics'];
                        if ($source['organics'] > $free_holds)
                        {
                            $organics_buy = $free_holds;
                        }

                        $free_holds -= $organics_buy;
                        $playerinfo['ship_organics'] += $organics_buy;
                        echo $langvars['l_tdr_loaded'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                    }
                    else
                    {
                        $organics_buy = 0;
                    }

                    if ($ore_buy == 0 && $goods_buy == 0 && $organics_buy == 0)
                    {
                        echo $langvars['l_tdr_nothingtoload'] . "<br>";
                    }

                    if ($traderoute['circuit'] == '1')
                    {
                        $sql = "UPDATE ::prefix::ships SET ship_ore = :ship_ore, ship_goods = :ship_goods, ship_organics = :ship_organics WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':colonists_buy', $playerinfo['ship_ore'], \PDO::PARAM_INT);
                        $stmt->bindParam(':fighters_buy', $playerinfo['ship_goods'], \PDO::PARAM_INT);
                        $stmt->bindParam(':torps_buy', $playerinfo['ship_organics'], \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                }

                $sql = "UPDATE ::prefix::planets SET ore = ore - :ore_buy, goods = goods - :goods_buy, organics = organics - :organics_buy WHERE planet_id = :planet_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $source['planet_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            }
            // Destination is a planet, so load colonists and weapons
            elseif (($traderoute['dest_type'] == 'L') || ($traderoute['dest_type'] == 'C'))
            {
                if ($source['colonists'] > 0 && $free_holds > 0 && $playerinfo['trade_colonists'] == 'Y')
                {
                    $colonists_buy = $source['colonists'];
                    if ($source['colonists'] > $free_holds)
                    {
                        $colonists_buy = $free_holds;
                    }

                    $free_holds -= $colonists_buy;
                    $playerinfo['ship_colonists'] += $colonists_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                }
                else
                {
                    $colonists_buy = 0;
                }

                $free_torps = \Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
                if ($source['torps'] > 0 && $free_torps > 0 && $playerinfo['trade_torps'] == 'Y')
                {
                    $torps_buy = $source['torps'];
                    if ($source['torps'] > $free_torps)
                    {
                        $torps_buy = $free_torps;
                    }

                    $free_torps -= $torps_buy;
                    $playerinfo['torps'] += $torps_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                }
                else
                {
                    $torps_buy = 0;
                }

                $free_fighters = \Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
                if ($source['fighters'] > 0 && $free_fighters > 0 && $playerinfo['trade_fighters'] == 'Y')
                {
                    $fighters_buy = $source['fighters'];
                    if ($source['fighters'] > $free_fighters)
                    {
                        $fighters_buy = $free_fighters;
                    }

                    $free_fighters -= $fighters_buy;
                    $playerinfo['ship_fighters'] += $fighters_buy;
                    echo $langvars['l_tdr_loaded'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                }
                else
                {
                    $fighters_buy = 0;
                }

                if ($fighters_buy == 0 && $torps_buy == 0 && $colonists_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtoload'] . "<br>";
                }

                if ($traderoute['circuit'] == '1')
                {
                    $sql = "UPDATE ::prefix::ships SET torps = :torps, ship_fighters = :ship_fighters, ship_colonists = :ship_colonists WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':torps', $playerinfo['torps'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_fighters', $playerinfo['ship_fighters'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_colonists', $playerinfo['ship_colonists'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                $sql = "UPDATE ::prefix::planets SET colonists = colonists - :colonists, torps = torps - :torps, fighters = fighters - :fighters WHERE planet_id = :planet_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':colonists', $colonists_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':torps', $torps_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':fighters', $fighters_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $source['planet_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            }
        }

        if ($dist['scooped1'] != 0)
        {
            echo $langvars['l_tdr_scooped'] . " " . number_format($dist['scooped1'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
        }

        \Tki\TraderouteResults::closeCell();

        if ($traderoute['circuit'] == '2')
        {
            $playerinfo['credits'] += $sourcecost;
            $destcost = 0;
            if ($traderoute['dest_type'] == 'P')
            {
                // Added the below for traderoute bug
                $ore_buy = 0;
                $goods_buy = 0;
                $organics_buy = 0;
                $energy_buy = 0;

                // Sells commodities
                $portfull = 0;
                if ($dest['port_type'] != 'ore')
                {
                    $ore_price = $ore_price + $tkireg->ore_delta * $dest['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
                    $ore_buy = $playerinfo['ship_ore'];
                    if ($dest['port_ore'] - $playerinfo['ship_ore'] < 0)
                    {
                        $ore_buy = $dest['port_ore'];
                        $portfull = 1;
                    }

                    $destcost += $ore_buy * $ore_price;

                    if ($ore_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }
                    }

                    $playerinfo['ship_ore'] -= $ore_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'goods')
                {
                    $goods_price = $goods_price + $tkireg->goods_delta * $dest['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
                    $goods_buy = $playerinfo['ship_goods'];
                    if ($dest['port_goods'] - $playerinfo['ship_goods'] < 0)
                    {
                        $goods_buy = $dest['port_goods'];
                        $portfull = 1;
                    }

                    $destcost += $goods_buy * $goods_price;
                    if ($goods_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }
                    }

                    $playerinfo['ship_goods'] -= $goods_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'organics')
                {
                    $organics_price = $organics_price + $tkireg->organics_delta * $dest['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
                    $organics_buy = $playerinfo['ship_organics'];
                    if ($dest['port_organics'] - $playerinfo['ship_organics'] < 0)
                    {
                        $organics_buy = $dest['port_organics'];
                        $portfull = 1;
                    }

                    $destcost += $organics_buy * $organics_price;
                    if ($organics_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }
                    }

                    $playerinfo['ship_organics'] -= $organics_buy;
                }

                $portfull = 0;
                if ($dest['port_type'] != 'energy' && $playerinfo['trade_energy'] == 'Y')
                {
                    $energy_price = $energy_price + $tkireg->energy_delta * $dest['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
                    $energy_buy = $playerinfo['ship_energy'];
                    if ($dest['port_energy'] - $playerinfo['ship_energy'] < 0)
                    {
                        $energy_buy = $dest['port_energy'];
                        $portfull = 1;
                    }

                    $destcost += $energy_buy * $energy_price;
                    if ($energy_buy != 0)
                    {
                        if ($portfull == 1)
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisfull'] . ")<br>";
                        }
                        else
                        {
                            echo $langvars['l_tdr_sold'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }
                    }

                    $playerinfo['ship_energy'] -= $energy_buy;
                }
                else
                {
                    $energy_buy = 0;
                }

                $free_holds = \Tki\CalcLevels::abstractLevels($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

                // Time to buy
                if ($dest['port_type'] == 'ore')
                {
                    $ore_price = $ore_price - $tkireg->ore_delta * $dest['port_ore'] / $tkireg->ore_limit * $tkireg->inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $ore_buy = 0;
                    }
                    else
                    {
                        $ore_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $ore_buy * $ore_price)
                        {
                            $ore_buy = ($playerinfo['credits'] + $destcost) / $ore_price;
                        }

                        if ($dest['port_ore'] < $ore_buy)
                        {
                            $ore_buy = $dest['port_ore'];
                            if ($dest['port_ore'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($ore_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($ore_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_ore'] . "<br>";
                        }

                        $playerinfo['ship_ore'] += $ore_buy;
                        $destcost -= $ore_buy * $ore_price;
                    }

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $dest['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'goods')
                {
                    $goods_price = $goods_price - $tkireg->goods_delta * $dest['port_goods'] / $tkireg->goods_limit * $tkireg->inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $goods_buy = 0;
                    }
                    else
                    {
                        $goods_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $goods_buy * $goods_price)
                        {
                            $goods_buy = ($playerinfo['credits'] + $destcost) / $goods_price;
                        }

                        if ($dest['port_goods'] < $goods_buy)
                        {
                            $goods_buy = $dest['port_goods'];
                            if ($dest['port_goods'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($goods_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($goods_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_goods'] . "<br>";
                        }

                        $playerinfo['ship_goods'] += $goods_buy;
                        $destcost -= $goods_buy * $goods_price;
                    }

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $dest['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'organics')
                {
                    $organics_price = $organics_price - $tkireg->organics_delta * $dest['port_organics'] / $tkireg->organics_limit * $tkireg->inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $organics_buy = 0;
                    }
                    else
                    {
                        $organics_buy = $free_holds;
                        if ($playerinfo['credits'] + $destcost < $organics_buy * $organics_price)
                        {
                            $organics_buy = ($playerinfo['credits'] + $destcost) / $organics_price;
                        }

                        if ($dest['port_organics'] < $organics_buy)
                        {
                            $organics_buy = $dest['port_organics'];

                            if ($dest['port_organics'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($organics_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($organics_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_organics'] . "<br>";
                        }

                        $playerinfo['ship_organics'] += $organics_buy;
                        $destcost -= $organics_buy * $organics_price;
                    }

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $dest['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($dest['port_type'] == 'energy')
                {
                    $energy_price = $energy_price - $tkireg->energy_delta * $dest['port_energy'] / $tkireg->energy_limit * $tkireg->inventory_factor;
                    if ($traderoute['source_type'] == 'L')
                    {
                        $energy_buy = 0;
                    }
                    else
                    {
                        $energy_buy = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg) - $playerinfo['ship_energy'] - $dist['scooped1'];
                        if ($playerinfo['credits'] + $destcost < $energy_buy * $energy_price)
                        {
                            $energy_buy = ($playerinfo['credits'] + $destcost) / $energy_price;
                        }

                        if ($dest['port_energy'] < $energy_buy)
                        {
                            $energy_buy = $dest['port_energy'];
                            if ($dest['port_energy'] == 0)
                            {
                                echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . " (" . $langvars['l_tdr_portisempty'] . ")<br>";
                            }
                        }

                        if ($energy_buy != 0)
                        {
                            echo $langvars['l_tdr_bought'] . " " . number_format($energy_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
                        }

                        $playerinfo['ship_energy'] += $energy_buy;
                        $destcost -= $energy_buy * $energy_price;
                    }

                    if ($ore_buy == 0 && $goods_buy == 0 && $energy_buy == 0 && $organics_buy == 0)
                    {
                        echo $langvars['l_tdr_nothingtotrade'] . "<br>";
                    }

                    $sql = "UPDATE ::prefix::universe SET port_ore = port_ore - :ore_buy, port_energy = port_energy - :energy_buy, port_goods = port_goods - :goods_buy, port_organics = port_organics - :organics_buy WHERE sector_id = :sector_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':ore_buy', $ore_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':energy_buy', $energy_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':goods_buy', $goods_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':organics_buy', $organics_buy, \PDO::PARAM_INT);
                    $stmt->bindParam(':sector_id', $dest['sector_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }

                if ($dist['scooped2'] > 0)
                {
                    $playerinfo['ship_energy'] += $dist['scooped2'];

                    if ((int) $playerinfo['ship_energy'] > \Tki\CalcLevels::energy($playerinfo['power'], $tkireg))
                    {
                        $playerinfo['ship_energy'] = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg);
                    }
                }

                $sql = "UPDATE ::prefix::ships SET ship_ore = :ship_ore, ship_goods = :ship_goods, ship_organics = :ship_organics, ship_energy = :ship_energy WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_ore', $playerinfo['ship_ore'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_goods', $playerinfo['ship_goods'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_organics', $playerinfo['ship_organics'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_energy', $playerinfo['ship_energy'], \PDO::PARAM_INT);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
            }
            else // Dest is planet
            {
                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    $colonists_buy = 0;
                    $fighters_buy = 0;
                    $torps_buy = 0;
                }

                $setcol = 0;

                $col_dump = 0;
                if ($playerinfo['trade_colonists'] == 'Y')
                {
                    $colonists_buy += $playerinfo['ship_colonists'];
                    $col_dump = $playerinfo['ship_colonists'];
                    if ($dest['colonists'] + $colonists_buy >= $tkireg->colonist_limit)
                    {
                        $exceeding = $dest['colonists'] + $colonists_buy - $tkireg->colonist_limit;
                        $col_dump = $exceeding;
                        $setcol = 1;
                        $colonists_buy -= $exceeding;
                        if ($colonists_buy < 0)
                        {
                            $colonists_buy = 0;
                        }
                    }
                }

                if ($colonists_buy != 0)
                {
                    if ($setcol == 1)
                    {
                        echo $langvars['l_tdr_dumped'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . " (" . $langvars['l_tdr_planetisovercrowded'] . ")<br>";
                    }
                    else
                    {
                        echo $langvars['l_tdr_dumped'] . " " . number_format($colonists_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_colonists'] . "<br>";
                    }
                }

                $fight_dump = 0;
                if ($playerinfo['trade_fighters'] == 'Y')
                {
                    $fighters_buy += $playerinfo['ship_fighters'];
                    $fight_dump = $playerinfo['ship_fighters'];
                }

                if ($fighters_buy != 0)
                {
                    echo $langvars['l_tdr_dumped'] . " " . number_format($fighters_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_fighters'] . "<br>";
                }

                $torps_dump = 0;
                if ($playerinfo['trade_torps'] == 'Y')
                {
                    $torps_buy += $playerinfo['torps'];
                    $torps_dump = $playerinfo['torps'];
                }

                if ($torps_buy != 0)
                {
                    echo $langvars['l_tdr_dumped'] . " " . number_format($torps_buy, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_torps'] . "<br>";
                }

                if ($torps_buy == 0 && $fighters_buy == 0 && $colonists_buy == 0)
                {
                    echo $langvars['l_tdr_nothingtodump'] . "<br>";
                }

                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    if ($playerinfo['trade_colonists'] == 'Y')
                    {
                        if ($setcol != 1)
                        {
                            $col_dump = 0;
                        }
                    }
                    else
                    {
                        $col_dump = $playerinfo['ship_colonists'];
                    }

                    $fight_dump = $playerinfo['ship_fighters'];
                    if ($playerinfo['trade_fighters'] == 'Y')
                    {
                        $fight_dump = 0;
                    }

                    $torps_dump = $playerinfo['torps'];
                    if ($playerinfo['trade_torps'] == 'Y')
                    {
                        $torps_dump = 0;
                    }
                }

                $sql = "UPDATE ::prefix::planets SET colonists = colonists + :colonists_buy, fighters = fighters + :fighters_buy, torps = torps + :torps_buy WHERE planet_id = :planet_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':colonists_buy', $colonists_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':fighters_buy', $fighters_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':torps_buy', $torps_buy, \PDO::PARAM_INT);
                $stmt->bindParam(':planet_id', $traderoute['dest_id'], \PDO::PARAM_INT);
                $stmt->execute();
                \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                if ($traderoute['source_type'] == 'L' || $traderoute['source_type'] == 'C')
                {
                    $sql = "UPDATE ::prefix::ships SET ship_colonists = :col_dump, ship_fighters = :fight_dump, torps = :torps_dump, ship_energy = ship_energy + :dist_scooped WHERE ship_id = :ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':col_dump', $col_dump, \PDO::PARAM_INT);
                    $stmt->bindParam(':fight_dump', $fight_dump, \PDO::PARAM_INT);
                    $stmt->bindParam(':torps_dump', $torps_dump, \PDO::PARAM_INT);
                    $stmt->bindParam(':dist_scooped', $dist['scooped'], \PDO::PARAM_INT);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }
                else
                {
                    if ($setcol == 1)
                    {
                        $sql = "UPDATE ::prefix::ships SET ship_colonists = :col_dump, ship_fighters = ship_fighters - :fight_dump, torps = torps - :torps_dump, ship_energy = ship_energy + :dist_scooped WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':col_dump', $col_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':fight_dump', $fight_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':torps_dump', $torps_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':dist_scooped', $dist['scooped'], \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                    else
                    {
                        $sql = "UPDATE ::prefix::ships SET ship_colonists = ship_colonists - :col_dump, ship_fighters = ship_fighters - :fight_dump, torps = torps - :torps_dump, ship_energy = ship_energy + :dist_scooped WHERE ship_id = :ship_id";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':col_dump', $col_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':fight_dump', $fight_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':torps_dump', $torps_dump, \PDO::PARAM_INT);
                        $stmt->bindParam(':dist_scooped', $dist['scooped'], \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                }
            }

            if ($dist['scooped2'] != 0)
            {
                echo $langvars['l_tdr_scooped'] . " " . number_format($dist['scooped1'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_tdr_energy'] . "<br>";
            }
        }
        else
        {
            echo $langvars['l_tdr_onlyonewaytdr'];
            $destcost = 0;
        }

        \Tki\TraderouteResults::showCost($tkireg);

        if ($sourcecost > 0)
        {
            echo $langvars['l_tdr_profit'] . " : " . number_format(abs($sourcecost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }
        else
        {
            echo $langvars['l_tdr_cost'] . " : " . number_format(abs($sourcecost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }

        \Tki\TraderouteResults::closeCost();

        if ($destcost > 0)
        {
            echo $langvars['l_tdr_profit'] . " : " . number_format(abs($destcost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }
        else
        {
            echo $langvars['l_tdr_cost'] . " : " . number_format(abs($destcost), 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        }

        \Tki\TraderouteResults::closeTable();

        $total_profit = (int) round($sourcecost + $destcost);
        \Tki\TraderouteResults::displayTotals($pdo_db, $lang, $total_profit);

        $newsec = $sourceport['sector_id'];
        if ($traderoute['circuit'] == '1')
        {
            $newsec = $destport['sector_id'];
        }

        $sql = "UPDATE ::prefix::ships SET turns = turns - :triptime, credits = credits + :profit, turns_used = turns_used + :triptime, sector = :new_sector SET WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':triptime', $dist['triptime'], \PDO::PARAM_INT);
        $stmt->bindParam(':profit', $total_profit, \PDO::PARAM_INT);
        $stmt->bindParam(':triptime', $dist['triptime'], \PDO::PARAM_INT);
        $stmt->bindParam(':new_sector', $newsec, \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        $playerinfo['credits'] += $total_profit - $sourcecost;
        $playerinfo['turns'] -= $dist['triptime'];

        $tdr_display_creds = number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        \Tki\TraderouteResults::displaySummary($pdo_db, $lang, $tdr_display_creds, $dist, $playerinfo);
        echo $tr_repeat . " -- ";
        if ($traderoute['circuit'] == 2)
        {
            $langvars['l_tdr_engageagain'] = str_replace("[here]", "<a href=\"traderoute.php?engage=[tdr_engage]\">" . $langvars['l_here'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[five]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=5\">" . $langvars['l_tdr_five'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[ten]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=10\">" . $langvars['l_tdr_ten'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[fifty]", "<a href=\"traderoute.php?engage=[tdr_engage]&amp;tr_repeat=50\">" . $langvars['l_tdr_fifty'] . "</a>", $langvars['l_tdr_engageagain']);
            $langvars['l_tdr_engageagain'] = str_replace("[tdr_engage]", (string) $engage, $langvars['l_tdr_engageagain']);
            if ($tr_repeat == 1)
            {
                echo $langvars['l_tdr_engageagain'] . "\n";
                \Tki\TraderouteResults::showRepeat($engage);
            }
        }

        if ($tr_repeat == 1)
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $tkitimer, $template, null);
        }
    }
}
