<script setup lang="ts">
import PopUp from "@/Components/atoms/modal/PopUp.vue";

withDefaults(defineProps<{
  title: string,
  show: boolean,
  closeButton: boolean,
  closeButtonText: string,
}>(), {
  closeButton: false,
  closeButtonText: 'Close',
})
defineEmits(['close']);
</script>

<template>
  <pop-up :show="show" @close="$emit('close')">
    <div class="p-6 w-3/5 border border-ui-orange-500 bg-ui-grey-900/90 border-x-4">
      <header class="flex items-center mb-2">
        <h2 class="flex-grow text-lg font-medium text-ui-yellow">{{ title }}</h2>
        <div class="flex space-x-4">
          <slot name="header" />
          <button v-if="closeButton" @click="$emit('close')" class="underline">{{closeButtonText}} [ESC]</button>
        </div>
      </header>
      <slot/>
    </div>
  </pop-up>
</template>