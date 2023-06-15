<?php

namespace Tki\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// TODO: Replace with SchedulerGateway

/**
 * @property string $class_name
 * @property Carbon $last_run_at
 * @property Carbon $next_run_after
 */
class Scheduler extends Model
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
     * Implements SchedulerGateway::selectSchedulerLastRun
     * @return Carbon|null
     */
    public static function lastRun(): ?Carbon
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
