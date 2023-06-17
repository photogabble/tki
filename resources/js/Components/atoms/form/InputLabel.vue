<script setup lang="ts">
defineProps<{
  value?: string;
}>();
</script>

<template>
  <label class="block font-medium text-sm text-ui-orange-500">
    <span v-if="value">{{ value }}</span>
    <span v-else><slot /></span>
  </label>
</template>
