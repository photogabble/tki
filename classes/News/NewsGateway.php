<?php declare(strict_types = 1);
/**
 * classes/News/NewsGateway.php from The Kabal Invasion.
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

namespace Tki\News; // Domain Entity organization pattern, News objects

// TODO: Rename News and move to app/Models

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NewsGateway extends Model
{
    /**
     * @todo make usage aware of Carbon requirement
     * @param Carbon $day
     * @return Collection
     */
    public function selectNewsByDay(Carbon $day): Collection
    {
        // SQL call that selects all of the news items between the start date beginning of day, and the end of day.
        return NewsGateway::query()
            ->whereBetween('created_at', [$day->startOfDay(), $day->endOfDay()])
            ->get();
    }
}
