<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    log: Object
});

const getSeverityClass = (sev) => {
    switch (sev) {
        case 'critical': return 'bg-rose-100 text-rose-700 border-rose-200';
        case 'warning': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};
</script>

<template>
    <Head title="Audit Log Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('audit.index')" class="flex items-center justify-center h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-800 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Log Event #{{ log.id }}</h2>
                </div>
                <div :class="['px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest border', getSeverityClass(log.severity)]">
                    {{ log.severity }} Severity
                </div>
            </div>
        </template>

        <div class="py-12 max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Action Summary -->
                <div class="md:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-[32px] border border-slate-200/60 shadow-soft">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Event Context</h3>
                        
                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">Module</label>
                                <div class="text-lg font-bold text-slate-800">{{ log.module }}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">Action</label>
                                <div class="text-lg font-bold text-[#3d4adb] uppercase tracking-tight">{{ log.action_type.replace('_', ' ') }}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">Timestamp</label>
                                <div class="text-sm font-bold text-slate-700">{{ formatDate(log.created_at) }}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">Status</label>
                                <div :class="['inline-flex px-2 py-0.5 rounded-lg text-[11px] font-black uppercase tracking-widest', log.status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700']">
                                    {{ log.status }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-slate-50">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] block mb-4">Request URL</label>
                            <div class="breakable-url bg-slate-50 p-4 rounded-2xl text-xs font-mono text-slate-600 border border-slate-100">
                                {{ log.url }}
                            </div>
                        </div>
                    </div>

                    <!-- Data Changes -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-200/60 shadow-soft overflow-hidden">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Internal Data state</h3>
                        
                        <div v-if="log.old_values || log.new_values" class="space-y-6">
                            <div v-if="log.old_values && Object.keys(log.old_values).length" class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Previous records (OLD)</label>
                                <pre class="p-6 rounded-2xl bg-slate-50 border border-slate-100 text-[12px] text-slate-600 font-mono">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                            </div>

                            <div v-if="log.new_values && Object.keys(log.new_values).length" class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Update (NEW)</label>
                                <pre class="p-6 rounded-2xl bg-[#3d4adb]/5 border border-[#3d4adb]/10 text-[12px] text-[#3d4adb] font-mono">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                            </div>
                        </div>
                        <div v-else class="py-12 text-center">
                            <div class="text-slate-300 text-sm font-medium">No record-level changes detected for this event.</div>
                        </div>
                    </div>
                </div>

                <!-- Side Info -->
                <div class="space-y-8">
                    <!-- Actor Info -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-200/60 shadow-soft">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">System Actor</h3>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-16 w-16 rounded-2xl bg-[#3d4adb] flex items-center justify-center text-white text-xl font-black shadow-premium">
                                {{ log.user_name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div>
                                <div class="text-lg font-black text-slate-800 leading-tight">{{ log.user_name }}</div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-[#3d4adb] mt-1">{{ log.role }}</div>
                            </div>
                        </div>
                        <div class="space-y-4 pt-6 border-t border-slate-50">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-400 uppercase tracking-widest">IP Address</span>
                                <span class="text-slate-800 tabular-nums font-bold">{{ log.ip_address }}</span>
                            </div>
                            <div class="space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">User Agent</span>
                                <div class="log-metadata-block">
                                    {{ log.user_agent }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Info -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-200/60 shadow-soft">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Affected Resource</h3>
                        <div v-if="log.auditable_type" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Type</span>
                                <span class="text-sm font-bold text-slate-800">{{ log.auditable_type.split('\\').pop() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Record ID</span>
                                <span class="text-sm font-bold text-slate-800">#{{ log.auditable_id }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <span class="text-xs font-bold text-slate-400 uppercase">Non-Resource Action</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
