<?php

namespace App\Console;

use App\Jobs\ApocalypseScheduler;
use App\Jobs\BankScheduler;
use App\Jobs\DegradeScheduler;
use App\Jobs\GovenorScheduler;
use App\Jobs\KabalScheduler;
use App\Jobs\NewsScheduler;
use App\Jobs\PlanetsScheduler;
use App\Jobs\PortsScheduler;
use App\Jobs\RankingScheduler;
use App\Jobs\TowScheduler;
use App\Jobs\TurnsScheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1 Minute
        |--------------------------------------------------------------------------
        |
        | GovenorScheduler => sched_thegovernor.php
        | PortsScheduler => sched_ports.php
        |
        */
        $schedule->job(GovenorScheduler::class)->everyMinute()->withoutOverlapping();
        $schedule->job(PortsScheduler::class)->everyMinute()->withoutOverlapping();

        /*
        |--------------------------------------------------------------------------
        | 2 Minutes
        |--------------------------------------------------------------------------
        |
        | TurnsScheduler => sched_turns.php
        | KabalScheduler => sched_kabal.php
        | TowScheduler => sched_tow.php
        | BankScheduler => sched_ibank.php
        | PlanetsScheduler => sched_planets.php
        |
        */
        $schedule->job(TurnsScheduler::class)->everyTwoMinutes()->withoutOverlapping();
        $schedule->job(KabalScheduler::class)->everyTwoMinutes()->withoutOverlapping();
        $schedule->job(TowScheduler::class)->everyTwoMinutes()->withoutOverlapping();
        $schedule->job(BankScheduler::class)->everyTwoMinutes()->withoutOverlapping();;
        $schedule->job(PlanetsScheduler::class)->everyTwoMinutes()->withoutOverlapping();;

        /*
        |--------------------------------------------------------------------------
        | 5 Minutes
        |--------------------------------------------------------------------------
        |
        | DegradeScheduler => sched_degrade.php
        |
        */
        $schedule->job(DegradeScheduler::class)->everyFiveMinutes()->withoutOverlapping();

        /*
        |--------------------------------------------------------------------------
        | 15 Minutes
        |--------------------------------------------------------------------------
        |
        | NewsScheduler => sched_news.php
        | ApocalypseScheduler => sched_apocalypse.php
        |
        */
        $schedule->job(NewsScheduler::class)->everyFifteenMinutes()->withoutOverlapping();
        $schedule->job(ApocalypseScheduler::class)->everyFifteenMinutes()->withoutOverlapping();

        /*
        |--------------------------------------------------------------------------
        | 30 Minutes
        |--------------------------------------------------------------------------
        |
        | RankingScheduler => sched_ranking.php
        |
        */
        $schedule->job(RankingScheduler::class)->everyThirtyMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
