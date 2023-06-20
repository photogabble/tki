<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tki\Models\User;
use Tki\Models\Zone;
use Tki\Types\ZonePermission;

/**
 * Refactored from zoneinfo.php.
 *
 * @mixin Zone
 */
class ZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        $name = ($this->id < 5)
            ? __('common.l_zname_'. $this->id)
            : $this->name;

        switch($this->id) {
            case 1:
                $owner = __('zoneinfo.l_zi_nobody');
                break;
            case 2:
                $owner = __('zoneinfo.l_zi_feds');
                break;
            case 3:
                $owner = __('zoneinfo.l_zi_traders');
                break;
            case 4:
                $owner = __('zoneinfo.l_zi_war');
                break;
            default:
                $owner = $this->owner->name;
        }

        $userCanEdit = false;

        if (
            ($this->team_zone && $user->team_id === $this->owner_id && $this->owner->creator === $user->id) ||
            (!$this->team_zone && $user->id === $this->owner_id)
        ) {
            $userCanEdit = true;
        }

        // TODO: implement isFriendly:
        //       Y: No danger, not a War Zone
        //       L: Is a War Zone, in a sector we had a battle
        //       N: Owned by team / player we are hostile towards


        return [
            'name' => $name,
            'owner' => $owner,
            'isFriendly' => ZonePermission::Allow,
            'isEditable' => $userCanEdit,
            'allow_beacon' => $this->allow_beacon,
            'allow_attack' => $this->allow_attack,
            'allow_planetattack' => $this->allow_planetattack,
            'allow_warpedit' => $this->allow_warpedit,
            'allow_planet' => $this->allow_planet,
            'allow_trade' => $this->allow_trade,
            'allow_defenses' => $this->allow_defenses,
            'max_hull' => $this->max_hull,
            'over_size' => $user->ship && $user->ship->hull > $this->max_hull,
        ];
    }
}
