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

namespace Tki\Players; // Domain Entity organization pattern, Players objects

// TODO: Rename User and move to app/Models

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PlayersGateway extends Authenticatable
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
     * @todo refactor all usages to use authenticated user as provided by Laravel!
     * @todo also this used to use the ships table instead of users, things need to be aware of that
     * @param string|null $email
     * @return PlayersGateway|null
     */
    public function selectPlayerInfo(?string $email): ?PlayersGateway
    {
        return PlayersGateway::where('email', $email)->first();
    }

    /**
     * @todo refactor all usages to be aware that this returns User rather than Ship
     * @param int|null $ship_id
     * @return array
     */
    public function selectPlayerInfoById(?int $ship_id): array
    {
        return PlayersGateway::find($ship_id);
    }
}
