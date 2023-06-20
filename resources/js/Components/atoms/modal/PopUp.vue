<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue';
import {SafeTeleport} from "vue-safe-teleport";

const props = withDefaults(
    defineProps<{
      show?: boolean;
      closeable?: boolean;
      danger?: boolean;
    }>(), {
      show: false,
      closeable: true,
      danger: false
    }
);

const emit = defineEmits(['close']);

const close = () => {
  if (props.closeable) {
    emit('close');
  }
};

const closeOnEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.show) {
    close();
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.body.style.overflow = 'visible';
});

</script>

<template>
  <safe-teleport to="#modal-target">
    <div v-if="show" :class="['absolute top-0 left-0 w-full h-full z-50 flex justify-center items-center', (danger) ? 'bg-[#260202d6]' : 'bg-ui-grey-900/80']">
      <slot/>
    </div>
  </safe-teleport>
</template>