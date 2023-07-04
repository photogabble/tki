<?php declare(strict_types = 1);
/**
 * classes/Ships/ShipsGateway.php from The Kabal Invasion.
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Tki\Types\MovementMode;

/**
 * @property bool $trade_colonists
 * @property bool $trade_fighters
 * @property bool $trade_torps
 * @property bool $trade_energy
 * @property bool $cleared_defenses
 *
 * @property string $ship_name
 * @property Carbon $destroyed_at
 * @property int $hull
 * @property int $engines
 * @property int $power
 * @property int $computer
 * @property int $sensors
 * @property int $beams
 * @property int $torp_launchers
 * @property int $torps
 * @property int $shields
 * @property int $armor
 * @property int $armor_pts
 * @property int $cloak
 * @property int $credits
 * @property int $sector_id
 * @property int $ship_ore
 * @property int $ship_organics
 * @property int $ship_goods
 * @property int $ship_energy
 * @property int $ship_colonists
 * @property int $ship_fighters
 * @property int $ship_damage
 * @property int $turns
 *
 * @property bool $on_planet
 *
 * @property int $dev_warpedit
 * @property int $dev_genesis
 * @property int $dev_beacon
 * @property int $dev_emerwarp
 * @property bool $dev_escapepod
 * @property bool $dev_fuelscoop
 * @property bool $dev_lssd
 * @property int $dev_minedeflector
 *
 * @property int $owner_id
 * @property-read Universe $sector
 * @property-read User|null $owner
 */
class Ship extends Model
{
    use HasFactory;

    protected $fillable = [
        'sector_id',
        'cleared_defenses',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function setDestroyed(): bool
    {
        $this->destroyed_at = Carbon::now();
        // TODO: Dispatch Event which can trigger a new ship being provided
        return $this->save();
    }

    /**
     * @todo refactor usages to be Model aware
     * @todo then remove $ship_id
     * @param int $ship_id
     * @param int $rating
     * @return void
     */
    public function updateDestroyedShip(int $ship_id, int $rating = 0): void
    {
        $this->engines = 0;
        $this->power = 0;
        $this->computer = 0;
        $this->sensors = 0;
        $this->beams = 0;
        $this->torp_launchers = 0;
        $this->torps = 0;
        $this->armor = 0;
        $this->armor_pts = 100;
        $this->cloak = 0;
        $this->shields = 0;
        $this->sector_id = 1;
        $this->rating = $rating;
        $this->cleared_defense = ' ';

        $this->ship_ore = 0;
        $this->ship_organics = 0;
        $this->ship_energy = 1000;
        $this->ship_colonists =0;
        $this->ship_goods = 0;
        $this->ship_fighters = 100;
        $this->ship_damage =  0;
        $this->credits = 1000;
        $this->on_planet = false;
        $this->dev_warpedit = 0;
        $this->dev_genesis =0;
        $this->dev_beacon = 0;
        $this->dev_emerwarp = 0;
        $this->dev_escapepod = false;
        $this->dev_fuelscoop = false;
        $this->dev_minedeflector = 0;
        $this->ship_destroyed =false;
        $this->dev_lssd = false;

        $this->save();
    }

    /**
     * Moving the ship, does just that, with no checks to see if the ship can
     * travel there under its own power. To be used for spawning and towing.
     *
     * @param int $sectorId
     * @return void
     */
    public function moveTo(int $sectorId, MovementMode $mode): MovementLog
    {
        return MovementLog::writeLog($this->owner_id, $sectorId, $mode);
    }

    /**
     * This function handles moving the player between sectors, it returns a movement log which
     * can contain events that have happened during travel.
     *
     * @param int $sectorId
     * @param MovementMode $mode
     * @param int $turnsUsed
     * @param int $energyScooped
     * @return MovementLog
     */
    public function travelTo(int $sectorId, MovementMode $mode, int $turnsUsed, int $energyScooped): MovementLog
    {
        $this->owner->spendTurns($turnsUsed);

        // energyScooped is calculated by Move::calcFuelScooped, it will never go over our max energy
        $this->increment('ship_energy', $energyScooped);

        // Make Move, this might be undone depending on how CheckDefenses::fighters computes.
        $movement = MovementLog::writeLog($this->owner_id, $sectorId, $mode, $turnsUsed, $energyScooped);
        $this->update(['sector_id' => $sectorId]);

        // TODO: refactor CheckDefenses::fighters to provide a JsonResponse on battle. This can sometimes result in
        //       the player _not_ making it to their final destination!
        // \Tki\CheckDefenses::fighters($pdo_db, $lang, $sector, $playerinfo, $tkireg, $title, $calledfrom);

        // CheckDefenses::fighters may create a MovementLogEvent of EventType::Fighters attach it to $movement
        // and then return without running CheckDefenses::mines so the front end can display the retreat,
        // pay, fight or cloak options to the player. For example, resolving MovementLogEvent with retreat
        // will trigger Ship::moveTo(previous).

        // TODO: refactor CheckDefenses::mines to provide a JsonResponse on battle
        // \Tki\CheckDefenses::mines($pdo_db, $lang, $sector, $title, $playerinfo, $tkireg);

        return $movement;
        // TODO: Implement, travelling should cost some energy, if
        //       ship doesn't have enough to make the movement
        //       then throw an exception.
    }
}
