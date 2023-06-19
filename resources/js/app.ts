import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Localisation } from '@/Plugins/localisation';
import VueSafeTeleport from 'vue-safe-teleport';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'TKI';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(Localisation, Translations)
            .use(VueSafeTeleport)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
