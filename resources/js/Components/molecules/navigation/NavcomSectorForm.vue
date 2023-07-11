<script setup lang="ts">
import {useNavigationComputer} from "@/Composables/useNavigationComputer";
import InputError from "@/Components/atoms/form/InputError.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import {MovementMode} from "@/types/resources/movement";
import {useAuth} from "@/Composables/useAuth";
import {ref} from "vue";

const props = defineProps<{
  mode: MovementMode,
  modelValue: number;
}>();

const emit = defineEmits(['course']);

const {error, loading, compute} = useNavigationComputer();
const inputSector = ref<number>(props.modelValue);
const {sector, config} = useAuth();

const computeCourse = async () => {
  const result = await compute(inputSector.value, props.mode);
  if (result) emit('course', result);
};

</script>

<template>
  <form @submit.prevent="computeCourse">
    <div class="mt-1 text-sm">
      <label for="sector">{{
          mode === 'RealSpace'
              ? __('rsmove.l_rs_insector', {sector: sector.id, max_sectors: config.max_sectors})
              : __('navcomp.l_nav_query')
        }}</label>
      <text-input id="sector" v-model="inputSector" autofocus />
      <input-error :message="error"/>
    </div>
    <footer class="mt-5 text-ui-orange-500 font-medium">
      <text-button :disabled="loading">[ {{ __('rsmove.l_rs_submit') }} ] </text-button>
    </footer>
  </form>
</template>