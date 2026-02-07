<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

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

const maxCategoryCount = computed(() => {
    if (!props.assetsByCategory.length) return 1;
    return Math.max(...props.assetsByCategory.map(c => c.total));
});

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
                        <Link
                            :href="route('departments.select')"
                            class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            Select Department
                        </Link>
                    </div>
                </div>

                <!-- Active Department Dashboard -->
                <div v-else>
                    <!-- Header -->
                    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-2xl font-bold leading-tight text-gray-900">
                                {{ department?.name }} Dashboard
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ currentDate }} · {{ department?.description || 'Operational Overview' }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                v-if="can('asset-create')"
                                :href="route('assets.create')"
                                class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover transition-colors"
                            >
                                <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Asset
                            </Link>
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
                        </div>

                        <!-- Right Column: Activity & Quick Links -->
                        <div class="space-y-8">
                            <!-- Recent Activity Feed -->
                            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-soft">
                                <h3 class="flex items-center text-base font-semibold text-gray-900 mb-6">
                                     <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Recent Activity
                                </h3>
                                <div class="flow-root">
                                    <ul v-if="recentActivity.length > 0" role="list" class="-mb-8">
                                        <li v-for="(activity, activityIdx) in recentActivity" :key="activity.id">
                                            <div class="relative pb-8">
                                                <span v-if="activityIdx !== recentActivity.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 ring-8 ring-white">
                                                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                Added/Updated <span class="font-medium text-gray-900">{{ activity.name }}</span>
                                                            </p>
                                                            <p class="text-xs text-gray-400 mt-0.5">{{ activity.location }}</p>
                                                        </div>
                                                        <div class="whitespace-nowrap text-right text-xs text-gray-400">
                                                            <time>{{ activity.updated_at }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div v-else class="text-center py-4">
                                        <p class="text-sm text-gray-500">No recent activity.</p>
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 border-t border-gray-50">
                                    <Link :href="route('assets.index')" class="flex items-center justify-center text-sm font-medium text-primary hover:text-primary-hover">
                                        View all assets
                                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
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
                                <Link 
                                    :href="route('media.news.index')"
                                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-center text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                                >
                                    Manage News
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
