import {computed, ref} from "vue";

const openStates = ref<Array<'help' | 'profile' | 'ship-report'>>([]);

export function useModal(name?: 'help' | 'profile' | 'ship-report') {
    const openModal = () => {
        if (name) openStates.value.push(name);
    }
    const closeModal = () => {
        if (name) {
            openStates.value = openStates.value.filter((s) => s !== name);
        } else if (openStates.value.length > 0) {
            openStates.value.pop();
        }
    }
    const isOpen = computed(() => {
        return (name) ? openStates.value.includes(name) : openStates.value.length > 0;
    });

    return {isOpen, openModal, closeModal};
}