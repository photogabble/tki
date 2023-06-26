<?php declare(strict_types = 1);
/**
 * new2.php from The Kabal Invasion.
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


namespace Tki\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use Tki\Http\Controllers\Controller;
use Tki\Models\BankAccount;
use Tki\Models\Preset;
use Tki\Models\Ship;
use Tki\Models\User;
use Tki\Models\Zone;
use Tki\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Tki\Types\MovementMode;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'ship_name' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($maxTurns = DB::selectOne('SELECT MAX(turns_used + turns) AS mTurns FROM users')) {
            $maxTurns = is_null($maxTurns->mTurns)
                ? config('scheduler.max_turns')
                : min($maxTurns->mTurns, config('scheduler.max_turns'));
        } else {
            $maxTurns = config('scheduler.max_turns');
        }

        /** @var User $user */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'turns' => $maxTurns,
            'credits' => 1000,
        ]);

        // Create Ship
        // TODO: Create Ship Enum and have this largely placed there
        $ship = new Ship();
        $ship->owner_id = $user->id;
        $ship->ship_name = $request->ship_name;
        $ship->ship_destroyed = false;
        $ship->armor_pts = 10;
        $ship->ship_energy = 100;
        $ship->ship_fighters = 10;
        $ship->on_planet = false;
        $ship->dev_warpedit = 0;
        $ship->dev_genesis = 0;
        $ship->dev_beacon = 0;
        $ship->dev_emerwarp = 0;
        $ship->dev_escapepod = false;
        $ship->dev_fuelscoop = false;
        $ship->dev_minedeflector = 0;
        $ship->trade_colonists = true;
        $ship->trade_fighters = false;
        $ship->trade_torps = false;
        $ship->trade_energy = true;
        $ship->cleared_defenses = null;
        $ship->dev_lssd = false;
        $ship->sector_id = 1;
        $ship->save();

        $user->ship()->associate($ship);

        // Move ship to starting sector
        $ship->moveTo(1, MovementMode::Spawn);

        // Create Players Zone record
        Zone::create([
            'name' => $user->name . "'s Territory",
            'owner_id' => $user->id,
            'team_zone' => false,
        ]);

        // Create Bank Account
        BankAccount::create(['user_id' => $user->id]);

        // Create Presets
        $presets = [];
        for ($i = 0; $i < config('game.max_presets'); $i++) {
            $presets[] = new Preset();
        }
        $user->presets()->saveMany($presets);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
