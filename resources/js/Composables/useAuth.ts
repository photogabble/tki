import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";
import type {User} from "@/types";

// TODO: Rename to usePlayerState
export function useAuth() {
    const pageProps = usePage();
    const isLoggedIn = computed<Boolean>(() => pageProps.props.auth.user !== null);
    const user: User = pageProps.props.auth.user;

    return {
        isLoggedIn,
        logout: () => console.log('todo: implement'),
        user,
        ship: isLoggedIn.value ? user.ship : null,
        sector: isLoggedIn.value ? user?.ship?.sector : null,
        presets: isLoggedIn.value ? user.presets : null,
        stats: pageProps.props.stats,
    }
}