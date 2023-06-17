<template>
  <Transition @after-enter="onAfterEnter" @after-leave="onAfterLeave">
    <div v-if="isVisible" class="absolute w-[350px] right-0 top-0 border-l-4 p-2 border-double border-l-ui-orange-500 bg-ui-grey-900 z-50 h-full">
      <slot/>
    </div>
  </Transition>
</template>

<script setup lang="ts">
const emit = defineEmits(['visible']);

defineProps({
  isVisible: Boolean,
});

const onAfterEnter = () => emit('visible', true);
const onAfterLeave = () => emit('visible', false);
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  right: 0;
  transition: right 250ms ease;
}

.v-enter-from,
.v-leave-to {
  right: -350px;
}
</style>