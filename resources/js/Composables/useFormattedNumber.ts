import {useAuth} from "@/Composables/useAuth";

export function useFormattedNumber() {
    const {isLoggedIn, user} = useAuth();
    const formatter = new Intl.NumberFormat(isLoggedIn.value ? user.lang.replace('_', '-') : 'en-GB');
    return (n: number) => formatter.format(n);
}