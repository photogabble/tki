<template>
  <div class="flex flex-row">
    <nav-link
        v-for="(state,idx) in states"
        :key="`toggle-${idx}`"
        width="full"
        height="6"
        class="text-xs"
        @click="$emit('update:modelValue', state)"
        :active="modelValue === state"
    >{{state}}</nav-link>
  </div>
</template>
<script setup lang="ts">
import NavLink from "@/Components/atoms/navigation/NavLink.vue";

defineProps({
  states: {
    type: Array,
    default() { return []; }
  },
  modelValue: String,
});

defineEmits(['update:modelValue']);
</script>