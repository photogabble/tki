import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";

// TODO: Rename to usePlayerState
export function useAuth() {
    const pageProps = usePage();
    const isLoggedIn = computed<Boolean>(() => pageProps.props.auth.user !== null);
    const user = pageProps.props.auth.user;

    return {
        isLoggedIn,
        logout: () => console.log('todo: implement'),
        user,
        ship: isLoggedIn.value ? user.ship : null,
        sector: isLoggedIn.value ? user.ship.sector : null,
        presets: isLoggedIn.value ? user.presets : null,
    }
}