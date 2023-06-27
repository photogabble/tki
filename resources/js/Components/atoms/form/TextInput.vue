<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps<{
  modelValue: string|number;
}>();

defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
  if (input.value?.hasAttribute('autofocus')) {
    input.value?.focus();
  }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        class="px-1.5 py-1 bg-ui-grey-900 border w-full border-ui-orange-500/50 focus:border-ui-orange-500 focus:outline-none focus:ring focus:ring-ui-yellow focus:ring-sm focus:ring-opacity-50 shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
