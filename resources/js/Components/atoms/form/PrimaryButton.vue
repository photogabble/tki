<script setup>
import {computed} from "vue";

const props = defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    active: Boolean,
    disabled: Boolean,
});

const outerClass = computed(() => {
    return [
        `block h-12 border p-0.5 disabled:opacity-50`,
        {
            'border-ui-salmon': props.active && !props.disabled,
            'border-ui-orange-500': !props.active && !props.disabled,
            'border-ui-orange-500/50 cursor-default pointer-events-none': props.disabled,
        }
    ];
});

const innerClass = computed(() => {
    return [
        'block flex h-full justify-center items-center ease-in-out duration-300 px-2',
        {
            'bg-ui-salmon text-ui-grey-900 hover:bg-ui-salmon/10 hover:text-ui-salmon': props.active && !props.disabled,
            'bg-ui-orange-500/10 hover:bg-ui-orange-500 hover:text-ui-grey-900': !props.active && !props.disabled,
            'bg-ui-orange-500/10 text-ui-orange-500/50': props.disabled
        }
    ];
});

</script>

<template>
    <button :is="type" :class="outerClass" :disabled="disabled">
        <span :class="innerClass">
            <slot/>
        </span>
    </button>
</template>
