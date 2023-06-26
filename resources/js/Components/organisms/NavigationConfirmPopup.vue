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

import InputError from "@/Components/atoms/form/InputError.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import type {RealSpaceMove} from "@/types/galaxy-overview";
import PopUp from "@/Components/atoms/modal/PopUp.vue";
import {useStack} from "@/Composables/useStack";
import {useAuth} from "@/Composables/useAuth";
import {useApi} from "@/Composables/useApi";
import {router} from "@inertiajs/vue3";
import {ref, watch} from 'vue';

type States = 'input' | 'loading' | 'error' | 'loadedRealSpace' | 'loadedWarpMoves';
type Modes  = 'RealSpace' | 'Warps';

defineEmits(['update:modelValue']);

interface Props {
  modelValue: number;
  mode: Modes;
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'RealSpace',
});

const {currentState, stackActions} = useStack<States>('input');
const {currentState: currentMode, stackActions: modeActions} = useStack<Modes>(props.mode ?? 'RealSpace');

const realSpaceMove = ref<RealSpaceMove>({} as RealSpaceMove);
const warpMoves = ref({});

const inputSector = ref<number>();
const error = ref<string>();

const destSector = ref<number>();

const {sector, stats} = useAuth();
const api = useApi();

const computeRsMove = async (sector: number) => {
  stackActions.add('loading');
  const response = await fetch(route('real-space.calculate', {sector}), {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
    },
  });

  const json = await response.json();

  if (response.ok) {
    realSpaceMove.value = json;
    destSector.value = sector;
    stackActions.add('loadedRealSpace');
  } else {
    error.value = json.message;
    // TODO: check this works as expected and last isn't set to 'loading'
    if (stackActions.last() === 'input') {
      stackActions.previous();
    } else {
      stackActions.add('error');
    }
  }
};

const computeWarpMove = async (sector: number) => {
  stackActions.add('loading');
  const response = await fetch(route('real-space.calculate', {sector}), {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
    },
  });

  const json = await response.json();

  if (response.ok) {
    realSpaceMove.value = json;
    stackActions.add('loadedWarpMoves');
  } else {
    error.value = json.message;
    stackActions.add('error');
  }
};

const compute = async () => {
  if (!inputSector.value) return;
  if (currentMode.value === 'Warps') return computeWarpMove(inputSector.value);
  return computeRsMove(inputSector.value);
}

const engage = async () => {
  // if loadedRealSpace post to real-space.move
  // if loadedWarpMoves post to nav-com.move
  if (currentState.value === 'loadedRealSpace') {
    const response = await api.post(route('real-space.move'), {
      sector: destSector.value,
    });

    const json = await response.json();

    if (response.ok) {
      // There are a few ok response types:
      // - player has moved into the new sector without issue: redirect to /dashboard to display navigation result
      // - player has come up against fighters demanding a payment, display payment form
      // - player has come up against fighters and battle has begun
      // - player has come up against mines and detonated them

      // All is fine redirect to dashboard for report
      router.visit(route('dashboard'), {data:{navigation: true}});

    } else if (response.status === 402) {
      // Fighters in sector are demanding a payment to continue

    } else {
      error.value = json.message;
      stackActions.add('error');
    }
  }
};

watch(props, async (v) => {
  if (v.modelValue >= 1) return await computeRsMove(v.modelValue);

  // Reset component state
  realSpaceMove.value = {} as RealSpaceMove;
  warpMoves.value = {};
  inputSector.value = undefined;
  error.value = undefined;
  stackActions.reset();
});
</script>

<template>
  <pop-up :show="modelValue !== -1" @close="$emit('update:modelValue', -1)">
    <div class="p-6 w-3/5 border border-ui-orange-500 bg-ui-grey-900/90 border-x-4">
      <header class="flex items-center">
          <h2 class="flex-grow text-lg font-medium text-ui-yellow">{{ currentMode === 'RealSpace' ?  __('rsmove.l_rs_title') : 'Navigation Computer' }}</h2>
          <div class="flex space-x-4">
            <text-button @click="$emit('update:modelValue', -1)" class="underline">Close [ESC]</text-button>
          </div>
        </header>
      <form v-if="currentState === 'input'" @submit.prevent="compute">
        <div class="mt-1 text-sm">
          <label for="sector">{{ __('rsmove.l_rs_insector', {sector: sector.id, max_sectors: stats.max_sectors}) }}</label>
          <text-input id="sector" v-model="inputSector" autofocus />
          <input-error :message="error"/>
        </div>
        <footer class="mt-5 text-ui-orange-500 font-medium">
          <text-button>[ {{ __('rsmove.l_rs_submit') }} ] </text-button>
        </footer>
      </form>
      <template v-else-if="currentState === 'loading'">
        <p class="mt-1 text-sm text-white">[...] NavCom loading</p>
      </template>
      <template v-else-if="currentState === 'loadedRealSpace'">
        <p class="mt-1 text-sm text-white">
          <template v-if="!realSpaceMove.is_same_sector">
            {{ __('rsmove.l_rs_movetime', {triptime: realSpaceMove.turns}) }}
            {{ __('rsmove.l_rs_energy', {energy: realSpaceMove.energy_scooped}) }}

            <span v-if="realSpaceMove.can_navigate">{{ __('rsmove.l_rs_engage', {turns: realSpaceMove.turns_available}) }}</span>
            <span v-else class="text-red-600">{{ __('rsmove.l_rs_noturns')}}</span>
          </template>
          <span v-else>This is the same sector</span>
        </p>
        <footer class="mt-5 text-ui-orange-500 font-medium">
          <text-button @click="engage" :disabled="!realSpaceMove.can_navigate">[ Engage Engines ] </text-button>
          <text-button @click="computeWarpMove(modelValue)">[ Nav Computer ] </text-button>
          <text-button @click="stackActions.add('input')">[ Other ]</text-button>
        </footer>
      </template>
      <template v-else-if="currentState === 'loadedWarpMoves'">
        <p class="mt-1 text-sm text-white">WARP MOVES</p>
        <footer class="mt-5 text-ui-orange-500 font-medium">
          <text-button @click="engage" :disabled="!realSpaceMove.can_navigate">[ Engage Engines ] </text-button>
          <text-button @click="stackActions.add('input')">[ Other ]</text-button>
        </footer>
      </template>
      <template v-else>
        <p class="mt-1 text-sm text-white">{{error}}</p>
      </template>
    </div>
  </pop-up>
</template>