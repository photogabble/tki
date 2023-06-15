<?php declare(strict_types = 1);
/**
 * feedback.php from The Kabal Invasion.
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
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'feedback',
                                'footer', 'galaxy', 'insignias',
                                'universal'));
$title = $langvars['l_feedback_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$players_gateway = new \Tki\Models\User($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$content = null;
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING); // URL doesn't allow spaces, string does.
if (($content === null) || (strlen(trim($content)) === 0))
{
    $content = false;
}

if ($content === false || $content === null)
{
    echo "<form accept-charset='utf-8' action=feedback.php method=post>\n";
    echo "<table>\n";
    echo "<tr><td>" . $langvars['l_feedback_to'] . "</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=GameAdmin></td></tr>\n";
    echo "<tr><td>" . $langvars['l_feedback_from'] . "</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=\"$playerinfo[character_name] - $playerinfo[email]\"></td></tr>\n";
    echo "<tr><td>" . $langvars['l_feedback_topi'] . "</td><td><input disabled type=text name=dummy size=40 maxlength=40 value=" . $langvars['l_feedback_feedback'] . "></td></tr>\n";
    echo "<tr><td>" . $langvars['l_feedback_message'] . "</td><td><textarea name=content rows=5 cols=40></textarea></td></tr>\n";
    echo "<tr><td></td><td><input type=submit value=" . $langvars['l_submit'] . "><input type=reset value=" . $langvars['l_reset'] . "></td>\n";
    echo "</table>\n";
    echo "</form>\n";
    echo "<br>" . $langvars['l_feedback_info'] . "<br>\n";
}
else
{
    $link_to_game = "https://" . $request->server->get('HTTP_HOST') . Tki\SetPaths::setGamepath();
    mail("$tkireg->admin_mail", $langvars['l_feedback_subj'], "IP address - " . $request->server->get('REMOTE_ADDR') . "\r\nGame Name - {$playerinfo['character_name']}\r\nServer URL - {$link_to_game}\r\n\r\n{$_POST['content']}", "From: {$playerinfo['email']}\r\nX-Mailer: PHP/" . phpversion());
    echo $langvars['l_feedback_messent'] . "<br><br>";
}

echo "<br>\n";
if (empty($_SESSION['username']))
{
    echo str_replace("[here]", "<a href='index.php'>" . $langvars['l_here'] . "</a>", $langvars['l_universal_main_login']);
}
else
{
    Tki\Text::gotoMain($pdo_db, $lang);
}

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
