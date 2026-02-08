<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

let pollInterval;

const props = defineProps({
    department: {
        type: Object,
        default: null,
    },
    stats: {
        type: Object,
        required: true,
    },
    assetsByCategory: {
        type: Array,
        default: () => [],
    },
    assetsBySubcategory: {
        type: Array,
        default: () => [],
    },
    topAssetGroups: {
        type: Array,
        default: () => [],
    },
    mostEnteredData: {
        type: Array,
        default: () => [],
    },
    topContributors: {
        type: Array,
        default: () => [],
    },
    activityLogs: {
        type: Array,
        default: () => [],
    },
    logActions: {
        type: Array,
        default: () => [],
    },
    allUsers: {
        type: Array,
        default: () => [],
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    hasDepartmentSelected: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
const isSuperAdmin = computed(() => page.props.auth?.roles?.includes('SuperAdmin') || can('view_all_activity_logs'));

import { ref, watch } from 'vue';

const logFilters = ref({
    user_id: '',
    action: '',
    date: '',
});

const applyFilters = () => {
    router.reload({
        only: ['activityLogs'],
        data: {
            log_user_id: logFilters.value.user_id,
            log_action: logFilters.value.action,
            log_date: logFilters.value.date,
        },
        preserveScroll: true,
        preserveState: true,
    });
};

watch([() => logFilters.value.user_id, () => logFilters.value.action, () => logFilters.value.date], () => {
    applyFilters();
});

onMounted(() => {
    // Auto-refresh dashboard data every 10 seconds
    pollInterval = setInterval(() => {
        router.reload({
            only: ['stats', 'assetsByCategory', 'assetsBySubcategory', 'topAssetGroups', 'mostEnteredData', 'topContributors', 'activityLogs'],
            data: {
                log_user_id: logFilters.value.user_id,
                log_action: logFilters.value.action,
                log_date: logFilters.value.date,
            },
            preserveScroll: true,
            preserveState: true,
        });
    }, 10000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const maxCategoryCount = computed(() => {
    if (!props.assetsByCategory.length) return 1;
    return Math.max(...props.assetsByCategory.map(c => c.total));
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getActivityColor = (action) => {
    switch (action) {
        case 'created': return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
        case 'updated': return 'bg-blue-50 text-blue-700 ring-blue-600/20';
        case 'deleted': 
        case 'force_deleted': return 'bg-rose-50 text-rose-700 ring-rose-600/20';
        case 'restored': return 'bg-amber-50 text-amber-700 ring-amber-600/20';
        case 'login': return 'bg-indigo-50 text-indigo-700 ring-indigo-600/20';
        case 'logout': return 'bg-slate-50 text-slate-700 ring-slate-600/20';
        case 'transfer': return 'bg-[#3d4adb]/10 text-[#3d4adb] ring-[#3d4adb]/20';
        default: return 'bg-slate-50 text-slate-700 ring-slate-600/20';
    }
};

const currentDate = new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="w-full">
                
                <!-- No Department Selected State -->
                <div v-if="!hasDepartmentSelected" class="flex min-h-[60vh] flex-col items-center justify-center rounded-[32px] border border-dashed border-slate-200 bg-white p-16 text-center shadow-soft">
                    <div class="mb-8 flex h-24 w-24 items-center justify-center rounded-[24px] bg-slate-50 text-slate-300 border border-slate-100">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Select Department Context</h3>
                    <p class="mt-3 text-slate-400 max-w-md font-medium">
                        To view real-time analytics and asset distribution, please select an administrative department from the university hierarchy.
                    </p>
                    <div class="mt-10">
                        <AppButton
                            :href="route('departments.select')"
                            label="Select Context"
                            variant="primary"
                            class="!rounded-xl px-10 shadow-premium"
                        />
                    </div>
                </div>

                <!-- Active Department Dashboard -->
                <div v-else>
                    <!-- Dashboard Strategic Header -->
                    <div class="mb-12 flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                        <div class="space-y-1">
                            <div class="flex items-center gap-3">
                                <h2 class="text-3xl font-black tracking-tight text-slate-800 leading-tight">
                                    {{ department?.name }}
                                </h2>
                                <span class="rounded-full bg-[#3d4adb]/10 px-3 py-1 text-[10px] font-black text-[#3d4adb] uppercase tracking-widest border border-[#3d4adb]/20">Operational Hub</span>
                            </div>
                            <p class="text-[15px] font-medium text-slate-400 max-w-2xl">
                                Strategic oversight for {{ department?.name }} infrastructure. Active session: {{ currentDate }}.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <AppButton
                                v-if="can('asset-create')"
                                :href="route('assets.create')"
                                label="Register System"
                                variant="primary"
                                class="!rounded-xl px-8 shadow-premium"
                            >
                                <template #icon>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </template>
                            </AppButton>
                        </div>
                    </div>

                    <!-- Key Metrics Grid -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-10">
                        <!-- Total Assets -->
                        <div class="group relative overflow-hidden rounded-[24px] border border-slate-200/50 bg-white p-7 shadow-soft transition-all hover:shadow-premium hover:-translate-y-1">
                            <dt class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Total Assets</span>
                                <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600 border border-emerald-100 transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-4 flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ stats.assets }}</span>
                            </dd>
                            <div class="mt-6 h-1 w-full rounded-full bg-slate-50 overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500 transition-all duration-1000" style="width: 100%"></div>
                            </div>
                        </div>

                        <!-- Locations / Space -->
                        <div class="group relative overflow-hidden rounded-[24px] border border-slate-200/50 bg-white p-7 shadow-soft transition-all hover:shadow-premium hover:-translate-y-1">
                            <dt class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Facility Scope</span>
                                <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600 border border-blue-100 transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-4">
                                <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ stats.rooms }}</span>
                                <span class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Registered Rooms</span>
                            </dd>
                            <p class="mt-2 text-[11px] font-semibold text-slate-400 uppercase tracking-tighter">
                                Distributed across {{ stats.buildings }} Buildings
                            </p>
                        </div>

                        <!-- Department Users -->
                        <div class="group relative overflow-hidden rounded-[24px] border border-slate-200/50 bg-white p-7 shadow-soft transition-all hover:shadow-premium hover:-translate-y-1">
                            <dt class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Management Team</span>
                                <div class="rounded-xl bg-violet-50 p-2.5 text-violet-600 border border-violet-100 transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-4">
                                <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ stats.users }}</span>
                                <span class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Operators</span>
                            </dd>
                            <p class="mt-2 text-[11px] font-semibold text-slate-400 uppercase tracking-tighter">
                                Assigned to this jurisdiction
                            </p>
                        </div>

                         <!-- Active Categories -->
                         <div class="group relative overflow-hidden rounded-[24px] border border-slate-200/50 bg-white p-7 shadow-soft transition-all hover:shadow-premium hover:-translate-y-1">
                            <dt class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Classifications</span>
                                <div class="rounded-xl bg-amber-50 p-2.5 text-amber-600 border border-amber-100 transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-4 line-clamp-1">
                                <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ assetsByCategory.length }}</span>
                                <span class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Active Categories</span>
                            </dd>
                             <p class="mt-2 text-[11px] font-semibold text-slate-400 uppercase tracking-tighter">
                                Categorized inventory items
                            </p>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        
                        <!-- Left Column: Analytics & Distributions -->
                        <div class="space-y-8 lg:col-span-2">
                            <!-- Assets by Category Chart -->
                            <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 shadow-soft">
                                <h3 class="flex items-center text-lg font-bold text-slate-800 tracking-tight">
                                    <svg class="mr-3 h-5 w-5 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                                    </svg>
                                    Asset Distribution by Category
                                </h3>
                                <div class="mt-8 space-y-6">
                                    <template v-if="assetsByCategory.length > 0">
                                        <div v-for="category in assetsByCategory" :key="category.id" class="group">
                                            <div class="mb-2.5 flex items-center justify-between">
                                                <span class="text-[13px] font-bold text-slate-600 group-hover:text-[#3d4adb] transition-colors">{{ category.name }}</span>
                                                <span class="text-[13px] font-bold text-slate-800">{{ category.total }}</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-50">
                                                <div 
                                                    class="h-full rounded-full bg-[#3d4adb] transition-all duration-700 ease-out" 
                                                    :style="{ width: `${(category.total / maxCategoryCount) * 100}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </template>
                                    <div v-else class="py-12 text-center">
                                        <p class="text-sm text-slate-400 font-medium">No inventory data available for this department.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assets by Subcategory -->
                            <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 shadow-soft">
                                <h3 class="flex items-center text-lg font-bold text-slate-800 tracking-tight mb-8">
                                    <svg class="mr-3 h-5 w-5 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    Detailed Sub-classification
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="sub in assetsBySubcategory" :key="sub.name" class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100/50 hover:border-[#3d4adb]/20 hover:bg-white transition-all shadow-sm">
                                        <span class="text-[13px] font-bold text-slate-600 truncate pr-2">{{ sub.name }}</span>
                                        <span class="text-[11px] font-bold text-slate-800 bg-white px-2.5 py-1 rounded-lg shadow-inner border border-slate-200/50">{{ sub.total }}</span>
                                    </div>
                                    <div v-if="assetsBySubcategory.length === 0" class="col-span-2 text-center py-6 text-slate-400 text-sm font-medium">
                                        No sub-category metrics available at this level.
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Top Asset Groups -->
                                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        Top Asset Groups
                                    </h3>
                                    <ul class="space-y-3">
                                        <li v-for="(group, idx) in topAssetGroups" :key="idx" class="flex items-center justify-between group cursor-default">
                                            <span class="text-sm text-gray-600 truncate mr-2 group-hover:text-primary transition-colors">{{ group.group_name }}</span>
                                            <span class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">{{ group.total }}</span>
                                        </li>
                                        <li v-if="topAssetGroups.length === 0" class="text-sm text-gray-500 italic">No asset groups defined yet.</li>
                                    </ul>
                                </div>

                                <!-- Most Entered Data -->
                                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Most Entered Assets
                                    </h3>
                                    <ul class="space-y-3">
                                        <li v-for="(item, idx) in mostEnteredData" :key="idx" class="flex items-center justify-between group cursor-default">
                                            <span class="text-sm text-gray-600 truncate mr-2 group-hover:text-primary transition-colors">{{ item.name }}</span>
                                            <span class="text-xs font-bold text-primary bg-blue-50 px-2 py-0.5 rounded-full ring-1 ring-blue-500/10">{{ item.total }}</span>
                                        </li>
                                        <li v-if="mostEnteredData.length === 0" class="text-sm text-gray-500 italic">No assets entered yet.</li>
                                    </ul>
                                </div>

                                <!-- Top Contributors (Statistics) -->
                                <div class="col-span-1 md:col-span-2 rounded-[32px] border border-slate-200/60 bg-white p-8 shadow-soft">
                                    <div class="flex items-center justify-between mb-8">
                                        <h3 class="text-lg font-bold text-slate-800 tracking-tight flex items-center gap-3">
                                            <svg class="h-6 w-6 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Operator Efficiency & Contribution
                                        </h3>
                                        <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">{{ topContributors.length }} ACTIVE OPERATORS</span>
                                    </div>

                                    <div v-if="topContributors.length > 0" class="grid grid-cols-1 gap-5">
                                        <div v-for="(contributor, idx) in topContributors" :key="idx" 
                                            class="relative group"
                                        >
                                            <Link :href="route('assets.index', { created_by: contributor.user_id })" 
                                                class="flex flex-col sm:flex-row sm:items-center gap-6 p-6 rounded-[24px] bg-slate-50/50 border border-slate-100 hover:border-[#3d4adb]/20 hover:bg-white hover:shadow-soft transition-all duration-300"
                                            >
                                                <!-- Rank & Avatar -->
                                                <div class="flex items-center gap-4">
                                                    <div class="flex-shrink-0 relative">
                                                        <img v-if="contributor.profile_photo_path" :src="contributor.profile_photo_url" :alt="contributor.name" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-white shadow-sm group-hover:scale-110 transition-transform">
                                                        <div v-else class="h-12 w-12 rounded-2xl bg-[#3d4adb] flex items-center justify-center text-white text-sm font-bold ring-2 ring-white shadow-sm group-hover:scale-110 transition-transform">
                                                            {{ contributor.name.substring(0, 2).toUpperCase() }}
                                                        </div>
                                                        <div v-if="idx < 3" class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-lg bg-amber-400 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                                                            {{ idx + 1 }}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-[15px] font-bold text-slate-800 tracking-tight leading-tight transition-colors">{{ contributor.name }}</p>
                                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ contributor.role || 'System Operator' }}</p>
                                                    </div>
                                                </div>

                                                <!-- Stats Bar -->
                                                <div class="flex-1 w-full sm:w-auto mt-2 sm:mt-0 sm:px-4">
                                                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                                        <span>System Input share</span>
                                                        <span class="text-slate-800">{{ contributor.percentage }}%</span>
                                                    </div>
                                                    <div class="h-2 w-full bg-slate-200/50 rounded-full overflow-hidden">
                                                        <div class="h-full bg-[#3d4adb] rounded-full transition-all duration-1000 ease-out" :style="{ width: `${contributor.percentage}%` }"></div>
                                                    </div>
                                                </div>

                                                <!-- Total Assets Badge -->
                                                <div class="flex-shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center mt-2 sm:mt-0 gap-2">
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] sm:hidden">Total Assets</span>
                                                    <div class="inline-flex items-center px-4 py-1.5 rounded-xl text-sm font-bold bg-white border border-slate-200 text-slate-800 shadow-sm group-hover:border-[#3d4adb]/30 group-hover:text-[#3d4adb] transition-all">
                                                        {{ contributor.total }} <span class="ml-1.5 text-[10px] text-slate-400">UNITS</span>
                                                    </div>
                                                </div>
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <div v-if="topContributors.length === 0" class="text-center py-12">
                                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-[20px] bg-slate-50 text-slate-200 mb-4 border border-slate-100">
                                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-slate-400 font-medium">No contribution data recorded in the current session.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Activity & Quick Links -->
                        <div class="space-y-8">
                            <!-- Activity Log Feed -->
                            <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 shadow-soft">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="flex items-center text-lg font-bold text-slate-800 tracking-tight">
                                         <svg class="mr-3 h-5 w-5 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Audit Stream
                                    </h3>
                                    <span v-if="activityLogs.length > 0" class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                </div>

                                <!-- Filters (Super Admin only) -->
                                <div v-if="isSuperAdmin && (allUsers.length > 0 || logActions.length > 0)" class="mb-8 p-6 rounded-2xl bg-slate-50 border border-slate-100 space-y-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Governance Filter</label>
                                        <select v-model="logFilters.user_id" class="w-full text-[13px] font-semibold border-slate-200 rounded-xl focus:border-[#3d4adb] focus:ring-[#3d4adb]">
                                            <option value="">System-wide Audit</option>
                                            <option v-for="user in allUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Action Type</label>
                                            <select v-model="logFilters.action" class="text-[13px] font-semibold border-slate-200 rounded-xl focus:border-[#3d4adb] focus:ring-[#3d4adb]">
                                                <option value="">Any Operation</option>
                                                <option v-for="action in logActions" :key="action" :value="action">{{ action.replace('_', ' ').toUpperCase() }}</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Timestamp</label>
                                            <input type="date" v-model="logFilters.date" class="text-[13px] font-semibold border-slate-200 rounded-xl focus:border-[#3d4adb] focus:ring-[#3d4adb]" />
                                        </div>
                                    </div>
                                    <button v-if="logFilters.user_id || logFilters.action || logFilters.date" 
                                        @click="logFilters = {user_id: '', action: '', date: ''}"
                                        class="w-full h-10 rounded-xl border border-rose-100 bg-rose-50/50 text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:bg-rose-50 transition-all"
                                    >
                                        Clear Analysis
                                    </button>
                                </div>

                                <div class="flow-root">
                                    <ul v-if="activityLogs.length > 0" role="list" class="space-y-8">
                                        <li v-for="(log, logIdx) in activityLogs" :key="log.id">
                                            <div class="relative flex gap-4">
                                                <div class="relative flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-xl ring-2 ring-white overflow-hidden shadow-sm bg-slate-100 flex items-center justify-center">
                                                        <img v-if="log.user?.profile_photo_path" :src="log.user.profile_photo_url" :alt="log.user.name" class="h-full w-full object-cover">
                                                        <span v-else class="text-[11px] font-bold text-slate-400 uppercase">
                                                            {{ log.user?.name.substring(0, 2) || 'SYS' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <div class="text-[14px] font-semibold text-slate-800 leading-snug">
                                                        <span class="font-bold text-[#3d4adb]">{{ log.user?.name || 'System' }}</span>
                                                        <span class="text-slate-500 ml-1">{{ log.description.toLowerCase().replace(log.user?.name.toLowerCase() || '', '').trim() }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-3 mt-2">
                                                        <span :class="['text-[9px] px-2 py-0.5 rounded-lg font-bold uppercase tracking-widest border', getActivityColor(log.action)]">
                                                            {{ log.action.replace('_', ' ') }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                                                            {{ formatDate(log.created_at) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div v-else class="text-center py-16">
                                        <div class="h-20 w-20 bg-slate-50 text-slate-200 rounded-[24px] flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p class="text-sm text-slate-400 font-medium">No system activity detected.</p>
                                    </div>
                                </div>
                                <div class="mt-10 pt-8 border-t border-slate-100">
                                    <Link 
                                        :href="route('assets.index')"
                                        class="flex items-center justify-center w-full h-12 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-800 transition-all"
                                    >
                                        Access Governance Central
                                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>

                            <!-- News / Announcements Widget -->
                             <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 shadow-soft">
                                <h3 class="flex items-center text-lg font-bold text-slate-800 tracking-tight mb-4">
                                    <svg class="mr-3 h-5 w-5 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.68c.406.463.564.997.564 1.671 0 1.251-.774 2.228-2.09.614" />
                                    </svg>
                                    Communications
                                </h3>
                                <p class="text-[13px] text-slate-500 mb-8 font-medium leading-relaxed">
                                    {{ stats.news }} news publications are currently active on the enterprise portal.
                                </p>
                                <AppButton 
                                    :href="route('media.news.index')"
                                    label="Administrative News Central"
                                    variant="secondary"
                                    full
                                    class="!h-12 !rounded-xl"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
