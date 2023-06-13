<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    use HasFactory;

    /**
     *
     * Implements SchedulerGateway::selectSchedulerLastRun
     * @return Carbon|null
     */
    public static function lastRun(): ?Carbon
    {
        /** @var Scheduler|null $last */
        $last = self::query()
            ->orderBy('last_run', 'DESC')
            ->first();

        return (is_null($last))
            ? null
            : $last->last_run;
    }

    public static function nextRun(): Carbon
    {
        $seconds = config('scheduler.sched_ticks') * 60;

        if (!$lastRun = self::lastRun()) return Carbon::now();

        return $lastRun->addSeconds($seconds);
    }
}
