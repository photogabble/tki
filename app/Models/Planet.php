<?php declare(strict_types = 1);
/**
 * classes/Planets/PlanetsGateway.php from The Kabal Invasion.
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

namespace Tki\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Psy\Exception\DeprecatedException;

/**
 * @property int organics
 * @property int $ore
 * @property int $goods
 * @property int $energy
 * @property int $colonists
 * @property int $torps
 * @property int $fighters
 * @property int $credits
 * @property int|null $owner_id
 * @property int|null $team_id
 * @property-read Team|null $team
 * @property-read User|null $owner
 */
class Planet extends Model
{
    use HasFactory;

    protected $fillable = [
        'organics',
        'ore',
        'goods',
        'energy',
        'colonists',
        'torps',
        'fighters',
        'credits',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?? __('common.l_unnamed') ,
        );
    }

    /**
     * Refactored from CalcLevels::avgTech
     * @todo update so that a planet's tech level is independent from the players current ship
     * @return float
     */
    public function avgTechLevel(): float {
        if (!$this->owner) return 0;
        if (!$this->owner->ship) return 0;

        return $this->owner->ship->avgTechLevel();
    }

    /**
     * Returns the planet's integer level based upon its avg tech level.
     * Refactored from main.php.
     * @return int
     */
    public function level(): int {
        if (!$this->owner) return 0;

        $planetAvg = $this->avgTechLevel();

        if ($planetAvg < 8) return 0;
        if ($planetAvg < 12) return 1;
        if ($planetAvg < 16) return 2;
        if ($planetAvg < 20) return 3;

        return 4;
    }

    public function setDefeated(int $planettorps): void
    {
        $this->torps -= $planettorps;
        $this->base = false;
        $this->defeated = true;
        $this->owner_id = null;
        $this->fighters = 0;

        $this->save();
    }

    /**
     * @todo update usages to use setDefeated method on this Model
     * @deprecated
     * @param \PDO $pdo_db
     * @param array $planetinfo
     * @param int $planettorps
     * @return void
     */
    public function updateDefeatedPlanet(\PDO $pdo_db, array $planetinfo, int $planettorps): void
    {
        throw new DeprecatedException('use setDefeated method');
    }

    /**
     * @todo refactor usage to pass a model as $playerinfo
     * @param \PDO $pdo_db
     * @param \Tki\Registry $tkireg
     * @param array $playerinfo
     * @param string $planetname
     * @return void
     */
    public function genesisAddPlanet(\PDO $pdo_db, \Tki\Registry $tkireg, array $playerinfo, string $planetname): void
    {
        Planet::create([
            'sector_id' => $playerinfo['sector'],
            'name' => $planetname,
            'organics' => 0,
            'ore' => 0,
            'goods' => 0,
            'energy' => 0,
            'colonists' => 0,
            'credits' => 0,
            'fighters' => 0,
            'torps' => 0,
            'owner_id' => $playerinfo['ship_id'],
            'team_id' => null,
            'base' => false,
            'sells' => false,
            'prod_organics' => config('game.default_prod_organics'),
            'prod_ore' => config('game.default_prod_ore'),
            'prod_goods' => config('game.default_prod_goods'),
            'prod_energy' => config('game.default_prod_energy'),
            'prod_fighters' => config('game.default_prod_fighters'),
            'prod_torp' => config('game.default_prod_torp'),
            'defeated' => false,
        ]);

        // Todo: $playerinfo will eventually be Ship... or User... or well you know what I mean
        // TODO: by the time we get here its assumed that player has enough dev_genesis and turns to do this
        /** @var Ship $ship */
        $ship = Ship::find($playerinfo['ship_id']);
        $ship->update([
            'turns_used' => $ship->turns_used + 1,
            'turns' => max($ship->turns - 1, 0),
            'dev_genesis' => max($ship->dev_genesis - 1),
        ]);
    }

    /**
     * @param int $sector_id
     * @return Collection<Planet>
     *@todo refactor usage to be model aware
     */
    public function selectPlanetInfo(int $sector_id): Collection
    {
        return Planet::where('sector_id', $sector_id)->get();
    }

    /**
     * @todo refactor usage to use selectPlanetInfo
     * @deprecated
     * @param int $sector_id
     * @return array|null
     */
    public function selectAllPlanetInfo(int $sector_id): ?array
    {
        throw new DeprecatedException('use selectPlanetInfo');
    }

    /**
     * @param int $planet_id
     * @return Planet|null
     *@todo refactor usage to be model aware
     */
    public function selectPlanetInfoByPlanet(int $planet_id): ?Planet
    {
        return Planet::find($planet_id);
    }

    /**
     * @param int $ship_id
     * @return Collection<Planet>
     *@todo refactor usage to use planet relationship on user/ship/whatever...
     */
    public function selectAllPlanetInfoByOwner(int $ship_id): Collection
    {
        return Planet::where('owner_id', $ship_id)->get();
    }

    /**
     * @todo refactor usage to use planet relationship on user/ship/whatever...
     * @param int $ship_id
     * @return Collection
     */
    public function selectSomePlanetInfoByOwner(int $ship_id): Collection
    {
        return Planet::where('owner_id', $ship_id)->orderBy('sector_id', 'ASC')->get();
    }
}
