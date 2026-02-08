<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import AppButton from '@/Components/AppButton.vue';
import Pagination from '@/Components/Pagination.vue';
import throttle from 'lodash/throttle';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    assets: Object, // Paginated object
    filters: Object,
    meta: Object, // { buildings, categories, departments, creators }
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
const isSuperAdmin = computed(() => page.props.auth?.user?.roles?.includes('SuperAdmin'));
const isAssetManager = computed(() => page.props.auth?.user?.roles?.includes('Admin'));

// Dynamic data for sub-filters
const rooms = ref([]);
const subCategories = ref([]);

// Filter State
const filterForm = ref({
    search: props.filters.search || '',
    statuses: props.filters.statuses || [],
    building_id: props.filters.building_id || '',
    room_id: props.filters.room_id || '',
    category_id: props.filters.category_id || '',
    sub_category_id: props.filters.sub_category_id || '',
    department_id: props.filters.department_id || '',
    created_by: props.filters.created_by || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    asset_type: props.filters.asset_type || '',
});

const previewImage = ref(null);
const showImagePreview = (url) => {
    previewImage.value = url;
};

// Dropdown states
const showStatusFilter = ref(false);

const applyFilters = throttle(() => {
    // 1. Purge empty/null values
    const clean = {};
    for (const [key, value] of Object.entries(filterForm.value)) {
        if (value === null || value === '' || value === undefined) continue;
        if (Array.isArray(value)) {
            const validItems = value.filter(v => v !== '' && v !== null);
            if (validItems.length > 0) clean[key] = validItems;
        } else {
            clean[key] = value;
        }
    }

    router.get(route('assets.index'), clean, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(() => filterForm.value, () => {
    applyFilters();
}, { deep: true });

// Fetch Rooms when Building changes
watch(() => filterForm.value.building_id, async (newBuildingId) => {
    if (!newBuildingId) {
        rooms.value = [];
        filterForm.value.room_id = '';
        return;
    }
    try {
        const response = await axios.get(route('api.rooms', { building_id: newBuildingId }));
        rooms.value = response.data;
    } catch (e) {
        console.error('Failed to fetch rooms');
    }
});

// Fetch SubCategories when Category changes
watch(() => filterForm.value.category_id, async (newCategoryId) => {
    if (!newCategoryId) {
        subCategories.value = [];
        filterForm.value.sub_category_id = '';
        return;
    }
    try {
        const response = await axios.get(route('api.sub-categories', { category_id: newCategoryId }));
        subCategories.value = response.data;
    } catch (e) {
        console.error('Failed to fetch sub-categories');
    }
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (filterForm.value.search) count++;
    if (filterForm.value.statuses.length > 0) count++;
    if (filterForm.value.building_id) count++;
    if (filterForm.value.room_id) count++;
    if (filterForm.value.category_id) count++;
    if (filterForm.value.sub_category_id) count++;
    if (filterForm.value.department_id) count++;
    if (filterForm.value.created_by) count++;
    if (filterForm.value.date_from) count++;
    if (filterForm.value.date_to) count++;
    return count;
});

const toggleStatus = (status) => {
    const index = filterForm.value.statuses.indexOf(status);
    if (index === -1) {
        filterForm.value.statuses.push(status);
    } else {
        filterForm.value.statuses.splice(index, 1);
    }
};

const resetFilters = () => {
    filterForm.value = {
        search: '',
        statuses: [],
        building_id: '',
        room_id: '',
        category_id: '',
        sub_category_id: '',
        department_id: '',
        created_by: '',
        date_from: '',
        date_to: '',
        asset_type: '',
    };

    // Force reload with cleared params
    router.get(route('assets.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'active': return 'badge-success';
        case 'maintenance': return 'badge-warning';
        case 'damaged': return 'badge-danger';
        case 'retired': return 'badge-neutral';
        case 'donated': return 'badge-primary';
        case 'lost': return 'bg-slate-800 text-white border-slate-900 badge-base';
        default: return 'badge-neutral';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        active: 'Active',
        maintenance: 'Maintenance',
        damaged: 'Damaged',
        retired: 'Retired',
        donated: 'Donated',
        lost: 'Lost'
    };
    return labels[status] || status || 'Unknown';
};

// Drag and Drop Logic
const draggedAssetId = ref(null);
const dropTargetDeptId = ref(null);

const onDragStart = (event, assetId) => {
    draggedAssetId.value = assetId;
    event.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (event, deptId) => {
    event.preventDefault();
    dropTargetDeptId.value = deptId;
};

const onDrop = async (event, deptId) => {
    event.preventDefault();
    const assetId = draggedAssetId.value;
    draggedAssetId.value = null;
    dropTargetDeptId.value = null;

    if (!assetId || !deptId) return;

    window.showConfirm({
        title: 'Transfer Asset Ownership?',
        message: 'Are you sure you want to reassign this asset to a different department? This will be logged in the movement history.',
        confirmText: 'Transfer',
        onConfirm: async () => {
            try {
                await axios.post(route('assets.transfer', assetId), {
                    department_id: deptId,
                    reason: 'Department transfer via Drag & Drop'
                });
                router.reload({ only: ['assets'] });
                window.showToast('success', 'Asset ownership transferred.');
            } catch (error) {
                window.showToast('error', error.response?.data?.message || 'Transfer failed.');
            }
        }
    });
};

const expandedAssets = ref([]);

const toggleExpand = (assetId) => {
    if (expandedAssets.value.includes(assetId)) {
        expandedAssets.value = expandedAssets.value.filter(id => id !== assetId);
    } else {
        expandedAssets.value.push(assetId);
    }
};

// Deletion Logic
const confirmDeletion = (asset) => {
    window.showConfirm({
        title: 'Archive Asset Record?',
        message: `Are you sure you want to archive ${asset.asset_code}? This will remove it from active inventory while preserving history for audit compliance.`,
        confirmText: 'Archive Asset',
        confirmVariant: 'danger',
        onConfirm: () => executeDelete(asset.id),
    });
};

const executeDelete = async (id) => {
    try {
        await axios.delete(route('assets.destroy', id));
        router.reload({
            only: ['assets'],
            onSuccess: () => {
                window.showToast('success', 'Asset successfully archived.');
            }
        });
    } catch (error) {
        window.showToast('error', error.response?.data?.error || 'Failed to archive asset.');
    }
};

onMounted(async () => {
    // Hydrate dependent filters if parent is selected
    if (filterForm.value.building_id) {
        try {
            const res = await axios.get(route('api.rooms', { building_id: filterForm.value.building_id }));
            rooms.value = res.data;
        } catch (e) { }
    }

    if (filterForm.value.category_id) {
        try {
            const res = await axios.get(route('api.sub-categories', { category_id: filterForm.value.category_id }));
            subCategories.value = res.data;
        } catch (e) { }
    }
});
</script>

<template>

    <Head title="Enterprise Asset Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-slate-800 tracking-tight">
                        Asset Inventory
                    </h2>
                    <p class="mt-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        Global Asset Governance & Life-cycle Management
                    </p>
                </div>
                <div class="flex gap-3">
                    <AppButton v-if="can('asset-create')" :href="route('assets.create')" variant="primary"
                        class="!rounded-xl shadow-premium px-6">
                        <template #icon>
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </template>
                        Register Asset
                    </AppButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="space-y-6">

                <!-- Advanced Filtering Bar -->
                <div class="bg-white border border-slate-200 rounded-3xl p-7 shadow-soft space-y-6">
                    <!-- Row 1: Search & Type -->
                    <div class="flex flex-col md:flex-row items-end gap-5">
                        <div class="flex-1 w-full">
                            <label class="label-enterprise">Global Search</label>
                            <div class="relative group">
                                <TextInput v-model="filterForm.search" type="text" class="w-full !pl-12"
                                    placeholder="Search by code, room, or category..." />
                                <div
                                    class="absolute inset-y-0 left-4 flex items-center text-slate-300 group-focus-within:text-[#3d4adb] transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-72">
                            <label class="label-enterprise">Classification</label>
                            <select v-model="filterForm.asset_type" class="w-full">
                                <option value="">Global Hierarchy</option>
                                <option value="individual">Standalone Items</option>
                                <option value="bundle">System Bundles</option>
                                <option value="component">System Components</option>
                            </select>
                        </div>

                        <button @click="resetFilters"
                            class="h-[44px] px-6 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-400 hover:bg-slate-50 hover:text-slate-800 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Clear Filters
                        </button>
                    </div>

                    <!-- Row 2: Location & Category -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="label-enterprise">Building</label>
                            <select v-model="filterForm.building_id" class="w-full">
                                <option value="">All Buildings</option>
                                <option v-for="b in meta.buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-enterprise">Location / Room</label>
                            <select v-model="filterForm.room_id" :disabled="!filterForm.building_id" class="w-full">
                                <option value="">All Rooms</option>
                                <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-enterprise">Category</label>
                            <select v-model="filterForm.category_id" class="w-full">
                                <option value="">All Categories</option>
                                <option v-for="c in meta.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-enterprise">System Status</label>
                            <select v-model="filterForm.statuses[0]" class="w-full">
                                <option value="">All Statuses</option>
                                <option value="active">Active (Functional)</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="damaged">Damaged</option>
                                <option value="retired">Retired</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div v-if="filterForm.category_id">
                            <label class="label-enterprise">Sub-category</label>
                            <select v-model="filterForm.sub_category_id" class="w-full !border-[#3d4adb]/30">
                                <option value="">All {{ meta.categories.find(c => c.id === filterForm.category_id)?.name }} Sub-categories</option>
                                <option v-for="sc in subCategories" :key="sc.id" :value="sc.id">{{ sc.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Active Filters & Results Summary -->
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">
                            Showing <span class="text-gray-900">{{ assets.total }}</span> assets
                        </span>
                        <div v-if="activeFiltersCount > 0"
                            class="flex items-center gap-1.5 rounded-full bg-primary/5 px-3 py-1 ring-1 ring-primary/20">
                            <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                            <span class="text-[10px] font-black uppercase tracking-tighter text-primary">Filters active
                                ({{
                                activeFiltersCount }})</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Table Section -->
                    <div class="flex-1 w-full overflow-hidden">

                        <div v-if="assets.data.length === 0"
                            class="rounded-3xl border border-dashed border-gray-200 bg-white p-24 text-center shadow-soft">
                            <div
                                class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-50 text-gray-300">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-black text-gray-900 uppercase">No Matches Found</h3>
                            <p class="mt-2 text-gray-400 font-medium">Try refining your enterprise filters or check the
                                audit
                                logs.</p>
                            <button @click="resetFilters"
                                class="mt-8 text-sm font-black text-primary uppercase tracking-widest hover:underline">Clear
                                all
                                filters</button>
                        </div>

                        <div v-else class="space-y-6">
                            <div class="table-container">
                                <table class="ent-table">
                                    <thead>
                                        <tr>
                                            <th class="!w-16">Item</th>
                                            <th>Identifier & Label</th>
                                            <th>Location</th>
                                            <th>Governance</th>
                                            <th>History</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template v-for="asset in assets.data" :key="asset.id">
                                            <tr
                                                class="group border-b border-gray-100 hover:bg-gray-50/30 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="h-12 w-12 rounded-xl border border-slate-100 bg-slate-50 overflow-hidden flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-[#3d4adb] transition-all"
                                                        @click="showImagePreview(asset.image_url)">
                                                        <img v-if="asset.image_url" :src="asset.image_url"
                                                            class="h-full w-full object-cover" />
                                                        <svg v-else class="h-6 w-6 text-slate-300" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex flex-col gap-1">
                                                                <div class="flex items-center gap-2">
                                                                    <Link :href="route('assets.show', asset.id)"
                                                                        class="text-[14px] font-bold text-slate-800 hover:text-[#3d4adb] leading-tight">
                                                                        {{ asset.category || 'General Asset' }}
                                                                        <span v-if="asset.subCategory" class="text-slate-400 font-medium ml-1">— {{ asset.subCategory }}</span>
                                                                    </Link>

                                                                    <div v-if="asset.is_parent"
                                                                        class="flex h-5 w-5 items-center justify-center rounded bg-indigo-50 text-indigo-600 cursor-pointer hover:bg-indigo-100 transition-colors ml-1"
                                                                        @click="toggleExpand(asset.id)"
                                                                        title="Expand System Components">
                                                                        <svg class="h-3 w-3"
                                                                            :class="{ 'rotate-90': expandedAssets.includes(asset.id) }"
                                                                            fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                                        </svg>
                                                                    </div>

                                                                    <!-- SERIAL BADGE -->
                                                                    <span v-if="asset.bundle_serial" class="series-badge">
                                                                        {{ asset.bundle_serial }}
                                                                    </span>
                                                                    <span v-else-if="asset.series_no || asset.parent_id" class="series-badge">
                                                                        {{ asset.short_code }}
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="text-[10px] font-black font-mono text-gray-500 bg-gray-50 px-2 py-0.5 rounded border border-gray-200 self-start shadow-sm tracking-tight">
                                                                    {{ asset.full_serial || asset.asset_code || 'PENDING' }}
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <p class="text-[13px] font-semibold text-[#1F2937]">{{
                                                            asset.room?.name || 'Central Hall' }}</p>
                                                        <p
                                                            class="text-[11px] font-bold text-[#6B7280] uppercase tracking-tight">
                                                            {{ asset.owner_department || 'University' }}</p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            :class="['inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider', getStatusBadgeClass(asset.status)]">
                                                            {{ getStatusLabel(asset.status) }}
                                                        </span>
                                                        <span v-if="asset.count > 1"
                                                            class="text-[10px] font-bold text-gray-400 bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded">Qty:
                                                            {{ asset.count }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col leading-tight gap-1">
                                                        <span class="text-[11px] text-gray-500 font-medium">
                                                            <span class="text-gray-400 text-[10px]">Created by:</span> {{ asset.created_by }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-400">
                                                            Date: {{ asset.created_at_formatted }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex justify-end gap-2 px-2">
                                                        <Link :href="route('assets.show', asset.id)"
                                                            class="px-3 py-1.5 rounded-lg border border-slate-100 text-[10px] font-black uppercase tracking-widest text-[#3d4adb] hover:bg-[#3d4adb] hover:text-white transition-all shadow-sm">
                                                            Verify</Link>
                                                        <Link v-if="can('asset-edit')"
                                                            :href="route('assets.edit', asset.id)"
                                                            class="px-3 py-1.5 rounded-lg border border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50 transition-all shadow-sm">
                                                            Edit</Link>
                                                        <button v-if="can('asset-delete')"
                                                            @click="confirmDeletion(asset)"
                                                            class="p-2 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-all flex items-center justify-center border border-transparent hover:border-rose-100"
                                                            title="Archive Asset">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Hierarchical Children Rows -->
                                            <template v-if="asset.is_parent && expandedAssets.includes(asset.id)">
                                                <tr v-for="child in asset.children" :key="'child-' + child.id"
                                                    class="bg-gray-50/20 border-b border-gray-100">
                                                    <td class="px-6 py-2 flex justify-center">
                                                        <div class="h-8 w-8 rounded border border-gray-200 bg-white overflow-hidden flex items-center justify-center shadow-xs cursor-pointer hover:ring-2 hover:ring-[#3d4adb] transition-all"
                                                            @click="showImagePreview(child.image_url)">
                                                            <img v-if="child.image_url" :src="child.image_url"
                                                                class="h-full w-full object-cover" />
                                                            <svg v-else class="h-4 w-4 text-gray-300" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-2 pl-12 relative">
                                                        <!-- Simple tree line -->
                                                        <div class="absolute left-6 top-0 bottom-0 w-px bg-gray-200">
                                                        </div>
                                                        <div class="absolute left-6 top-1/2 w-4 h-px bg-gray-200"></div>
                                                        <div class="flex flex-col gap-1">
                                                            <div class="flex items-center gap-2">
                                                                <Link :href="route('assets.show', child.id)"
                                                                    class="text-[11px] font-bold text-[#1F2937] hover:text-[#3d4adb] truncate">
                                                                    {{ child.name }}
                                                                </Link>
                                                                 <span v-if="child.bundle_serial" class="series-badge !text-[11px] !px-2 !py-0.5">
                                                                    {{ child.bundle_serial }}
                                                                </span>
                                                                <span v-else-if="child.series_no" class="series-badge !text-[11px] !px-2 !py-0.5">
                                                                    {{ child.short_code }}
                                                                </span>
                                                            </div>
                                                             <span
                                                                class="text-[9px] font-black font-mono text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 self-start">{{
                                                                child.full_serial || child.asset_code }}</span>

                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-2">
                                                        <span
                                                            class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">System
                                                            Component</span>
                                                    </td>
                                                    <td class="px-6 py-2 text-center">
                                                        <span
                                                            class="text-[9px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded bg-white border border-gray-100 text-gray-500">{{
                                                            child.status }}</span>
                                                    </td>
                                                    <td class="px-6 py-2">
                                                        <div class="flex flex-col leading-tight gap-0.5">
                                                            <span class="text-[10px] text-gray-500 font-medium">
                                                                <span class="text-gray-400 text-[9px]">Created by:</span> {{ child.created_by }}
                                                            </span>
                                                            <span class="text-[9px] text-gray-400">
                                                                Date: {{ child.created_at_formatted }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-2 text-right">
                                                        <Link :href="route('assets.show', child.id)"
                                                            class="text-[10px] font-bold text-[#3d4adb] hover:underline uppercase">
                                                            Details</Link>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr v-if="asset.is_parent && asset.children.length === 0 && expandedAssets.includes(asset.id)"
                                                class="bg-gray-50/30">
                                                <td colspan="6"
                                                    class="px-6 py-4 text-center text-[11px] text-gray-400 italic border-b border-gray-50">
                                                    No components registered in this system yet.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="assets.links" />
                        </div>
                    </div>

                    <div v-if="(isSuperAdmin || isAssetManager) && meta.departments.length > 0" class="w-full lg:w-80">
                        <div
                            class="sticky top-8 rounded-[32px] border border-slate-200 bg-white p-8 shadow-premium">
                            <h3
                                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center">
                                <svg class="mr-3 h-4 w-4 text-[#3d4adb]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Transfer Hub
                            </h3>
                            <p class="text-[11px] text-slate-400 mb-8 font-medium leading-relaxed uppercase tracking-tighter">Drag an asset into a department below to reassign ownership.</p>

                            <div class="space-y-3">
                                <div v-for="dept in meta.departments" :key="dept.id" class="relative group"
                                    @dragover="onDragOver($event, dept.id)" @drop="onDrop($event, dept.id)">
                                    <div class="flex items-center justify-between rounded-2xl border p-4 transition-all duration-300"
                                        :class="[dropTargetDeptId === dept.id ? 'border-[#3d4adb] bg-[#3d4adb]/5 scale-[1.02] shadow-inner' : 'border-transparent bg-slate-50/50 hover:bg-white hover:border-slate-200 hover:shadow-soft']">
                                        <div class="flex items-center gap-4 overflow-hidden">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white border border-slate-100 shadow-sm transition-transform group-hover:scale-110">
                                                <span class="text-xs font-bold text-[#3d4adb]">{{ dept.name.substring(0, 2).toUpperCase() }}</span>
                                            </div>
                                            <span
                                                class="truncate text-xs font-bold text-slate-700 uppercase tracking-tighter">{{
                                                dept.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ERP-Grade Full Preview Modal -->
    <Teleport to="body">
        <div v-if="previewImage"
            class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-4 sm:p-20"
            @click="previewImage = null">
            <div class="relative max-h-full max-w-5xl rounded-2xl overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200"
                @click.stop>
                <img :src="previewImage" class="max-h-[85vh] w-auto object-contain" />
                <div class="absolute top-4 right-4 flex gap-2">
                    <button @click="previewImage = null"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md hover:bg-white/40 shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

</template>

<style scoped>
.series-badge {
    background: #3d4adb;
    color: white;
    font-size: 13px;
    padding: 3px 10px;
    border-radius: 9px;
    margin-left: 8px;
    font-weight: 800;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 12px rgba(61, 74, 219, 0.2);
    font-family: 'Inter', sans-serif;
    letter-spacing: -0.01em;
}
</style>
