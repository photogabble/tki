<script setup lang="ts">
/**
 * PresetEditorPopup.vue from The Kabal Invasion.
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
 * This component is the result of refactoring the legacy preset.php. It deals
 * with updating player preset RealSpace travel destinations.
 *
 */

import TextButton from "@/Components/atoms/form/TextButton.vue";
import InputError from "@/Components/atoms/form/InputError.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import type {PresetResource} from "@/types/resources/preset";
import PopUp from "@/Components/atoms/modal/PopUp.vue";
import {useForm} from "@inertiajs/vue3";
import {watch} from "vue";

const emit = defineEmits(['update:modelValue']);

interface Props {
  modelValue: PresetResource|undefined;
}

const props = defineProps<Props>();

const form = useForm({
  sector: props.modelValue?.link.to_sector_id ?? 1
});

watch(props, () => {
  form.sector = props.modelValue?.link.to_sector_id ?? 1
});

const store = () => form.submit(
    'patch',
    route('real-space.preset.store', {preset: props.modelValue.id}),
    {
      onSuccess: () => emit('update:modelValue', undefined),
      preserveState: (page) => Object.keys(page.props.errors).length > 0, // This keeps the data reactive
    }
);

</script>

<template>
  <pop-up :show="modelValue !== undefined" @close="$emit('update:modelValue', undefined)">
    <div class="p-6 w-3/5 border border-ui-orange-500 bg-ui-grey-900/90 border-x-4">
      <header class="flex items-center">
          <h2 class="flex-grow text-lg font-medium text-ui-yellow">{{ __('presets.l_pre_title') }}</h2>
          <div class="flex space-x-4">
            <text-button @click="$emit('update:modelValue', undefined)" class="underline">Close [ESC]</text-button>
          </div>
        </header>
      <form @submit.prevent="store">
        <div class="mt-1 text-sm">
          <label for="sector">Sector</label>
          <text-input id="sector" v-model="form.sector" autofocus />
          <input-error :message="form.errors.sector"/>
        </div>
        <footer class="mt-5 text-ui-orange-500 font-medium">
          <text-button :disabled="form.processing">[ {{ __('presets.l_pre_save') }} ] </text-button>
        </footer>
      </form>
    </div>
  </pop-up>
</template>