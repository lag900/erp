<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: Number,
});

const title = {
    403: '403: Forbidden',
    404: '404: Page Not Found',
    405: '405: Method Not Allowed',
    500: '500: Server Error',
    503: '503: Service Unavailable',
}[props.status];

const description = {
    403: 'Strict security protocol: You are not authorized to access this resource or perform this action. Please verify your permissions.',
    404: 'The requested resource could not be located in the current workspace. It may have been archived or moved.',
    405: 'Invalid operation attempt. The action performed is not supported on this resource endpoint.',
    500: 'Internal system malfunction. Our engineering team has been notified. Please try again shortly.',
    503: 'System is currently undergoing scheduled maintenance. We will be back online shortly.',
}[props.status];
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 text-gray-700 p-4">
        <div class="flex max-w-xl flex-col items-center text-center space-y-6">
            
            <div class="h-24 w-24 rounded-3xl bg-white shadow-premium flex items-center justify-center border border-gray-100">
                <span class="text-4xl font-black text-primary">{{ status }}</span>
            </div>

            <div class="space-y-3">
                <h1 class="text-3xl font-black uppercase tracking-tight text-gray-900">{{ title }}</h1>
                <p class="text-sm font-medium text-gray-500 leading-relaxed max-w-md mx-auto">{{ description }}</p>
                
                <div v-if="status === 405" class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100/50">
                    <p class="text-[11px] font-bold text-amber-700 uppercase tracking-widest flex items-center justify-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Technical Context
                    </p>
                    <p class="mt-2 text-[10px] text-amber-600 font-mono">
                        MethodNotAllowedHttpException: Route handler mismatch. Usually caused by incorrect form submission method or stale cache.
                    </p>
                </div>
            </div>

            <Link href="/" class="app-button-primary mt-6 !rounded-xl !px-8 !py-3 shadow-soft uppercase tracking-widest text-xs font-black">
                Return to Dashboard
            </Link>
        </div>
    </div>
</template>
