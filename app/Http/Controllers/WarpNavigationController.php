<?php declare(strict_types=1);
/**
 * move.php from The Kabal Invasion.
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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tki\Actions\NavCom;
use Tki\Http\Resources\SectorResource;
use Tki\Http\Resources\WarpRouteResource;
use Tki\Models\Universe;
use Tki\Models\User;
use Tki\Types\MovementMode;

class WarpNavigationController extends Controller
{
    public function makeWarpMove(Request $request): JsonResponse
    {
        $this->validate($request, [
            'sector' => ['required', 'exists:universes,id'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $sector = $request->get('sector');

        // Check players encounter's stack for any pending action.
        if (!is_null($user->currentEncounter)) {
            return new JsonResponse([
                'message' => __('move.l_move_encounter')
            ], 400);
        }

        // Check to see if the player has fewer turns available than needed and if so return 412
        if ($user->turns < $user->ship->warpTravelTurnCost()) {
            return new JsonResponse([
                'message' => __('move.l_move_turn')
            ], 412);
        }

        // Retrieve the warp link out of the current sector
        $link = $user->sector->links()
            ->where('dest', $sector)
            ->first();

        if (!$link) {
            return new JsonResponse([
                'message' => __('move.l_move_failed')
            ], 404);
        }

        $turns = $user->ship->warpTravelTurnCost();
        $movement = $user->ship->travelTo($sector, MovementMode::Warp, $turns, 0);
        $destination = Universe::queryForUser($user)->find($sector);

        return new JsonResponse([
            'movement' => $movement,
            'sector' => new SectorResource($destination),
            'turns' => $turns,
            'energy_scooped' => 0,
        ]);
    }

    public function calculateWarpMoves(Request $request, NavCom $navCom): JsonResponse|WarpRouteResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            'sector' => ['required', 'numeric', 'between:0,' . config('game.max_sectors'), 'exists:universes,id'],
        ]);

        $result = $navCom->calculate($user, $user->ship, (int)$request->get('sector'));

        if (is_null($result)) return new JsonResponse(null, 204);

        return new WarpRouteResource($result, $user);
    }
}