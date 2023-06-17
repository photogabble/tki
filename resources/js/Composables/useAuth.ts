import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";

export function useAuth() {
    const pageProps = usePage();
    return {
        isLoggedIn: computed<Boolean>(() => pageProps.props.auth.user !== null),
        logout: () => console.log('todo: implement'),
        user: pageProps.props.auth.user,
    }
}