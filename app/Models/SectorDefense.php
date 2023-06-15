<?php declare(strict_types=1);
/**
 * classes/Defenses/DefensesGateway.php from The Kabal Invasion.
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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SectorDefense extends Model
{
    protected $fillable = [
        'quantity'
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    // TODO: Rename to System?
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    /**
     * @param int $sector_id
     * @return Collection<SectorDefense>
     *@todo refactor usages for new Collection return
     */
    public function selectFighterDefenses(int $sector_id): Collection
    {
        return SectorDefense::query()
            ->where('sector_id', $sector_id)
            ->where('defense_type', 'F')
            ->orderBy('quantity', 'DESC')
            ->get();
    }

    /**
     * @param int $sector_id
     * @return Collection<SectorDefense>
     *@todo refactor usages for new Collection return
     */
    public function selectMineDefenses(int $sector_id): Collection
    {
        return SectorDefense::query()
            ->where('sector_id', $sector_id)
            ->where('defense_type', 'M')
            ->orderBy('quantity', 'DESC')
            ->get();
    }

    /**
     * @todo Refactor usages to use Sector -> SectorDefense relationship
     * @param int $sector_id
     * @return Collection
     */
    public function selectDefenses(int $sector_id): Collection
    {
        return SectorDefense::query()
            ->where('sector_id', $sector_id)
            ->get();
    }
}
