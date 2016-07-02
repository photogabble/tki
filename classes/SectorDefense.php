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
// File: classes/SectorDefense.php

namespace Tki;

class SectorDefense
{
    public static function messageDefenseOwner(\PDO $pdo_db, int $sector, $message)
    {
        $sql = "SELECT ship_id FROM {$pdo_db->prefix}sector_defence WHERE sector_id=:sector_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', $sector);
        $stmt->execute();
        $defence_present = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($defence_present !== null)
        {
            foreach ($defence_present as $tmp_defence)
            {
                PlayerLog::WriteLog($pdo_db, $tmp_defence['ship_id'], LOG_RAW, $message);
            }
        }
    }
}
