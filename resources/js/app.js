import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ProductShow from './Storefront/ProductShow.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const inertiaRoot = document.getElementById('app');

if (inertiaRoot?.dataset.page) {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            ),
        setup({ el, App, props, plugin }) {
            return createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
}

const mountJsonApp = (elementId, propsId, component) => {
    const element = document.getElementById(elementId);
    const propsElement = document.getElementById(propsId);

    if (!element || !propsElement) {
        return;
    }

    createApp(component, JSON.parse(propsElement.textContent || '{}')).mount(element);
};

mountJsonApp('product-show-app', 'product-show-props', ProductShow);

