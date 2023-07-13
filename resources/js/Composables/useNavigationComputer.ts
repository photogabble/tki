import type {ErrorResource} from "@/types/resources/error";
import {WarpRouteResource} from "@/types/resources/link";
import {MovementMode} from "@/types/resources/movement";
import {RealSpaceMove} from "@/types/galaxy-overview";
import {useApi} from "@/Composables/useApi";
import {ref} from "vue";

export function useNavigationComputer() {
    const loading = ref<boolean>(false);
    const error = ref<ErrorResource>();
    const api = useApi();

    const compute = async (sector: number, mode: MovementMode): Promise<WarpRouteResource | RealSpaceMove | false> => {
        loading.value = true;

        const response = await api.get((mode === 'RealSpace')
            ? route('real-space.calculate', {sector})
            : route('warp.calculate', {sector})
        );

        if (response.status === 204) {
            error.value = {
                message: 'Your computer technology is too primitive to compute a warp route to that distance.',
                status: response.status,
            };

            loading.value = false;
            return false;
        }

        const json = await response.json();
        loading.value = false;

        if (response.ok) {
            return json;
        } else {
            error.value = {
                message: json.message,
                status: response.status,
            }
        }

        return false;
    };

    const warpTo = async (sector: number): Promise<WarpRouteResource|false> => {
        loading.value = true;
        const response = await api.post(route('warp.move'), {sector});
        const json = await response.json();
        loading.value = false;

        if (response.ok) {
            return json as WarpRouteResource;
        } else {
            // Warping can have the following error responses that need handling by a user agent:
            // 412: Invalid sector or not enough turns
            // 300: Player has existing encounter blocking path
            // 404: Warp link does not exist
            error.value = {
                message: json.message,
                status: response.status,
            }
        }

        return false;
    }

    return {
        loading,
        error,
        compute,
        warpTo,
    }
}