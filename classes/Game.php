<?php declare(strict_types = 1);
/**
 * classes/Game.php from The Kabal Invasion.
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

/**
 * @todo delete or turn into Vue Component
 * @deprecated
 */
class Game
{
    public function isGameClosed(
        \PDO $pdo_db,
        Registry $tkireg,
        Timer $tkitimer,
        string $lang,
        Smarty $template
    ): bool
    {
        if ($tkireg->game_closed === true)
        {
            $langvars = Translate::load($pdo_db, $lang, array('login'));
            $title = $langvars['l_login_closed_message'];

            $header = new \Tki\Header();
            $header->display($pdo_db, $lang, $template, $title);

            echo $langvars['l_login_closed_message'];

            $footer = new \Tki\Footer();
            $footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
            return true;
        }
        else
        {
            return false;
        }
    }
}
