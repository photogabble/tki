<?php

namespace Tki\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// TODO: Replace with PlayersGateway

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
     * Returns number of players logged in within the past five minutes.
     * Implements PlayersGateway::selectPlayersLoggedIn()
     *
     * @param int $subMinutes
     * @return int
     */
    public static function loggedInCount(int $subMinutes = 5): int
    {
        return self::query()
            ->whereBetween('last_login', [Carbon::now(), Carbon::now()->subMinutes($subMinutes)])
            ->count();
    }
}
