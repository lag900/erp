import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import AppButton from '@/Components/AppButton.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// --- Production-Level Global Alerts ---
window.showConfirm = ({ title, message, confirmText, cancelText, onConfirm }) => {
    Swal.fire({
        title: title || 'Are you sure?',
        text: message || "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3d4adb',
        cancelButtonColor: '#f8fafc',
        confirmButtonText: confirmText || 'Yes, delete it!',
        cancelButtonText: cancelText || 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'rounded-xl px-6 py-3 font-bold text-white shadow-premium transition-all hover:scale-105 ml-3',
            cancelButton: 'rounded-xl px-6 py-3 font-bold text-slate-500 border border-slate-200 transition-all hover:bg-slate-50',
            popup: 'rounded-[32px] border-none shadow-premium p-8',
            title: 'text-2xl font-black text-slate-800 tracking-tight',
            htmlContainer: 'text-slate-500 font-medium'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && onConfirm) {
            onConfirm();
        }
    });
};

window.showToast = (icon, title) => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: icon, // success, error, warning, info
        title: title,
        customClass: {
            popup: 'rounded-2xl shadow-premium border-none p-4',
            title: 'text-sm font-bold text-slate-800'
        }
    });
};

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
        color: '#3d4adb',
    },
});
