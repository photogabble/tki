<?php

namespace Tki\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tki\Types\LogEnums;

/**
 * @property int $amount
 * @property int $bounty_on
 * @property int|null $placed_by
 *
 * @property-read User $bountyOn
 * @property-read User $placedBy
 */
class Bounty extends Model
{
    use HasFactory;

    public function bountyOn(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bounty_on');
    }

    public function placedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function cancel() : void
    {
        if (!is_null($this->placed_by)) {
            $characterName = $this->bountyOn->name;
            PlayerLog::writeLog($this->placed_by, LogEnums::BOUNTY_CANCELLED, "$this->amount|$characterName");
        }

        $this->delete();
    }

    public function collect(User $attacker): void
    {
        $characterName = $this->bountyOn->name;

        if (!is_null($this->placed_by)) {
            $placed = $this->placedBy->name;
        } else {
            $placed = __('bounty.l_by_thefeds');
        }

        // TODO: lang
        $attacker->depositCredits($this->amount, 'Bounty paid on ' . $characterName);

        PlayerLog::writeLog($attacker->id, LogEnums::BOUNTY_CLAIMED, "$this->amount|$characterName|$placed");
        PlayerLog::writeLog($this->placed_by, LogEnums::BOUNTY_PAID, "$this->amount|$characterName|");

        $this->delete();
    }
}
