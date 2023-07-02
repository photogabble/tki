<?php declare(strict_types = 1);
/**
 * classes/Players/PlayersGateway.php from The Kabal Invasion.
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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $credits
 * @property int $turns
 * @property int $turns_used
 * @property int $score
 * @property-read Ship|null $ship // If a player has no ship (escape pods are ships) then they have died in space
 * @property-read Preset|Collection $presets
 * @property-read Team|null $team
 * @property-read MovementLog[]|Collection $movementLog
 * @property-read Universe $sector
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login',
        'turns_used',
        'turns',
        'credits'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime'
    ];

    /**
     * All Players have a ship or else they are a corpse
     * floating in space.
     *
     * @return BelongsTo
     */
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function sector(): HasOneThrough
    {
        return $this->hasOneThrough(Universe::class, Ship::class, 'id', 'id', 'ship_id' , 'sector_id');
    }

    public function presets(): HasMany
    {
        return $this->hasMany(Preset::class)->orderBy('id', 'asc');
    }

    public function movementLog(): HasMany
    {
        return $this->hasMany(MovementLog::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    /**
     * Players might have multiple Encounters which need to be dealt with one after the other,
     * for example Hostile followed by Death.
     * @return HasOne
     */
    public function currentEncounter(): HasOne
    {
        return $this->hasOne(Encounter::class)
            ->whereNull('completed_at')
            ->oldestOfMany();
    }

    public function hasVisitedSector(int $sectorId) : bool
    {
        $cache = Cache::tags(['user-'.$this->id]);

        if (!$value = $cache->get('visited_'.$sectorId)) {
            $value = $this->movementLog()->where('sector_id', $sectorId)->exists();
            $cache->forever('visited_'.$sectorId, $value);
        }

        return $value;
    }

    public function spendTurns(int $amount): void
    {
        $this->decrement('turns', $amount);
        $this->increment('turns_used', $amount);
    }

    /**
     * @todo refactor to use Carbon and an offset in minutes
     * @param string $since_stamp
     * @param string $cur_time_stamp
     * @return int
     */
    public function selectPlayersLoggedIn(string $since_stamp, string $cur_time_stamp): int
    {
        // SQL call that selected the number (count) of logged in ships (should be players)
        // where last login time is between the since_stamp, and the current timestamp ($cur_time_stamp)
        // But it excludes kabal.

        return self::query()
            ->whereBetween('last_login', [$since_stamp, $cur_time_stamp])
            ->where('email', 'NOT LIKE', '%@kabal')
            ->count();
    }

    /**
     * @param string|null $email
     * @return User|null
     * @todo refactor all usages to use authenticated user as provided by Laravel!
     * @todo also this used to use the ships table instead of users, things need to be aware of that
     */
    public function selectPlayerInfo(?string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @todo refactor all usages to be aware that this returns User rather than Ship
     * @param int|null $ship_id
     * @return array
     */
    public function selectPlayerInfoById(?int $ship_id): array
    {
        return User::find($ship_id);
    }
}
