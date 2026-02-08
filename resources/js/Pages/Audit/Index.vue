<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    logs: Object,
    filters: Object,
    users: Array,
    stats: Object,
    modules: Array,
    actionTypes: Array,
});

const search = ref(props.filters.search);
const userId = ref(props.filters.user_id || '');
const moduleName = ref(props.filters.module || '');
const actionType = ref(props.filters.action_type || '');
const severity = ref(props.filters.severity || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = debounce(() => {
    router.get(route('audit.index'), {
        search: search.value,
        user_id: userId.value,
        module: moduleName.value,
        action_type: actionType.value,
        severity: severity.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([search, userId, moduleName, actionType, severity, dateFrom, dateTo], () => {
    applyFilters();
});

const getSeverityClass = (sev) => {
    switch (sev) {
        case 'critical': return 'bg-rose-100 text-rose-700 border-rose-200';
        case 'warning': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getStatusClass = (status) => {
    return status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const clearFilters = () => {
    search.value = '';
    userId.value = '';
    moduleName.value = '';
    actionType.value = '';
    severity.value = '';
    dateFrom.value = '';
    dateTo.value = '';
};

const cleanupLogs = () => {
    if (confirm('Are you sure you want to cleanup logs older than 6 months?')) {
        router.post(route('audit.cleanup'), { months: 6 });
    }
};
</script>

<template>
    <Head title="System Audit Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Enterprise Audit Center</h2>
                <div class="flex items-center gap-3">
                    <a :href="route('audit.export')" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Export CSV
                    </a>
                    <button @click="cleanupLogs" class="inline-flex items-center px-4 py-2 bg-rose-50 border border-rose-100 rounded-xl text-xs font-bold text-rose-600 uppercase tracking-widest shadow-sm hover:bg-rose-100 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Cleanup Old Logs
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 space-y-8">
            <!-- Stats Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-soft">
                    <div class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Logs Today</div>
                    <div class="text-3xl font-black text-slate-800">{{ stats.total_today }}</div>
                </div>
                <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-soft">
                    <div class="text-amber-500 text-[10px] font-bold uppercase tracking-widest mb-1">Warnings Today</div>
                    <div class="text-3xl font-black text-amber-600">{{ stats.warnings_today }}</div>
                </div>
                <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-soft">
                    <div class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mb-1">Critical Alerts</div>
                    <div class="text-3xl font-black text-rose-600">{{ stats.critical_today }}</div>
                </div>
                <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-soft overflow-hidden">
                    <div class="text-indigo-500 text-[10px] font-bold uppercase tracking-widest mb-1">Top Modules</div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span v-for="m in stats.modules" :key="m.module" class="px-2 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-bold rounded-lg break-all">{{ m.module }}</span>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white p-8 rounded-[32px] border border-slate-200/60 shadow-soft">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Search Keywords</label>
                        <input v-model="search" type="text" placeholder="User, IP, Module..." class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Filter User</label>
                        <select v-model="userId" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Users</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Module</label>
                        <select v-model="moduleName" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Modules</option>
                            <option v-for="m in modules" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Severity</label>
                        <select v-model="severity" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Levels</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Action Type</label>
                        <select v-model="actionType" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Actions</option>
                            <option v-for="a in actionTypes" :key="a" :value="a">{{ a.replace('_', ' ').toUpperCase() }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">From Date</label>
                        <input v-model="dateFrom" type="date" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">To Date</label>
                        <input v-model="dateTo" type="date" class="w-full text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="flex items-end">
                        <button @click="clearFilters" class="w-full h-[42px] bg-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-500 rounded-xl hover:bg-slate-200 transition-all">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Table -->
            <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-soft overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <th class="px-6 py-4">Timestamp</th>
                                <th class="px-6 py-4">User / Role</th>
                                <th class="px-6 py-4">Action</th>
                                <th class="px-6 py-4">Module</th>
                                <th class="px-6 py-4">Context</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-[13px] font-bold text-slate-800 tabular-nums">{{ formatDate(log.created_at) }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">{{ log.ip_address }}</div>
                                </td>
                                <td class="px-6 py-4 min-w-[200px]">
                                    <div class="text-[14px] font-bold text-slate-800 content-protect text-truncate-2" :title="log.user_name">{{ log.user_name }}</div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-indigo-500">{{ log.role }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter border whitespace-nowrap', getSeverityClass(log.severity)]">
                                        {{ log.action_type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[12px] font-bold text-slate-600 content-protect" :title="log.module">{{ log.module }}</span>
                                </td>
                                <td class="px-6 py-4 max-w-[220px]">
                                    <div v-if="log.auditable_type" class="text-[11px] font-medium text-slate-500 content-protect text-truncate-2" :title="`${log.auditable_type} #${log.auditable_id}`">
                                        {{ log.auditable_type.split('\\').pop() }} #{{ log.auditable_id }}
                                    </div>
                                    <div v-else class="text-[11px] text-slate-300 italic">System Action</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest whitespace-nowrap', getStatusClass(log.status)]">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('audit.show', log.id)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100">
                    <Pagination :links="logs.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
