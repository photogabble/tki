<script setup lang="ts">

import type {SectorType} from "@/types/resources/sector";
import {computed} from "vue";

const props = withDefaults(
    defineProps<{
      type: SectorType,
    }>(),
    {
      type: 'unknown',
    }
);

const imageSrc = computed(() => {
  const filename = (['none', 'unknown'].includes(props.type))
      ? props.type
      : `port-${props.type}`;

  return new URL(`../../../images/map-tiles/${filename}.png`, import.meta.url)
});
</script>

<template>
  <img :src="imageSrc"/>
</template>