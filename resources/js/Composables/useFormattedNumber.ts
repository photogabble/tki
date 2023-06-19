import {useAuth} from "@/Composables/useAuth";

export function useFormattedNumber() {
    const {isLoggedIn, user} = useAuth();
    const formatter = new Intl.NumberFormat(isLoggedIn ? user.lang : 'en-GB');
    return (n: number) => formatter.format(n);
}