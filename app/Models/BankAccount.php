<?php declare(strict_types=1);
/**
 * classes/Ibank/IbankGateway.php from The Kabal Invasion.
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

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $casts = [
        'loaned_on' => 'datetime'
    ];

    /**
     * @param array $playerinfo
     * @param int $credits
     * @return void
     * @todo refactor so $playerinfo is instance of Ship
     */
    public function reduceIbankCredits(array $playerinfo, int $credits): void
    {
        BankAccount::where('ship_id', $playerinfo['ship_id'])->decrement('credits', $credits);
    }

    /**
     * @param int $ship_id
     * @return int
     */
    public function selectIbankScore(int $ship_id): int
    {
        $account = BankAccount::where('ship_id', $ship_id)->first();
        return is_null($account)
            ? 0
            : $account->balance - $account->loan;
    }

    /**
     * @param int $ship_id
     * @return array
     * @todo refactor usages to use Ship -> BankAccount relationship
     */
    public function selectIbankAccount(int $ship_id): BankAccount
    {
        return BankAccount::where('ship_id', $ship_id)->first();
    }

    /**
     * @param int $ship_id
     * @return int
     * @todo refactor usage to use Ship -> BankAccount relationship
     */
    public function selectIbankLoanTime(int $ship_id): int
    {
        $account = BankAccount::where('ship_id', $ship_id)->first();
        return $account->loan_time->unix();
    }

    /**
     * @param int $ship_id
     * @return array
     * @todo refactor usage to use Ship -> BankAccount relationship
     */
    public function selectIbankLoanandTime(int $ship_id): array
    {
        $account = BankAccount::where('ship_id', $ship_id)->first();
        return ['time' => $account->loan_time->unix(), 'loan' => $account->loan];
    }
}
