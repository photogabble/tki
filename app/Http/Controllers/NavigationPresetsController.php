<?php declare(strict_types = 1);
/**
 * preset.php from The Kabal Invasion.
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
 * ---
 *
 * This Controller is the refactored result of preset.php. It handles storing
 * player preset real space locations for easy navigation.
 *
 */

namespace Tki\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tki\Models\Preset;

class NavigationPresetsController extends Controller {
    public function store(Preset $preset, Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'sector' =>  ['required', 'numeric', 'between:0,'.config('game.max_sectors'), 'exists:systems,id'],
        ]);

        $preset->update([
            'preset' => $request->get('sector', 1),
        ]);

        if ($request->expectsJson()) {
            return new JsonResponse(null, 204);
        }

        return redirect()->back();
    }
}