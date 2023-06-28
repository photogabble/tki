import {PageProps as InertiaPageProps} from "@inertiajs/core";
import {WarpRouteResource} from "@/types/resources/link";

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    navigation: boolean;
    route: null|WarpRouteResource;
}

export interface DashboardPageProps extends InertiaPageProps, PageProps {}