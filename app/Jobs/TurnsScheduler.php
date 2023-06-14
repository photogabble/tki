<?php

namespace App\Jobs;

use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// sched_turns.php
class TurnsScheduler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info(__('scheduler.l_sched_turns_title'));
        Log::info(__('scheduler.l_sched_turns_note'));

        Ship::query()
            ->where('turns', '<', config('scheduler.max_turns'))
            ->update([
                'turns' => DB::raw('LEAST(turns + '. config('scheduler.turns_per_tick') .', '.config('scheduler.max_turns').')')
            ]);
    }
}
