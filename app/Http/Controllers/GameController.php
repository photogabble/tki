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
use Tki\Http\Resources\SectorResource;
use Tki\Models\Universe;
use Tki\Models\User;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['ship', 'presets', 'ship.sector', 'ship.sector.links', 'ship.sector.zone']);

        // The above is passed through to the frontend via the Middleware attaching user
        // to all responses...

        return Inertia::render('Dashboard');
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