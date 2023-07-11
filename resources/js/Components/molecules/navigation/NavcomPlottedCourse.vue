<script setup lang="ts">

import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import {WarpRouteResource} from "@/types/resources/link";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import {useAuth} from "@/Composables/useAuth";

const emit = defineEmits(['action']);
const {config} = useAuth();

defineProps<{
  plottedCourse?: WarpRouteResource,
}>();

const engage = () => {

};

</script>

<template>
  <section class="text-white mt-1">
    <p v-if="!config.allow_navcomp" class="text-sm text-red-600">
      {{ __('navcomp.l_nav_nocomp') }}
    </p>
    <nav v-else-if="plottedCourse">
      <div class="space-x-2 flex">
        <p>{{ __('navcomp.l_nav_pathfnd') }}:</p>
        <ol class="flex space-x-2">
          <li>
            <sector-nav-button :link="plottedCourse.route.start"/>
          </li>
          <li v-for="link in plottedCourse.route.waypoints">
            <sector-nav-button :link="link"/>
          </li>
        </ol>
      </div>
      <p>{{ __('navcomp.l_nav_answ', {turns: plottedCourse.route.waypoints.length}) }}</p>
    </nav>
    <p v-else class="text-sm">{{ __('navcomp.l_nav_proper') }}</p>
    <footer class="mt-5 text-ui-orange-500 font-medium">
      <text-button @click="engage" :disabled="typeof plottedCourse === 'undefined'">
        [ {{ __('navcomp.l_nav_engage')}} ]
      </text-button>
      <text-button @click="emit('action', 'input')">
        [ Other ]
      </text-button>
    </footer>
  </section>
</template>