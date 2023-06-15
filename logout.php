<?php declare(strict_types = 1);
/**
 * logout.php from The Kabal Invasion.
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
$variables = array();

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common',
                                'footer', 'insignias', 'logout', 'news',
                                'universal'));
if (array_key_exists('username', $_SESSION))
{
    $current_score = 0;

    // Get playerinfo from database
    $players_gateway = new \Tki\Players\User($pdo_db);
    $playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

    $current_score = Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
    $langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common',
                                    'footer', 'insignias', 'logout', 'news',
                                    'universal'));
    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::LOGOUT, $request->server->get('REMOTE_ADDR'));
    $langvars['l_logout_text'] = str_replace("[name]", $_SESSION['username'], $langvars['l_logout_text']);
    $langvars['l_logout_text'] = str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_logout_text']);

    // Convert language entries to include session information while it still exists
    $langvars['l_logout_text_replaced'] = str_replace("[name]", $_SESSION['username'], $langvars['l_logout_text']);
    $langvars['l_logout_text_replaced'] = str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_logout_text_replaced']);
    $variables['current_score'] = $current_score;
    $variables['session_username'] = $_SESSION['username'];
    $variables['l_logout_text_replaced'] = $langvars['l_logout_text_replaced'];
}
else
{
    $variables['session_username'] = null;
    $variables['linkback'] = array("fulltext" => $langvars['l_universal_main_login'], "link" => "index.php");
}

// Set login status to false, then clear the session array, and finally clear the session cookie
$_SESSION['logged_in'] = false;
$_SESSION = array();
setcookie('tki_session', '', 0, '/');

// Destroy the session entirely
session_destroy();

$variables['body_class'] = 'tki'; // No special CSS for this page yet, so use standard tki-prime CSS
$variables['lang'] = $lang;
$variables['linkback'] = array("fulltext" => $langvars['l_universal_main_login'], "link" => "index.php");
$variables['title'] = $langvars['l_logout_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);

$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('logout.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
