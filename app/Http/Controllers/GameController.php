<?php declare(strict_types=1);
/**
 * GameController.php from The Kabal Invasion.
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

namespace Tki\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Tki\Actions\NavCom;
use Tki\Http\Resources\EncounterResource;
use Tki\Http\Resources\SectorResource;
use Tki\Http\Resources\WarpRouteResource;
use Tki\Models\Universe;
use Tki\Models\User;
use Tki\Types\WarpRoute;

class GameController extends Controller
{

    public function __construct()
    {
        // Only CacheResponse for galaxy map as that gets invalidated on navigation.
        $this->middleware(function(Request $request, $next) {
            if (!$request->routeIs('explore')) return $next($request);
            $cache = app(CacheResponse::class);

            // Need to handle in this way to set user id for cache tag.
            return $cache->handle($request, $next, 'galaxy-'.$request->user()->id);
        });
    }

    public function index(Request $request, NavCom $navCom): Response
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['ship', 'presets', 'ship.sector', 'ship.sector.links', 'ship.sector.zone']);

        // The above is passed through to the frontend via the Middleware attaching user
        // to all responses...

        // Page Props
        $props = [
            'encounters' => EncounterResource::collection($user->pendingEncounters()),
        ];

        // If player is following an autopilot route then waypoints will be set containing the
        // route as computed by the NavCom:
        if ($route = WarpRoute::fromUrlParam($request)) {
            if ($route->contains($user->ship->sector_id)) {
                $props['route'] = new WarpRouteResource($route, $user);
            }
        }

        return Inertia::render('Dashboard', $props);
    }

    public function galaxyMap(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['ship', 'ship.sector']);

        return Inertia::render('Galaxy', [
            'sectors' => fn() => SectorResource::collection(
                Universe::queryForUser($user)->paginate(1000)->withQueryString()
            ),
        ]);
    }
}
