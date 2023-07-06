<script setup lang="ts">
/**
 * NavigationReportPopup.vue from The Kabal Invasion.
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

import PopUpWithHeader from "@/Components/molecules/modal/PopUpWithHeader.vue";

defineProps<{
  modelValue: Boolean;
}>();

const emit = defineEmits(['update:modelValue']);

// Navigation Mode: Real Space or Warp
// Autopilot Engaged: Yes or No
// Has Event:
// - Fighter Demanding


const cancelNavigation = () => {
  emit('update:modelValue', false);
}
</script>

<template>
  <pop-up-with-header title="Auto Pilot" :show="modelValue" @close="cancelNavigation" close-button close-button-text="Cancel Auto Pilot">

    <p>
      TODO...
    </p>

  </pop-up-with-header>
</template>