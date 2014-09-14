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
// File: classes/Ports.php

namespace Bnt;

class Ports
{
    public static function getType($ptype, $langvars)
    {
        switch ($ptype)
        {
            case 'ore':
                $ret = $langvars['l_ore'];
                break;
            case 'none':
                $ret = $langvars['l_none'];
                break;
            case 'energy':
                $ret = $langvars['l_energy'];
                break;
            case 'organics':
                $ret = $langvars['l_organics'];
                break;
            case 'goods':
                $ret = $langvars['l_goods'];
                break;
            case 'special':
                $ret = $langvars['l_special'];
                break;
        }
        return $ret;
    }
}

