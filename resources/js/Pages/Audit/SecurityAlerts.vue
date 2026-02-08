<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    alerts: Object
});

const getSeverityClass = (sev) => {
    return sev === 'critical' ? 'bg-rose-50 border-rose-100' : 'bg-amber-50 border-amber-100';
};

const getBadgeClass = (sev) => {
    return sev === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Security Alerts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Security & Governance Alerts</h2>
                <Link :href="route('audit.index')" class="text-xs font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-all">
                    View Full Audit Trail →
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div v-if="alerts.data.length > 0" class="space-y-4">
                <div v-for="alert in alerts.data" :key="alert.id" 
                    :class="['p-6 rounded-[24px] border transition-all hover:scale-[1.01] shadow-soft flex items-center gap-6', getSeverityClass(alert.severity)]"
                >
                    <div :class="['h-12 w-12 rounded-xl flex items-center justify-center shrink-0 shadow-sm', alert.severity === 'critical' ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white']">
                        <svg v-if="alert.severity === 'critical'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>

                    <div class="flex-1 min-w-0 content-protect">
                        <div class="flex items-center gap-3 mb-1">
                            <span :class="['px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest whitespace-nowrap', getBadgeClass(alert.severity)]">
                                {{ alert.severity }}
                            </span>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ formatDate(alert.created_at) }}</span>
                        </div>
                        <h3 class="text-[15px] font-bold text-slate-800 leading-tight break-words" :title="alert.action_type">
                            {{ alert.action_type.replace('_', ' ').toUpperCase() }} in {{ alert.module }}
                        </h3>
                        <p class="text-[13px] text-slate-600 font-medium mt-1" :title="`User: ${alert.user_name} | Role: ${alert.role} | IP: ${alert.ip_address}`">
                            Triggered by <span class="text-slate-900 font-bold break-all">{{ alert.user_name }}</span> ({{ alert.role }}) from IP {{ alert.ip_address }}
                        </p>
                    </div>

                    <div class="shrink-0 flex items-center gap-4">
                        <Link :href="route('audit.show', alert.id)" class="px-6 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                            Investigate
                        </Link>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 font-medium">
                    <Pagination :links="alerts.links" />
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-[32px] border border-dashed border-slate-200 shadow-soft">
                <div class="h-20 w-20 bg-emerald-50 text-emerald-500 rounded-[28px] border border-emerald-100 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">System Integrity Normal</h3>
                <p class="text-[14px] text-slate-400 mt-2 font-medium">No critical security violations or anomalies detected at this time.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
