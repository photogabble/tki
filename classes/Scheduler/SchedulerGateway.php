<?php declare(strict_types = 1);
/**
 * classes/Scheduler/SchedulerGateway.php from The Kabal Invasion.
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

namespace Tki\Scheduler; // Domain Entity organization pattern, Scheduler objects

// TODO: Rename Scheduler and move to app/Models

use Tki\Models\Scheduler;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $class_name
 * @property Carbon $last_run_at
 * @property Carbon $next_run_after
 */
class SchedulerGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_name',
        'last_run_at',
        'next_run_after',
        'multiplier'
    ];

    protected $casts = [
        'next_run_after' => 'datetime',
        'last_run_at' => 'datetime'
    ];

    /**
     * @todo refactor usage tobe Carbon aware
     * @return Carbon|null
     */
    public function selectSchedulerLastRun(): ?Carbon
    {
        /** @var Scheduler|null $last */
        $last = self::query()
            ->orderBy('last_run_at', 'DESC')
            ->first();

        return (is_null($last))
            ? null
            : $last->last_run_at;
    }

    public static function nextRun(): ?Carbon
    {
        /** @var Scheduler|null $last */
        $last = self::query()
            ->orderBy('next_run_after', 'DESC')
            ->first();

        return (is_null($last))
            ? null
            : $last->next_run_after;
    }
}
