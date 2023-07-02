<?php declare(strict_types=1);
/**
 * Actions/EncounterActivity.php from The Kabal Invasion.
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

namespace Tki\Actions\Encounters;

use Illuminate\Http\Request;
use Tki\Models\Encounter;
use Tki\Models\User;

abstract class EncounterOption
{
    protected Encounter $model;

    protected Request $request;

    protected User $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = $request->user();
    }

    public function setEncounter(Encounter $model): void
    {
        $this->model = $model;
    }

    abstract public function execute(): bool;

    public function can(): bool
    {
        return true;
    }
}