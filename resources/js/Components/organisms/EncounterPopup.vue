<script setup lang="ts">
/**
 * EncounterPopup.vue from The Kabal Invasion.
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
import type {EncounterResource} from "@/types/resources/encounter";
import {computed} from "vue";
import {router} from "@inertiajs/vue3";

const props = defineProps<{
  modelValue: Array<EncounterResource>;
}>();

const emit = defineEmits(['update:modelValue']);

const current = computed(() => {
  const current = props.modelValue[0];
  return (current) ?
      {
        ...current,
        canExit: current.options.hasOwnProperty('complete'),
        exitAction: current.options['complete'],
      } : undefined;
});

const parseMessage = (message:string) => {
  const replace = {
    white: '<span class="text-white">',
    red: '<span class="text-red-600">',
    green: '<span class="text-green-600">',
  };

  [...message.matchAll('\\<(?<name>\\w+)(?<attributes>\\s+[^\\>]*|)\\>')].forEach((match) => {
    message = message.replace(match[0], replace[match.groups.name] ?? '<span>');
  });

  [...message.matchAll('\\<\\/(?<name>\\w+)>')].forEach((match) => {
    if (Object.keys(replace).includes(match.groups.name)) {
      message = message.replace(match[0], '</span>');
    }
  });

  return message;
};

const exit = () => {
  if (!current.value) return;

  if (current.value.canExit) {
    router.visit(current.value.exitAction.link, {
      method: 'get',
    });
  }
};
</script>

<template>
  <pop-up-with-header @close="exit" :title="current?.title ?? ''" :show="typeof current !== 'undefined'" :close-button-text="current?.exitAction?.text" :close-button="current?.canExit">
    <p v-if="current" v-for="message of current.messages" v-html="parseMessage(message)" />
  </pop-up-with-header>
</template>