<script setup lang="ts">
/**
 * NavigationConfirmPopup.vue from The Kabal Invasion.
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
 * This component is the result of refactoring the legacy rsmove.php. It deals
 * with displaying both RealSpace information before engaging engines and the
 * result from the Navigation Computer (NavCom.) I initially wrote it to be
 * used from Galaxy.vue however, it's also used in Main.vue when the player
 * initiates RealSpace travel from one of their presets, or enters a sector
 * to navigate to by hand.
 *
 * The use of v-model for defining show state allows the parent component to
 * control rsMove and navCom state with this components responsibility being
 * entirely about displaying the data and asking for confirmation.
 *
 * ---
 *
 * TODO: add a player configurable toggle to enable "fast-navigation" which
 *       disables the confirmation and skips straight to displaying the
 *       result of their journey.
 *
 * ---
 *
 * This component contains front end elements from rsmove.php and navcomp.php.
 */

import TextButton from "@/Components/atoms/form/TextButton.vue";
import type {WarpRouteResource} from "@/types/resources/link";
import type {RealSpaceMove} from "@/types/galaxy-overview";
import PopUp from "@/Components/atoms/modal/PopUp.vue";
import {useAuth} from "@/Composables/useAuth";
import {ref, watch} from 'vue';
import NavcomSectorForm from "@/Components/molecules/navigation/NavcomSectorForm.vue";
import NavcomRealSpaceCourse from "@/Components/molecules/navigation/NavcomRealSpaceCourse.vue";
import NavcomPlottedCourse from "@/Components/molecules/navigation/NavcomPlottedCourse.vue";
import {useNavigationComputer} from "@/Composables/useNavigationComputer";
import {MovementMode} from "@/types/resources/movement";

type States = 'input' | 'loading' | 'error' | 'DisplayRealSpaceMove' | 'DisplayWarpMoves';

defineEmits(['update:modelValue']);

interface Props {
  modelValue: number;
  mode: MovementMode;
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'RealSpace',
});

const state = ref<States>('input');
const currentMode = ref<MovementMode>(props.mode);
const propSector = ref<number|undefined>(props.modelValue);
const navigation = ref<RealSpaceMove | WarpRouteResource | undefined>();
const {sector, config} = useAuth();

const {loading, compute} = useNavigationComputer();

const setNavigation = (v: RealSpaceMove | WarpRouteResource) => {
  navigation.value = v;
  state.value = currentMode.value === 'RealSpace'
    ? 'DisplayRealSpaceMove'
    : 'DisplayWarpMoves';

  console.log('===setNavigation', v, state.value, currentMode.value);
};

const setState = (s: States) => {
  if (s === 'input') navigation.value = undefined;
  if (s === 'DisplayRealSpaceMove') currentMode.value = 'RealSpace';
  if (s === 'DisplayWarpMoves') currentMode.value = 'Warp';
  state.value = s;
}

const doAction = async (a: string) => {
  if (a === 'input') {
    navigation.value = undefined;
    return setState('input');
  }

  if (a === 'navcom' && propSector.value) {
    currentMode.value = 'Warp';
    const result = await compute(propSector.value, 'Warp');
    if (result) setNavigation(result);
  }
}

watch(props, async (v) => {
  propSector.value = v.modelValue
  currentMode.value = v.mode;
  state.value = 'input';

  // If we have been passed a v-model value then load it and set the state
  if (v.modelValue >= 1) {
    const result = await compute(v.modelValue, v.mode);
    if (result) {
      setNavigation(result);
      return;
    }
  }

  navigation.value = undefined;
});
</script>

<template>
  <pop-up :show="modelValue !== -1 && !loading" @close="$emit('update:modelValue', -1)">
    <div class="p-6 w-3/5 border border-ui-orange-500 bg-ui-grey-900/90 border-x-4">
      <header class="flex items-center">
          <h2 class="flex-grow text-lg font-medium text-ui-yellow">{{ mode === 'RealSpace' ?  __('rsmove.l_rs_title') : __('navcomp.l_nav_title') }}</h2>
          <div class="flex space-x-4">
            <text-button @click="$emit('update:modelValue', -1)" class="underline">Close [ESC]</text-button>
          </div>
        </header>
        <navcom-sector-form
          v-if="state === 'input'"
          :mode="currentMode"
          v-model="propSector"
          @course="setNavigation"
        />
        <navcom-real-space-course
          v-else-if="state === 'DisplayRealSpaceMove'"
          :course="navigation"
          @action="doAction"
        />
        <navcom-plotted-course
          v-else-if="state === 'DisplayWarpMoves'"
          :plotted-course="navigation"
          @action="doAction"
        />
    </div>
  </pop-up>
</template>