import {PageProps as InertiaPageProps} from "@inertiajs/core";

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    // TODO
}

export interface DashboardPageProps extends InertiaPageProps, PageProps {
}