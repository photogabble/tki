<script setup lang="ts">
import {LinkResource} from "@/types/resources/link";

const props = defineProps<{
  link: LinkResource;
}>();
</script>

<template>
  <button :class="['flex-grow text-left hover:underline', {
            'text-white': (link.hasVisited && !link.hasDanger),
            'text-red-600': (link.hasVisited && link.hasDanger),
            'text-ui-orange-500': !link.hasVisited
          }]">=> {{ link.to_sector_id }}</button>
</template>