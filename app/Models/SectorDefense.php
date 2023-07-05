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
use Tki\Types\DefenseType;

/**
 * @property DefenseType $defense_type
 * @property int $quantity
 * @property int $owner_id
 * @property-read User $owner
 * @property-read Universe $sector
 */
class SectorDefense extends Model
{
    protected $fillable = [
        'quantity'
    ];

    protected $casts = [
        'defense_type' => DefenseType::class,
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }

    // TODO: Rename to System?
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public static function inSector(int $sector, DefenseType $type): Collection
    {
        return SectorDefense::with('owner')
            ->where('sector_id', $sector)
            ->where('defense_type', $type)
            ->orderBy('quantity', 'DESC')
            ->get();
    }

    /**
     * @param int $sector_id
     * @return Collection<SectorDefense>
     *@todo refactor usages for new Collection return
     */
    public static function fighters(int $sector_id): Collection
    {
        return SectorDefense::with('owner')
            ->where('sector_id', $sector_id)
            ->where('defense_type', DefenseType::Fighters)
            ->orderBy('quantity', 'DESC')
            ->get();
    }

    public static function fightersCount(int $sector_id): int
    {
        return SectorDefense::with('owner')
            ->where('sector_id', $sector_id)
            ->where('defense_type', DefenseType::Fighters)
            ->orderBy('quantity', 'DESC')
            ->sum('quantity');
    }

    public static function fightersHaveToll(int $sector_id): bool
    {
        return SectorDefense::query()
            ->where('sector_id', $sector_id)
            ->where('defense_type', DefenseType::Fighters)
            ->where('quantity', '>', 0)
            ->where('fm_setting', 'toll')
            ->exists();
    }

    /**
     * @param int $sector_id
     * @return Collection<SectorDefense>
     *@todo refactor usages for new Collection return
     */
    public static function mines(int $sector_id): Collection
    {
        return SectorDefense::with('owner')
            ->where('sector_id', $sector_id)
            ->where('defense_type', DefenseType::Mines)
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
