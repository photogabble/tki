import {Ref, ref, UnwrapRef} from "vue";

export function useStack<T>(initialState: T) {
    let currentState: Ref<UnwrapRef<T>> = ref<T>(initialState);
    let history: Array<T> = [initialState];

    return {
        currentState,
        stackActions: {
            history: () => history,
            add: (state: T) => {
                history.push(currentState.value as T);
                currentState.value = state as UnwrapRef<T>;
            },
            replace: (state: T) => {
                history.pop();
                history.push(currentState.value as T);
                currentState.value = state as UnwrapRef<T>;
            },
            last: (): T => history[history.length - 1],
            previous: () => {
                const previous = history.pop()
                if (previous) currentState.value = previous as UnwrapRef<T>;
            },
            reset: () => {
                history = [initialState];
                currentState.value = initialState as UnwrapRef<T>;
            }
        }
    }

}