<?php
declare(strict_types=1);
/**
 * app/Types/ZoneInfo.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2023 Simon Dann, The Kabal Invasion development team, Ron Harwood, and the BNT development team
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

namespace Tki\Types;

final class ZoneInfo
{
    public function __construct(
        public string $name,
        public string $owner,
        public bool $isEditable,
        public ZonePermission $isFriendly,
        public ZonePermission $allow_beacon,
        public ZonePermission $allow_attack,
        public ZonePermission $allow_planetattack,
        public ZonePermission $allow_warpedit,
        public ZonePermission $allow_planet,
        public ZonePermission $allow_trade,
        public ZonePermission $allow_defenses,
        public int $max_hull,
        public bool $over_size,
    ){}
}