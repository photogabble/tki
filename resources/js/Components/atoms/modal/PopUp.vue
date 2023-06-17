<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = withDefaults(
    defineProps<{
      show?: boolean;
      closeable?: boolean;
    }>(), {
      show: false,
      closeable: true,
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

  <teleport to="main">
    <div v-if="show" class="absolute top-0 left-0 w-full h-full bg-[#260202d6] z-50 flex justify-center items-center">
      <slot/>
    </div>
  </teleport>

</template>