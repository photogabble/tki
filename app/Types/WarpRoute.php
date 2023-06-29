<?php declare(strict_types=1);
/**
 * app/Types/WarpRoute.php from The Kabal Invasion.
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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tki\Http\Resources\LinkResource;

class WarpRoute implements Arrayable {
    /**
     * @param LinkResource $start
     * @param LinkResource[] $waypoints
     */
    public function __construct(public LinkResource $start, public array $waypoints){}

    public function toUrlParam(Request $request): string
    {
        $key = sha1(implode(',', array_reduce($this->waypoints, function(array $carry, LinkResource $link) use ($request){
            $carry[] = $link->toArray($request)['to_sector_id'];
            return $carry;
        }, [$this->start->toArray($request)['to_sector_id']])));

        $cache = Cache::tags(['user-'.$request->user()->id, 'navcom']);

        $cache->forever($key, $this);

        return $key;
    }

    public static function fromUrlParam(Request $request, string $key = 'waypoints'): ?WarpRoute
    {
        $cache = Cache::tags(['user-'.$request->user()->id, 'navcom']);
        $n = $request->get($key);
        return $cache->get($request->get($key));
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'waypoints' => $this->waypoints,
        ];
    }
}