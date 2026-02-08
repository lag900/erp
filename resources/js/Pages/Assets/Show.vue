<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({
    asset: { type: Object, required: true },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

// --- Sections Management ---
const collapsed = ref({
    specs: false,
    composition: false,
    history: true,
});
const toggle = (section) => collapsed.value[section] = !collapsed.value[section];

// --- Status System ---
const statusMap = {
    active: { label: 'Operational', class: 'bg-green-50 text-green-700 border-green-100' },
    maintenance: { label: 'Maintenance', class: 'bg-blue-50 text-blue-700 border-blue-100' },
    damaged: { label: 'Faulty', class: 'bg-red-50 text-red-700 border-red-100' },
    retired: { label: 'Decommissioned', class: 'bg-gray-50 text-gray-600 border-gray-100' },
};
const currentStatus = computed(() => statusMap[props.asset.status] || statusMap.active);

const copy = (text) => {
    navigator.clipboard.writeText(text);
    if (window.showToast) window.showToast('success', 'ID copied to clipboard');
};

// --- Child Components Search & Display ---
const componentQuery = ref('');
const filteredChildren = computed(() => {
    if (!props.asset.children) return [];
    const q = componentQuery.value.trim().toLowerCase();
    if (!q) return props.asset.children;
    return props.asset.children.filter(c => 
        (c.asset_code?.toLowerCase().includes(q) || c.name?.toLowerCase().includes(q))
    );
});

// --- Add Component Modal Logic ---
const showAddComponentModal = ref(false);
const search = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const performSearch = debounce(async (query) => {
    if (!query || query.length < 2) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const res = await axios.get(route('api.assets.search'), {
            params: { search: query, exclude_id: props.asset.id }
        });
        searchResults.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        isSearching.value = false;
    }
}, 300);

watch(search, performSearch);

const attachForm = useForm({
    child_id: null
});

const attachAsset = (child) => {
    attachForm.child_id = child.id;
    attachForm.post(route('assets.attach', props.asset.id), {
        onSuccess: () => {
            showAddComponentModal.value = false;
            search.value = '';
            searchResults.value = [];
            if (window.showToast) window.showToast('success', 'Hardware linked successfully');
        }
    });
};

// --- Delete Logic ---
const showDeleteConfirm = ref(false);
const isDeleting = ref(false);
const executeDelete = async () => {
    isDeleting.value = true;
    try {
        await axios.delete(route('assets.destroy', props.asset.id));
        if (window.showToast) window.showToast('success', 'Record archived');
        router.visit(route('assets.index'));
    } catch (e) {
        if (window.showToast) window.showToast('error', 'Failed to archive');
        isDeleting.value = false;
        showDeleteConfirm.value = false;
    }
};
</script>

<template>
    <Head :title="`${asset.short_code} Details`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('assets.index')" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900">{{ asset.short_code }}</h2>
                            <span :class="['px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase border', currentStatus.class]">
                                {{ currentStatus.label }}
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tight">{{ asset.category }} / {{ asset.subCategory }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <Link v-if="can('asset-edit')" :href="route('assets.edit', asset.id)" class="btn-secondary !h-9 !px-4 !text-xs !rounded-xl">Edit Record</Link>
                    <button v-if="can('asset-edit')" @click="showAddComponentModal = true" class="btn-primary !h-9 !px-6 !text-xs !rounded-xl">Add Item to This Computer</button>
                    <Link v-if="can('asset-edit')" :href="route('assets.group.manage', asset.id)" class="btn-secondary !h-9 !px-4 !text-xs !rounded-xl" title="Legacy Grouping">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Main Dashboard -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- Core Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row">
                            <div class="md:w-48 h-48 md:h-auto bg-gray-50 flex items-center justify-center p-4">
                                <div class="w-full h-full rounded-xl overflow-hidden bg-white border border-gray-100 flex items-center justify-center shadow-inner">
                                    <img v-if="asset.image_url" :src="asset.image_url" class="object-cover w-full h-full" />
                                    <svg v-else class="h-10 w-10 text-gray-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                            </div>
                            <div class="flex-1 p-6 md:p-8">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Enterprise Index</p>
                                            <h3 class="text-sm font-black text-gray-900 font-mono mt-0.5 cursor-pointer hover:text-primary transition-colors flex items-center gap-2" @click="copy(asset.full_serial || asset.asset_code)" title="Copy Code">
                                                {{ asset.full_serial || asset.asset_code }}
                                            </h3>
                                        </div>
                                        <div v-if="asset.bundle_serial" class="px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-black shadow-lg shadow-primary/20 ring-1 ring-primary/30">
                                            {{ asset.bundle_serial }}
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                </div>
                                <div class="grid grid-cols-2 gap-6 pb-6 border-b border-gray-50">
                                    <div>
                                        <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Device Group</p>
                                        <p class="text-sm font-bold text-gray-700">{{ asset.group_name || 'Individual Unit' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Base Category</p>
                                        <p class="text-sm font-bold text-gray-700">{{ asset.subCategory }}</p>
                                    </div>
                                </div>
                                <div class="mt-6 flex items-start gap-3">
                                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary shrink-0"></div>
                                    <p class="text-xs text-gray-500 font-medium italic leading-relaxed">
                                        {{ asset.note || 'No specific maintenance or usage notes recorded for this hardware profile.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Components & Hardware List -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <button @click="toggle('composition')" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">Device Components & Hardware</h3>
                                </div>
                                <svg :class="['h-4 w-4 text-gray-300 transition-transform duration-300', collapsed.composition ? '' : 'rotate-180']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div v-show="!collapsed.composition" class="px-6 pb-6 space-y-4">
                                <div v-if="asset.children.length === 0" class="py-10 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-100">
                                    <p class="text-xs text-gray-400 font-medium italic">No accessories or components linked to this system serial.</p>
                                    <button @click="showAddComponentModal = true" class="mt-3 text-[10px] font-black text-primary uppercase tracking-widest">+ Add Component Now</button>
                                </div>
                                <div v-else class="space-y-4">
                                     <div class="flex items-center justify-between">
                                        <div class="relative flex-1 max-w-[200px]">
                                            <input v-model="componentQuery" type="text" placeholder="Filter..." class="w-full h-8 !pl-8 !text-[11px] !rounded-lg !border-gray-100 !bg-gray-50" />
                                            <svg class="absolute left-2.5 top-2.5 h-3 w-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        </div>
                                        <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">{{ filteredChildren.length }} Items Connected</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <Link v-for="child in filteredChildren" :key="child.id" :href="route('assets.show', child.id)" class="flex items-center justify-between p-3 rounded-xl border border-gray-50 hover:bg-gray-50 transition-all group">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2-2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-gray-900 group-hover:text-primary transition-colors flex items-center gap-2">
                                                        {{ child.full_serial || child.asset_code || 'NO-CODE' }}
                                                        <span v-if="child.bundle_serial" class="text-[8px] font-black text-primary uppercase bg-primary/5 px-1 py-0.5 rounded border border-primary/10 tracking-tighter">{{ child.bundle_serial }}</span>
                                                    </p>
                                                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-tight">{{ child.name || 'Unknown Component' }}</p>
                                                    <!-- Specs Preview -->
                                                    <div v-if="child.infos && child.infos.length" class="mt-1 flex flex-wrap gap-1">
                                                        <span v-for="spec in child.infos" :key="spec.key" class="text-[8px] font-medium text-gray-400">
                                                            {{ spec.key }}: {{ spec.value }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="h-1.5 w-1.5 rounded-full" :class="statusMap[child.status]?.class.split(' ')[1] || 'bg-gray-200'"></span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Data -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <button @click="toggle('specs')" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">Technical Specifications</h3>
                                </div>
                                <svg :class="['h-4 w-4 text-gray-300 transition-transform duration-300', collapsed.specs ? '' : 'rotate-180']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div v-show="!collapsed.specs" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="asset.infos.length === 0" class="col-span-2 py-6 text-center text-xs text-gray-400 italic">Core device parameters not indexed.</div>
                                <div v-for="info in asset.infos" :key="info.id" class="p-4 rounded-xl border border-gray-50 bg-gray-50/20">
                                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-tighter">{{ info.key || 'Specification' }}</p>
                                    <p class="text-sm font-bold text-gray-700 mt-0.5">{{ info.value || 'Missing Data' }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Content -->
                    <div class="lg:col-span-4 space-y-6">
                        
                        <!-- Governance -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                            <div>
                                <h3 class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-4">Device Custodian</h3>
                                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-gray-900 flex items-center justify-center text-white font-bold">
                                        {{ asset.department_info.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ asset.department_info.name }}</p>
                                        <p class="text-[9px] font-black text-primary uppercase">{{ asset.department_info.code }} System</p>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-6 border-t border-gray-50">
                                <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-4">Registration Log</p>
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-gray-700 flex items-center justify-center text-[10px] font-black text-white">
                                        {{ asset.creator.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">{{ asset.creator.name }}</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">{{ asset.creator.role || 'Personnel' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Placement -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                            <h3 class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-4">Deployment Placement</h3>
                            <div class="space-y-4">
                                <div class="p-5 rounded-xl bg-gray-900 text-white shadow-lg shadow-gray-200">
                                    <p class="text-[9px] font-black uppercase opacity-40">Static Location</p>
                                    <h4 class="text-base font-bold mt-1">{{ asset.room.name }}</h4>
                                    <p class="text-[10px] font-black text-accent uppercase mt-1 tracking-widest">{{ asset.room.code }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 rounded-xl border border-gray-50 bg-gray-50/10">
                                        <p class="text-[9px] font-black uppercase text-gray-400">Campus</p>
                                        <p class="text-xs font-bold text-gray-700 mt-1 truncate">{{ asset.room.location }}</p>
                                    </div>
                                    <div class="p-3 rounded-xl border border-gray-50 bg-gray-50/10">
                                        <p class="text-[9px] font-black uppercase text-gray-400">Level</p>
                                        <p class="text-xs font-bold text-gray-700 mt-1">{{ asset.room.level }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Archive Option -->
                        <div v-if="can('asset-delete')" class="pt-4">
                            <button @click="showDeleteConfirm = true" class="w-full p-4 rounded-xl border border-red-50 bg-red-50/50 text-red-600 text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                                Archive Hardware Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Component Modal -->
        <Modal :show="showAddComponentModal" @close="showAddComponentModal = false" max-width="md">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 rounded-xl bg-gray-900 flex items-center justify-center text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Add Item to This Computer</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Link peripherals to {{ asset.asset_code }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="relative">
                        <InputLabel for="search" value="Search for Accessory or Part" class="!text-[9px] !font-black !uppercase !text-gray-400 !mb-2" />
                        <TextInput id="search" v-model="search" type="text" class="block w-full !h-12 !text-sm !bg-gray-50 !border-gray-100 !rounded-xl" placeholder="Enter Serial, Name or Category..." />
                        
                        <div v-if="isSearching" class="absolute right-3 top-9">
                            <svg class="h-5 w-5 animate-spin text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </div>

                        <!-- Search Results -->
                        <div v-if="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl border border-gray-100 shadow-2xl z-50 p-1">
                            <button 
                                v-for="res in searchResults" 
                                :key="res.id" 
                                type="button" 
                                @click="attachAsset(res)"
                                class="flex w-full items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors text-left group"
                            >
                                <div>
                                    <p class="text-xs font-bold text-gray-900">{{ res.asset_code }}</p>
                                    <p class="text-[9px] uppercase font-black text-gray-400">{{ res.name }} <span class="text-primary/40">• {{ res.department }}</span></p>
                                </div>
                                <span class="text-[9px] font-black text-primary uppercase opacity-0 group-hover:opacity-100 transition-opacity">Link +</span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-primary/[0.03] p-6 rounded-2xl border border-primary/5">
                        <p class="text-xs text-gray-600 font-medium leading-relaxed">
                            Linking a component will associate its history and maintenance schedule with this primary device. Items must be unassigned to appear here.
                        </p>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-50 flex gap-3">
                    <SecondaryButton @click="showAddComponentModal = false" class="flex-1 !h-12 !rounded-xl !text-[10px] !font-black !uppercase">Cancel Action</SecondaryButton>
                    <PrimaryButton v-if="false" class="flex-1 !h-12 !rounded-xl !text-[10px] !font-black !uppercase">Register New Item</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Archive Modal -->
        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false" max-width="md">
            <div class="p-8">
                <h3 class="text-xl font-bold text-gray-900">Archive Hardware Profile?</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Moving <span class="font-mono font-bold">{{ asset.asset_code }}</span> to archives will preserve historical audit data but remove it from active inventory.
                </p>
                <div class="mt-8 flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1 !h-11 !rounded-xl !text-xs">Discard</SecondaryButton>
                    <DangerButton @click="executeDelete" :disabled="isDeleting" class="flex-1 !h-11 !rounded-xl !text-xs">
                        {{ isDeleting ? 'WAIT...' : 'Confirm archive' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
