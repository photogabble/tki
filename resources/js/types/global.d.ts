import {PageProps as InertiaPageProps} from '@inertiajs/core';
import {AxiosInstance} from 'axios';
import ziggyRoute, {Config as ZiggyConfig} from 'ziggy-js';
import {PageProps as AppPageProps} from './';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    type TranslationDictionary = Record<string, Record<string, string> | string>;

    var route: typeof ziggyRoute;
    var Ziggy: ZiggyConfig;

    var Translations: TranslationDictionary;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
