import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import AppButton from '@/Components/AppButton.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        
        // Global Fail-Safe Error Handling
        vueApp.config.errorHandler = (err, instance, info) => {
            console.error('Global Vue Error Captured:', err);
            console.error('Error Info:', info);
            // Optionally send to logging service here
        };

        // Global Permission Helpers
        vueApp.config.globalProperties.$can = (permission) => {
            const permissions = props.initialPage.props.auth?.permissions || [];
            if (Array.isArray(permission)) {
                return permission.some(p => permissions.includes(p));
            }
            return permissions.includes(permission);
        };

        vueApp.config.globalProperties.$hasRole = (role) => {
            const roles = props.initialPage.props.auth?.roles || [];
            if (Array.isArray(role)) {
                return role.some(r => roles.includes(r));
            }
            return roles.includes(role);
        };

        return vueApp
            .use(plugin)
            .use(ZiggyVue)
            .component('AppButton', AppButton)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
