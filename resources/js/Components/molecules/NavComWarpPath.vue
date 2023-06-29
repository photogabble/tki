<script setup lang="ts">

import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import {WarpRouteResource} from "@/types/resources/link";

defineProps<{
  route?: WarpRouteResource,
}>();

</script>

<template>
  <section class="text-white">
    <nav v-if="route">
      <div class="mt-1 space-x-2 flex">
        <p>{{ __('navcomp.l_nav_pathfnd') }}:</p>
        <ol class="flex space-x-2">
          <li>
            <sector-nav-button :link="route.start"/>
          </li>
          <li v-for="link in route.waypoints">
            <sector-nav-button :link="link"/>
          </li>
        </ol>
      </div>
      <p>{{ __('navcomp.l_nav_answ', {turns: route.waypoints.length}) }}</p>
    </nav>
    <div v-else>
      <p class="mt-1 text-sm">{{ __('navcomp.l_nav_proper') }}</p>
    </div>
    <slot/>
  </section>
</template>