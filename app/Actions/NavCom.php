<?php declare(strict_types=1);
/**
 * navcomp.php from The Kabal Invasion.
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

namespace Tki\Actions;

use Illuminate\Support\Facades\DB;
use Tki\Types\WarpRoute;
use Tki\Models\Ship;
use Tki\Models\User;

class NavCom
{
    public function calculate(User $user, Ship $ship, int $destSector): ?WarpRoute
    {
        if ($ship->computer < 5) {
            $maxSearchDepth = 2;
        } elseif ($ship->computer < 10) {
            $maxSearchDepth = 3;
        } elseif ($ship->computer < 15) {
            $maxSearchDepth = 4;
        } elseif ($ship->computer < 20) {
            $maxSearchDepth = 5;
        } else {
            $maxSearchDepth = 6;
        }

        for ($searchDepth = 1; $searchDepth <= $maxSearchDepth; $searchDepth++) {
            $select = ['a1.start', 'a1.dest as dest_1'];

            for ($i = 2; $i <= $searchDepth; $i++) {
                $select[] = "a$i.dest as dest_$i";
            }

            $from = ['links as a1'];

            for ($i = 2; $i <= $searchDepth; $i++) {
                $from[] = "links AS a$i";
            }

            $query = DB::table(DB::raw(implode(',', $from)))
                ->select($select)
                ->where('a1.start', '=', $ship->sector_id);

            for ($i = 2; $i <= $searchDepth; $i++) {
                $temp1 = $i - 1;
                $query->where("a$temp1.dest", '=', DB::raw("a$i.start"));
            }

            $query->where("a$searchDepth.dest", '=', $destSector);
            $query->where("a1.dest", '!=', DB::raw('a1.start'));

            for ($i = 2; $i <= $searchDepth; $i++) {
                $notIn = [DB::raw('a1.dest'), DB::raw('a1.start')];

                for ($temp2 = 2; $temp2 < $i; $temp2++) {
                    $notIn[] = DB::raw("a$temp2.dest");
                }

                $query->whereNotIn("a$i.dest", $notIn);
            }

            $query->orderBy('a1.start', 'desc');
            $query->orderBy('a1.dest', 'desc');

            for ($i = 2; $i <= $searchDepth; $i++) {
                $query->orderBy("a$i.dest", 'desc');
            }

            $result = $query
                ->distinct()
                ->limit(1)
                ->first();

            if (!is_null($result)) {
                $result = get_object_vars($result);
                $start = null;
                $path = [];

                foreach ($result as $key => $value) {
                    if ($key === 'start') {
                        $start = $value;
                        continue;
                    }

                    $ord = explode('_', $key)[1];
                    $path[$ord] = $value;
                }

                return new WarpRoute(
                    $user,
                    $start,
                    array_values($path)
                );
            }
        }
        return null;
    }
}