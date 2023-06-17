<template>
  <div :class="`overflow-hidden  ${centered ? 'flex flex-row items-center justify-center' : ''} h-20 flex-grow border mt-1 border-ui-orange-500/50`">
    <slot/>
  </div>
</template>

<script setup lang="ts">
defineProps({
  centered: Boolean
});
</script>