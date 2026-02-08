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
        case 'created': return 'bg-green-100 text-green-700 ring-green-600/20';
        case 'updated': return 'bg-blue-100 text-blue-700 ring-blue-600/20';
        case 'deleted': 
        case 'force_deleted': return 'bg-red-100 text-red-700 ring-red-600/20';
        case 'restored': return 'bg-yellow-100 text-yellow-700 ring-yellow-600/20';
        case 'login': return 'bg-purple-100 text-purple-700 ring-purple-600/20';
        case 'logout': return 'bg-orange-100 text-orange-700 ring-orange-600/20';
        case 'transfer': return 'bg-cyan-100 text-cyan-700 ring-cyan-600/20';
        case 'role_created':
        case 'permission_created': return 'bg-indigo-100 text-indigo-700 ring-indigo-600/20';
        case 'role_updated':
        case 'permission_moved':
        case 'permission_bulk_assign': return 'bg-blue-100 text-blue-700 ring-blue-600/20';
        case 'role_deleted':
        case 'permission_deleted':
        case 'permission_bulk_revoke': return 'bg-rose-100 text-rose-700 ring-rose-600/20';
        default: return 'bg-gray-100 text-gray-700 ring-gray-600/20';
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
                <div v-if="!hasDepartmentSelected" class="flex min-h-[60vh] flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                    <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-gray-200">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Select a Department</h3>
                    <p class="mt-2 text-gray-500 max-w-md">
                        Please select a department to view its dashboard, assets, and operational analytics.
                    </p>
                    <div class="mt-8">
                        <AppButton
                            :href="route('departments.select')"
                            label="Select Department"
                            size="md"
                        />
                    </div>
                </div>

                <!-- Active Department Dashboard -->
                <div v-else>
                    <!-- Header -->
                    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-[22px] font-semibold tracking-tight text-gray-900">
                                {{ department?.name }} Dashboard
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ currentDate }} · {{ department?.description || 'Operational Overview' }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <AppButton
                                v-if="can('asset-create')"
                                :href="route('assets.create')"
                                label="Add Asset"
                                variant="primary"
                                size="md"
                            >
                                <template #icon>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </template>
                            </AppButton>
                        </div>
                    </div>

                    <!-- Key Metrics Grid -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                        <!-- Total Assets -->
                        <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-soft transition hover:shadow-md">
                            <dt class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500">Total Assets</span>
                                <div class="rounded-md bg-green-50 p-2 text-green-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-gray-900">{{ stats.assets }}</span>
                            </dd>
                            <div class="mt-4 h-1 w-full rounded-full bg-gray-50">
                                <div class="h-1 rounded-full bg-green-500" style="width: 100%"></div>
                            </div>
                        </div>

                        <!-- Locations / Space -->
                        <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-soft transition hover:shadow-md">
                            <dt class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500">Space Utilization</span>
                                <div class="rounded-md bg-blue-50 p-2 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-2">
                                <span class="text-2xl font-bold text-gray-900">{{ stats.rooms }}</span>
                                <span class="text-sm font-medium text-gray-500 ml-1">Rooms</span>
                            </dd>
                            <p class="mt-1 text-xs text-gray-500">
                                Across {{ stats.buildings }} Buildings
                            </p>
                        </div>

                        <!-- Department Users -->
                        <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-soft transition hover:shadow-md">
                            <dt class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500">Team Members</span>
                                <div class="rounded-md bg-purple-50 p-2 text-purple-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-gray-900">{{ stats.users }}</span>
                            </dd>
                            <p class="mt-1 text-xs text-gray-500">
                                Assigned to this department
                            </p>
                        </div>

                         <!-- Active Categories -->
                         <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-soft transition hover:shadow-md">
                            <dt class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500">Asset Categories</span>
                                <div class="rounded-md bg-orange-50 p-2 text-orange-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                            </dt>
                            <dd class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-gray-900">{{ assetsByCategory.length }}</span>
                            </dd>
                             <p class="mt-1 text-xs text-gray-500">
                                Total Types of Assets
                            </p>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        
                        <!-- Left Column: Analytics & Distributions -->
                        <div class="space-y-8 lg:col-span-2">
                            <!-- Assets by Category Chart -->
                            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                <h3 class="flex items-center text-base font-semibold text-gray-900">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                                    </svg>
                                    Assets by Category
                                </h3>
                                <div class="mt-6 space-y-5">
                                    <template v-if="assetsByCategory.length > 0">
                                        <div v-for="category in assetsByCategory" :key="category.id" class="group">
                                            <div class="mb-2 flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 group-hover:text-primary">{{ category.name }}</span>
                                                <span class="text-sm font-medium text-gray-900">{{ category.total }}</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                                <div 
                                                    class="h-full rounded-full bg-primary transition-all duration-500" 
                                                    :style="{ width: `${(category.total / maxCategoryCount) * 100}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </template>
                                    <div v-else class="py-8 text-center">
                                        <p class="text-sm text-gray-500">No assets data available yet.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assets by Subcategory -->
                            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                <h3 class="flex items-center text-base font-semibold text-gray-900 mb-6">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    Assets by Subcategory
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="sub in assetsBySubcategory" :key="sub.name" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100 hover:border-primary/30 transition-colors">
                                        <span class="text-sm font-medium text-gray-700 truncate pr-2">{{ sub.name }}</span>
                                        <span class="text-xs font-bold text-gray-900 bg-white px-2.5 py-1 rounded-md shadow-sm border border-gray-100">{{ sub.total }}</span>
                                    </div>
                                    <div v-if="assetsBySubcategory.length === 0" class="col-span-2 text-center py-4 text-sm text-gray-500">
                                        No subcategory data available based on current assets.
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
                                <div class="col-span-1 md:col-span-2 rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                            <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Team Contribution Analytics
                                        </h3>
                                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ topContributors.length }} Contributors</span>
                                    </div>

                                    <!-- Distribution Overview (Simple CSS Bar) -->
                                    <div v-if="topContributors.length > 0" class="mb-8">
                                        <div class="h-4 w-full flex overflow-hidden rounded-full bg-gray-100">
                                            <div v-for="(contributor, idx) in topContributors" :key="idx" 
                                                 class="h-full border-r border-white last:border-0 hover:opacity-80 transition-opacity relative group/bar"
                                                 :style="{ width: `${contributor.percentage}%`, backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#3b82f6', '#64748b'][idx % 8] }"
                                            >
                                                <!-- Tooltip -->
                                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover/bar:opacity-100 pointer-events-none whitespace-nowrap z-10">
                                                    {{ contributor.name }}: {{ contributor.percentage }}%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between mt-2 text-xs text-gray-400">
                                            <span>High Usage</span>
                                            <span>Asset Distribution</span>
                                        </div>
                                    </div>

                                    <!-- Contributors List -->
                                    <div class="grid grid-cols-1 gap-4">
                                        <div v-for="(contributor, idx) in topContributors" :key="idx" 
                                            class="relative group"
                                        >
                                            <Link :href="route('assets.index', { created_by: contributor.user_id })" 
                                                class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl bg-gray-50/50 border border-gray-100 hover:border-indigo-200 hover:bg-white hover:shadow-sm transition-all duration-200"
                                            >
                                                <!-- Rank & Avatar -->
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 relative">
                                                        <img v-if="contributor.profile_photo_path" :src="contributor.profile_photo_url" :alt="contributor.name" class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                                                        <div v-else class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-white shadow-sm">
                                                            {{ contributor.name.substring(0, 2).toUpperCase() }}
                                                        </div>
                                                        <div v-if="idx < 3" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-yellow-400 text-[10px] shadow-sm ring-1 ring-white">
                                                            {{ idx + 1 }}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ contributor.name }}</p>
                                                        <p class="text-xs text-gray-500 truncate">{{ contributor.role || 'Contributor' }}</p>
                                                    </div>
                                                </div>

                                                <!-- Stats Bar -->
                                                <div class="flex-1 w-full sm:w-auto mt-2 sm:mt-0 sm:px-4">
                                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                                        <span class="text-gray-500">Contribution</span>
                                                        <span class="font-medium text-gray-900">{{ contributor.percentage }}%</span>
                                                    </div>
                                                    <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-500 group-hover:bg-indigo-600" :style="{ width: `${contributor.percentage}%` }"></div>
                                                    </div>
                                                </div>

                                                <!-- Total Assets Badge -->
                                                <div class="flex-shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center mt-2 sm:mt-0 gap-2">
                                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium sm:hidden">Total Assets</span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-bold bg-white border border-gray-200 text-gray-900 shadow-sm group-hover:border-indigo-200 group-hover:text-indigo-600">
                                                        {{ contributor.total }}
                                                    </span>
                                                </div>
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <div v-if="topContributors.length === 0" class="text-center py-8">
                                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 italic">No team contributions recorded yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Activity & Quick Links -->
                        <div class="space-y-8">
                            <!-- Activity Log Feed -->
                            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="flex items-center text-base font-semibold text-gray-900">
                                         <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Activity Log
                                    </h3>
                                    <span v-if="activityLogs.length > 0" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-0.5 rounded border border-gray-100">Live</span>
                                </div>

                                <!-- Filters (Super Admin only) -->
                                <div v-if="isSuperAdmin && (allUsers.length > 0 || logActions.length > 0)" class="mb-6 grid grid-cols-1 gap-3 border-b border-gray-50 pb-6">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Filter by User</label>
                                        <select v-model="logFilters.user_id" class="text-xs rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50">
                                            <option value="">All Users</option>
                                            <option v-for="user in allUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Action</label>
                                            <select v-model="logFilters.action" class="text-xs rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50">
                                                <option value="">Any Action</option>
                                                <option v-for="action in logActions" :key="action" :value="action">{{ action.replace('_', ' ').toUpperCase() }}</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Date</label>
                                            <input type="date" v-model="logFilters.date" class="text-xs rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50" />
                                        </div>
                                    </div>
                                    <button v-if="logFilters.user_id || logFilters.action || logFilters.date" 
                                        @click="logFilters = {user_id: '', action: '', date: ''}"
                                        class="text-[10px] font-bold text-red-500 hover:text-red-600 flex items-center justify-center gap-1 mt-1"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        Clear Filters
                                    </button>
                                </div>

                                <div class="flow-root">
                                    <ul v-if="activityLogs.length > 0" role="list" class="-mb-8">
                                        <li v-for="(log, logIdx) in activityLogs" :key="log.id">
                                            <div class="relative pb-8">
                                                <span v-if="logIdx !== activityLogs.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <!-- User Avatar / Info -->
                                                    <div class="relative">
                                                        <div class="h-8 w-8 rounded-full ring-4 ring-white overflow-hidden shadow-sm">
                                                            <img v-if="log.user?.profile_photo_path" :src="log.user.profile_photo_url" :alt="log.user.name" class="h-full w-full object-cover">
                                                            <div v-else class="h-full w-full flex items-center justify-center bg-gray-100 text-[10px] font-black text-gray-400 uppercase">
                                                                {{ log.user?.name.substring(0, 2) || 'SYS' }}
                                                            </div>
                                                        </div>
                                                        <span :class="['absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full ring-2 ring-white font-black text-[8px] shadow-sm', getActivityColor(log.action)]">
                                                            {{ log.action.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>

                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-0.5">
                                                        <div>
                                                            <p class="text-[13px] text-gray-600 leading-snug">
                                                                <span class="font-bold text-gray-900">{{ log.user?.name || 'System' }}</span>
                                                                {{ log.description.toLowerCase().replace(log.user?.name.toLowerCase() || '', '').trim() }}
                                                            </p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span :class="['text-[9px] px-1.5 py-0.5 rounded-md font-black uppercase tracking-wider', getActivityColor(log.action)]">
                                                                    {{ log.action.replace('_', ' ') }}
                                                                </span>
                                                                <span class="text-[9px] text-gray-400 font-medium">
                                                                    {{ formatDate(log.created_at) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div v-else class="text-center py-12">
                                        <div class="h-12 w-12 bg-gray-50 text-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p class="text-sm text-gray-400 italic">No activity matching your criteria.</p>
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 border-t border-gray-50 flex justify-center">
                                    <AppButton 
                                        variant="ghost" 
                                        size="sm" 
                                        :href="route('assets.index')"
                                        label="View detailed assets"
                                    >
                                        <template #trailing>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </template>
                                    </AppButton>
                                </div>
                            </div>

                            <!-- News / Announcements Widget -->
                             <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                <h3 class="flex items-center text-base font-semibold text-gray-900 mb-4">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.68c.406.463.564.997.564 1.671 0 1.251-.774 2.228-2.09.614" />
                                    </svg>
                                    Announcements
                                </h3>
                                <p class="text-sm text-gray-500 mb-4">
                                    {{ stats.news }} news items are currently published to the portal.
                                </p>
                                <AppButton 
                                    :href="route('media.news.index')"
                                    label="Manage News"
                                    variant="secondary"
                                    full
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
