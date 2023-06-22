<?php declare(strict_types = 1);
/**
 * classes/Sectors/SectorsGateway.php from The Kabal Invasion.
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

// TODO: rename Sector and handle all the migration stuff

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Psy\Exception\DeprecatedException;

/**
 * @property string $beacon
 * @property-read Zone $zone
 * @property-read Collection|Link[] $links
 * @property-read Collection|Planet[] $planets
 * @property-read Collection|Ship[] $ships
 * @property-read Collection|SectorDefense[] $defenses
 * @property-read Collection $ports
 * @property-read Collection|MovementLog[] $movementLog
 */
class Universe extends Model
{
    use HasFactory;

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function planets(): HasMany
    {
        return $this->hasMany(Planet::class, 'sector_id');
    }

    public function defenses(): HasMany
    {
        return $this->hasMany(SectorDefense::class, 'sector_id');
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class, 'sector_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'start');
    }

    public function movementLog(): HasMany
    {
        return $this->hasMany(MovementLog::class, 'sector_id');
    }

    /**
     * @todo refactor all usages to use sector relationship
     * @deprecated
     * @param int $sector_id
     * @return array|bool
     */
    public function selectSectorInfo(int $sector_id): array | bool
    {
        throw new DeprecatedException('refactor usage to use sector relationship');
    }
}
