<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import UpDownIndicator from "@/Components/atoms/UpDownIndicator.vue";
withDefaults(defineProps<{
  href: string
  direction: 'ASC' | 'DESC',
  isSorting: boolean,
}>(), {
  href: '#',
  direction: 'DESC',
  isSorting: false,
});
</script>

<template>
  <Link :href="href" :class="`${isSorting ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">
    <slot/> <up-down-indicator v-if="isSorting" :direction="direction" />
  </Link>
</template>