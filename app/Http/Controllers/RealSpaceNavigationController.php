<?php declare(strict_types = 1);
/**
 * rsmove.php from The Kabal Invasion.
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

use Tki\Http\Resources\SectorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tki\Types\MovementMode;
use Tki\Models\Universe;
use Tki\Models\User;

final class RealSpaceNavigationController extends Controller
{
    private User $user;

    private Universe|null $destination;

    /**
     * GET: /navigation/real-space
     *
     * Refactored from rsmove.php this function handles calculating how many turns and how much
     * energy will be scooped from a RealSpace move. The response is then handled by
     * NavigationConfirmPopup.vue on the frontend for displaying to the player.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateRealSpaceRoute(Request $request): JsonResponse
    {
        if (!$navigation = $this->calculateRoute($request)) return new JsonResponse(['message' => 'Destination sector was not found'], 404);

        return new JsonResponse([
            'sector' => new SectorResource($this->destination),
            'can_navigate' => ($this->user->turns >= $navigation['turns']) && $this->user->ship->sector_id !== $this->destination->id,
            'is_same_sector' => $this->user->ship->sector_id === $this->destination->id,
            'turns' => number_format($navigation['turns']),
            'turns_available' => number_format($this->user->turns),
            'energy_scooped' => $navigation['energyScooped'],
        ]);
    }

    /**
     * POST: /navigation/real-space
     *
     * Refactored from rsmove.php this function handles moving the player through RealSpace
     * to their selected destination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function makeRealSpaceMove(Request $request): JsonResponse
    {
        if (!$navigation = $this->calculateRoute($request)) return new JsonResponse(['message' => 'Destination sector was not found'], 404);

        if ($this->user->turns < $navigation['turns']) {
            // rsmove.php also reset cleared_defenses, I don't yet know why
            $this->user->update(['cleared_defenses' => ' ']);
            return new JsonResponse(['message' => 'Not enough turns to make RealSpace move'], 400);
        }

        // TODO: refactor CheckDefenses::fighters to provide a JsonResponse on battle. This can sometimes result in
        //       the player _not_ making it to their final destination!
        // \Tki\CheckDefenses::fighters($pdo_db, $lang, $sector, $playerinfo, $tkireg, $title, $calledfrom);

        $this->user->ship->travelTo(
            $this->destination->id,
            MovementMode::RealSpace,
            $navigation['turns'],
            $navigation['energyScooped']
        );

        $this->user->decrement('turns', $navigation['turns']);
        $this->user->increment('turns_used', $navigation['turns']);

        // TODO: refactor CheckDefenses::mines to provide a JsonResponse on battle
        // \Tki\CheckDefenses::mines($pdo_db, $lang, $sector, $title, $playerinfo, $tkireg);

        // Set virtual attributes on the sector model so SectorResource loads all related data
        $this->destination->has_visited = true;
        $this->destination->is_current_sector = true;

        return new JsonResponse([
            'sector' => new SectorResource($this->destination),
            'turns' => number_format($navigation['turns']),
            'energy_scooped' => $navigation['energyScooped'],
        ]);
    }

    private function calculateRoute(Request $request) : ?array
    {
        $this->user = $request->user();

        $this->validate($request, [
            'sector' => ['required', 'exists:universes,id'],
        ]);

        $this->destination = Universe::queryForUser($this->user)
            ->find($request->get('sector', 0));

        if (!$this->destination) return null;

        return $this->user->ship->sector
            ->calculateRealSpaceMove($this->user->ship, $this->destination);
    }
}