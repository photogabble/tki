<?php declare(strict_types = 1);
/**
 * scheduler/sched_ibank.php from The Kabal Invasion.
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

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Log;

class BankScheduler extends ScheduledTask
{
    /**
     * Apply interest rate to all bank accounts
     *
     * @todo Have the interest rate move around each day
     * @todo Have interest added be a transaction logged
     * @return void
     */
    public function run(): void
    {
        // l_sched_ibank_title

        $exponinter = pow(config('game.ibank_interest') + 1, $this->multiplier);
        $expoloan = pow(config('game.ibank_loaninterest') + 1, $this->multiplier);

        BankAccount::query()
            ->update([
                'balance' => DB::raw('balance * ' . $exponinter),
                'loan' => DB::raw('loan * ' . $expoloan)
            ]);

        // TODO: Add to translations table
        // l_sched_ibank_note
        Log::info("BankScheduler: Applied interest rates: balance: $exponinter, loan: $expoloan");
    }

    public function periodMinutes(): int
    {
        return 2;
    }

    public function maxCatchup(): int
    {
        return 1; // Only need to run once as we use multiplier to fill in lost time
    }
}