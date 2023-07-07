<?php declare(strict_types=1);
/**
 * Actions/Encounters/DefenseFighters/Pay.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2023 Simon Dann, The Kabal Invasion development team, Ron Harwood, and the BNT development team
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
 * ---
 *
 * This class is the refactored result of CheckDefenses::fighters
 *
 */

namespace Tki\Actions\Encounters\DefenseFighters;

use Tki\Actions\Encounters\EncounterOption;
use Tki\Actions\Toll;
use Tki\Models\PlayerLog;
use Tki\Models\SectorDefense;
use Tki\Types\LogEnums;

final class Pay extends EncounterOption
{
    public function execute(array $payload): bool
    {
        $sector = $this->user->ship->sector_id;
        $total_sec_fighters = SectorDefense::fightersCount($sector);

        $fightersTollFee = (int) round($total_sec_fighters * config('game.fighter_price') * 0.6);

        if ($this->user->credits < $fightersTollFee) {
            // Player doesn't have enough credits to pay, they must return to where they came
            // TODO: Should this cost turns? If the player hasn't the turns... what happens then?
            $this->user->ship->moveTo($this->encounter->movement->previous_id, $this->encounter->movement->mode);

            $this->encounter->persistData([
                'messages' => [
                    __('check_defenses.l_chf_notenoughcreditstoll'),
                    __('check_defenses.l_chf_movefailed')
                ],
                'tollFee' => $fightersTollFee,
            ]);

            return true; // This action is complete
        }

        $tollString = number_format($fightersTollFee);

        $this->encounter->persistData([
            'messages' => [
                __('check_defenses.l_chf_youpaidsometoll', ['toll' => $tollString]),
            ],
            'tollFee' => $fightersTollFee,
        ]);

        $this->user->spendCredits($fightersTollFee, 'Paid Toll Fee in sector: ' . $sector);

        Toll::distribute($sector, $fightersTollFee, $total_sec_fighters);
        PlayerLog::writeLog(
            $this->user->id,
            LogEnums::TOLL_PAID,
            "$tollString|$sector"
        );

        return true;
    }

    public function can(): bool
    {
        return true; // TODO: This should return false if the player hasn't the credits
    }
}