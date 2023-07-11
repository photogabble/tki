<script setup lang="ts">
import TextButton from "@/Components/atoms/form/TextButton.vue";
import type {RealSpaceMove} from "@/types/galaxy-overview";
import {useAuth} from "@/Composables/useAuth";
import {useApi} from "@/Composables/useApi";
import {router} from "@inertiajs/vue3";
import {ref} from "vue";

const emit = defineEmits(['action', 'fatal-error']);

const props = defineProps<{
  course: RealSpaceMove;
}>();

const loading = ref<boolean>(false);
const {config} = useAuth();
const api = useApi();

const engage = async () => {
  const response = await api.post(route('real-space.move'), {
    sector: props.course.sector.id,
  });

  if (!response.ok) {
    const json = await response.json();
    emit('fatal-error', json.message);
    return;
  }

  router.visit(route('dashboard'));
}
</script>

<template>
  <section>
    <p v-if="course.is_same_sector" class="mt-1 text-sm text-white">
      This is the same sector
    </p>
    <p v-else class="mt-1 text-sm text-white">
      {{ __('rsmove.l_rs_movetime', {triptime: course.turns}) }}
      {{ __('rsmove.l_rs_energy', {energy: course.energy_scooped}) }}

      <span v-if="course.can_navigate">{{ __('rsmove.l_rs_engage', {turns: course.turns_available}) }}</span>
      <span v-else class="text-red-600">{{ __('rsmove.l_rs_noturns')}}</span>
    </p>
    <footer class="mt-5 text-ui-orange-500 font-medium">
      <text-button @click="engage" :disabled="!course.can_navigate">[ {{ __('rsmove.l_rs_engage_link') }} ] </text-button>
      <text-button @click="emit('action', 'navcom')" :disabled="!config.allow_navcomp">[ Nav Computer ] </text-button>
      <text-button @click="emit('action', 'input')">[ Other ]</text-button>
    </footer>
  </section>
</template>