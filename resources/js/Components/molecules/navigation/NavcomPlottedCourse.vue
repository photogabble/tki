<script setup lang="ts">
import {useNavigationComputer} from "@/Composables/useNavigationComputer";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import {WarpRouteResource} from "@/types/resources/link";
import {useAuth} from "@/Composables/useAuth";
import { useIntervalFn } from '@vueuse/core';
import {router} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";

const emit = defineEmits(['action']);
const {loading, warpTo} = useNavigationComputer();
const {config} = useAuth();

const countDown = ref(5);
const { pause, resume, isActive } = useIntervalFn(() => {
  if (countDown.value > 0) countDown.value--;
  if (countDown.value === 0) {
    pause();
    engage();
  }
}, 1000, {immediate: false});

const props = withDefaults(defineProps<{
  plottedCourse?: WarpRouteResource,
  inFlight:boolean,
}>(), {
  inFlight: false,
});

const engage = async () => {
  // Make warp travel, then redirect to main page with plotted course id set as waypoints url param
  if (!props?.plottedCourse?.next) return;
  const movement = await warpTo(props.plottedCourse.next);
  if (movement) router.visit(route('dashboard'), {
    data: {
      waypoints: props.plottedCourse.id
    }
  });
};

onMounted(() => {
  if (props.plottedCourse && props.plottedCourse.inProgress) resume();
});

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
      <text-button @click="engage" :disabled="typeof plottedCourse === 'undefined' || loading">
        [ {{ __('navcomp.l_nav_engage')}} <span v-if="isActive">({{ countDown }} seconds)</span> ]
      </text-button>
      <text-button v-if="!inFlight" @click="emit('action', 'input')" :disabled="loading">
        [ Other ]
      </text-button>
    </footer>
  </section>
</template>