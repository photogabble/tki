<script setup lang="ts">

withDefaults(defineProps<{
  direction: 'ASC'|'DESC'
}>(), {
  direction: 'DESC',
});

</script>

<template>
  <span v-if="direction === 'ASC'">▲</span>
  <span v-if="direction === 'DESC'">▼</span>
</template>