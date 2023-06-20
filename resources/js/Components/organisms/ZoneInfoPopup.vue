<script setup lang="ts">
/**
 * zoneinfo.php from The Kabal Invasion.
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
 */

import ZonePermission from "@/Components/atoms/ZonePermission.vue";
import PopUp from "@/Components/atoms/modal/PopUp.vue";
import {ZoneResource} from "@/types/zone-info";

defineProps<{
  modelValue: Boolean;
  zone: ZoneResource;
}>();

defineEmits(['update:modelValue']);
</script>

<template>
    <pop-up :show="modelValue" @close="$emit('update:modelValue', false)">
        <!-- TODO: Add zoneedit.php, ported to Vue -->

        <div class="p-6 w-3/5 border border-ui-orange-500 bg-ui-grey-900/90 border-x-4">
            <header class="flex items-center">
                <h2 class="flex-grow text-lg font-medium text-ui-yellow">Zone Info</h2>
                <div class="flex space-x-4">
                    <button v-if="zone.isEditable" class="underline">You can [e]dit this zone</button>
                    <button @click="$emit('update:modelValue', false)" class="underline">Close [ESC]</button>
                </div>
            </header>
            <p class="mt-1 text-sm text-white">
                {{ zone.name }}: <span :class="{'text-green-600': zone.isFriendly === 'Y', 'text-ui-orange-500': zone.isFriendly === 'L', 'text-red-600': zone.isFriendly === 'N'}">{{ zone.owner }}</span>.
            </p>
            <p class="mt-1 text-sm text-white">
                {{__('report.l_beacons')}} are <zone-permission :value="zone.allow_beacon" />,
                Attacking is <zone-permission :value="zone.allow_attack" />, deploying Sector Defenses is
                <zone-permission :value="zone.allow_defenses" />, Warp Editors are <zone-permission :value="zone.allow_warpedit" />,
                Planets are <zone-permission :value="zone.allow_planet" /> and Port Trading is <zone-permission :value="zone.allow_trade" />.
            </p>

            <p class="mt-1 text-sm text-white">
                Maximum average tech level allowed:
                <span :class="zone.over_size ? 'text-red-600' : 'text-green-600'">{{ zone.max_hull === 0 ? __('zoneinfo.l_zi_ul')  : zone.max_hull }}</span>
            </p>
        </div>
    </pop-up>
</template>