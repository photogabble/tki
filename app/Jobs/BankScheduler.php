<?php

namespace App\Jobs;

use App\Models\BankAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BankScheduler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: Have the interest rate move around each day
        // TODO: Have interest added be a transaction logged

        $exponinter = config('game.ibank_interest') + 1;
        $expoloan = config('game.ibank_loaninterest') + 1;

        BankAccount::query()
            ->update([
                'balance' => DB::raw('balance * ' . $exponinter),
                'loan' => DB::raw('loan * ' . $expoloan)
            ]);
    }
}
