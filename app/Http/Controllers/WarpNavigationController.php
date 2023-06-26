<?php declare(strict_types = 1);
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
use Tki\Http\Resources\SectorResource;
use Tki\Models\Universe;
use Tki\Models\User;
use Tki\Types\MovementMode;

class WarpNavigationController extends Controller {
    public function makeWarpMove(Request $request): JsonResponse {
        $this->validate($request, [
            'sector' => ['required', 'exists:universes,id'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $sector = $request->get('sector');

        // Check to see if the player has less than one turn available
        // and if so return 412
        if ($user->turns < 1) {
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

        // TODO: refactor CheckDefenses::fighters to provide a JsonResponse on battle. This can sometimes result in
        //       the player _not_ making it to their final destination!
        // \Tki\CheckDefenses::fighters($pdo_db, $lang, $sector, $playerinfo, $tkireg, $title, $calledfrom);

        // TODO: make warp turns dependant upon ship clasification
        $user->decrement('turns', 1);
        $user->increment('turns_used', 1);

        $user->ship->travelTo($sector, MovementMode::Warp, 1, 0);

        // TODO: refactor CheckDefenses::mines to provide a JsonResponse on battle
        // \Tki\CheckDefenses::mines($pdo_db, $lang, $sector, $title, $playerinfo, $tkireg);

        $destination = Universe::queryForUser($user)->find($sector);
        return new JsonResponse([
            'sector' => new SectorResource($destination),
            'turns' => 1,
            'energy_scooped' => 0,
        ]);
    }

    public function calculateWarpMoves(Request $request): JsonResponse {
        // TODO: Refactor navcomp.php into action and invoke here
    }
}