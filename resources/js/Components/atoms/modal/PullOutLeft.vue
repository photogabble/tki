<script setup lang="ts">
const emit = defineEmits(['visible']);

defineProps({
  isVisible: Boolean,
});

const onAfterEnter = () => emit('visible', true);
const onAfterLeave = () => emit('visible', false);
</script>

<template>
  <Transition @after-enter="onAfterEnter" @after-leave="onAfterLeave">
    <div v-if="isVisible" class="overflow-y-auto absolute w-[360px] left-0 top-0 border-r-4 p-[10px] border-double border-r-ui-orange-500 bg-ui-grey-900 z-50 h-full">
      <slot/>
    </div>
  </Transition>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
  left: 0;
  transition: left 250ms ease;
}

.v-enter-from,
.v-leave-to {
  left: -360px;
}
</style>