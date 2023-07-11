<?php declare(strict_types=1);
/**
 * Types/WarpRoute.php from The Kabal Invasion.
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
use Illuminate\Support\Facades\Cache;
use Tki\Http\Resources\LinkResource;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Tki\Models\Universe;
use Tki\Models\User;

class WarpRoute implements Arrayable
{

    /** @var int[]  */
    public array $sectors;

    public LinkResource $start;

    /** @var LinkResource[] */
    public array $waypoints;

    /**
     * @param User $user
     * @param int $start
     * @param int[] $waypoints
     */
    public function __construct(User $user, int $start, array $waypoints)
    {
        $this->sectors = [$start, ...$waypoints];

        $sectors = Universe::queryForUser($user)
            ->whereIn('id', $this->sectors)
            ->get();

        $this->start = new LinkResource($sectors->where('id', $start)->first());
        $this->waypoints = array_map(function(int $id) use ($sectors){
            return new LinkResource($sectors->where('id', $id)->first());
        }, $waypoints);
    }

    public function toUrlParam(Request $request): string
    {
        $key = sha1(implode(',', $this->sectors));

        $cache = Cache::tags(['user-' . $request->user()->id, 'navcom']);

        $cache->forever($key, $this);

        return $key;
    }

    public static function fromUrlParam(Request $request, string $key = 'waypoints'): ?WarpRoute
    {
        if (!$request->has($key)) return null;

        $cache = Cache::tags(['user-' . $request->user()->id, 'navcom']);
        return $cache->get($request->get($key));
    }

    public function contains(int $sector): bool
    {
        return in_array($sector, $this->sectors);
    }

    public function remaining(int $sector): int
    {
        $key = array_search($sector, $this->sectors);
        if ($key === false) return 0;

        return count($this->sectors) - ($key + 1);
    }

    public function next(int $sector): ?int
    {
        $key = array_search($sector, $this->sectors);
        if ($key === false) return null;

        return $this->sectors[$key+1] ?? null;
    }

    public function toArray(): array
    {
        $request = Container::getInstance()->make('request');
        return [
            'start' => $this->start->toArray($request),
            'waypoints' => array_map(function (LinkResource $link) use ($request) {
                return $link->toArray($request);
            }, $this->waypoints),
        ];
    }
}