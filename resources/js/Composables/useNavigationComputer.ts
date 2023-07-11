import {useApi} from "@/Composables/useApi";
import {ref} from "vue";
import {WarpRouteResource} from "@/types/resources/link";
import {RealSpaceMove} from "@/types/galaxy-overview";
import {MovementMode} from "@/types/resources/movement";

export function useNavigationComputer() {
    const loading = ref<boolean>(false);
    const error = ref<string>();
    const api = useApi();

    const compute = async (sector:number, mode: MovementMode): Promise<WarpRouteResource|RealSpaceMove|false> => {
        loading.value = true;

        const response = await api.get((mode === 'RealSpace')
            ? route('real-space.calculate', {sector})
            : route('warp.calculate', {sector})
        );

        if (response.status === 204) {
            error.value = 'Your computer technology is too primitive to compute a warp route to that distance.';
            loading.value = false;
            return false;
        }

        const json = await response.json();
        loading.value = false;

        if (response.ok) {
            return json;
        } else {
            error.value = json.message;
        }

        return false;
    };

    return {
        loading,
        error,
        compute,
    }
}