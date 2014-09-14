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
// File: classes/Game.php

namespace Bnt;

class Game
{
    public static function isGameClosed($pdo_db, $bntreg, $lang, $template, $langvars)
    {

        if ($bntreg->game_closed)
        {
            $title = $langvars['l_login_closed_message'];
            Header::display($pdo_db, $lang, $template, $title);
            echo $langvars['l_login_closed_message'];
            Footer::display($pdo_db, $lang, $bntreg, $template);
            die();
        }
        else
        {
            return false;
        }
    }
}
