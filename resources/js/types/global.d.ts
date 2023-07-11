import {PageProps as InertiaPageProps} from '@inertiajs/core';
import ziggyRoute, {Config as ZiggyConfig} from 'ziggy-js';
import {PageProps as AppPageProps} from './';
import {AxiosInstance} from 'axios';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    type Lang = (string: string, config?: any) => string;
    type TranslationDictionary = Record<string, Record<string, string> | string>;

    var route: typeof ziggyRoute;
    var Ziggy: ZiggyConfig;

    var Translations: TranslationDictionary;

    const __: Lang;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
        __: typeof Lang;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
