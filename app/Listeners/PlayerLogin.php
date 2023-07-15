<?php

namespace Tki\Listeners;

use Carbon\Carbon;
use Tki\Actions\Score;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PlayerLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $event->user->update([
            'last_login' => Carbon::now(),
        ]);

        Score::updateScore($event->user);

        // TODO: Copy functionality from login2.php
    }
}
