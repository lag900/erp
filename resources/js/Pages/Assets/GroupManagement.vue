<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    groupTypes: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    group_name: props.asset.group_name || '',
    group_type_id: props.asset.group_type?.id || null,
    child_ids: props.asset.children?.map(child => child.id) || [],
});

const search = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const selectedAssets = ref(props.asset.children || []);

const searchAssets = debounce(async () => {
    if (search.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.ref = true;
    try {
        const response = await axios.get(route('api.assets.search'), {
            params: { 
                search: search.value,
                exclude_id: props.asset.id 
            }
        });
        // Filter out already selected assets
        searchResults.value = response.data.filter(item => 
            !form.child_ids.includes(item.id)
        );
    } catch (error) {
        console.error('Search failed:', error);
    } finally {
        isSearching.value = false;
    }
}, 300);

watch(search, () => {
    searchAssets();
});

const addAsset = (assetToAdd) => {
    if (!form.child_ids.includes(assetToAdd.id)) {
        form.child_ids.push(assetToAdd.id);
        selectedAssets.value.push(assetToAdd);
    }
    search.value = '';
    searchResults.value = [];
};

const removeAsset = (assetId) => {
    form.child_ids = form.child_ids.filter(id => id !== assetId);
    selectedAssets.value = selectedAssets.value.filter(a => a.id !== assetId);
};

const submit = () => {
    form.put(route('assets.group.update', props.asset.id), {
        onSuccess: () => {
            if (window.showToast) window.showToast('success', 'Asset grouping updated successfully');
        }
    });
};
</script>

<template>
    <Head title="Group Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Device Component Management</h2>
                    <p class="text-xs text-gray-500 font-medium">Link hardware accessories and internal parts to this primary device profile.</p>
                </div>
                <Link :href="route('assets.show', asset.id)" class="btn-secondary !h-9 !text-xs !rounded-xl">
                    Back to Details
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Sidebar: Asset Info -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Target Parent</h3>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-gray-900 flex items-center justify-center text-white font-bold text-lg">
                                    {{ asset.short_code.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ asset.asset_code }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tight">{{ asset.subCategory }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-900 p-6 rounded-2xl text-white">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 opacity-50">Instruction</h3>
                            <p class="text-xs leading-relaxed opacity-90">
                                Organizing assets into groups allows you to track them as a single system. Changes here will update the "Collection" view in the asset details page.
                            </p>
                        </div>
                    </div>

                    <!-- Right Main: Settings & Composition -->
                    <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                        
                        <!-- Configuration -->
                        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-50">
                                <h3 class="text-sm font-bold text-gray-900">1. Device Configuration</h3>
                            </div>
                            <div class="p-6 grid gap-6 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <InputLabel for="group_name" value="System Purpose / Device Name" class="!text-[10px] !font-black !uppercase !text-gray-400" />
                                    <TextInput id="group_name" v-model="form.group_name" class="block w-full !h-10 !text-sm !rounded-xl" placeholder="e.g. Lab workstation 01" />
                                </div>
                                <div class="space-y-1.5">
                                    <InputLabel for="group_type" value="Device Category" class="!text-[10px] !font-black !uppercase !text-gray-400" />
                                    <select id="group_type" v-model="form.group_type_id" class="block w-full h-10 px-3 text-sm rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-0 transition-all">
                                        <option :value="null">Uncategorized System</option>
                                        <option v-for="type in groupTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <!-- Composition -->
                        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-50">
                                <h3 class="text-sm font-bold text-gray-900">2. Hardware Components & Parts</h3>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="relative">
                                    <TextInput v-model="search" type="text" class="block w-full !pl-10 !h-11 !text-sm !bg-gray-50 !border-gray-100 !rounded-xl" placeholder="Search by ID or Category..." />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <svg v-if="!isSearching" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    </div>

                                    <div v-if="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl border border-gray-100 shadow-xl z-50 p-1">
                                        <button v-for="res in searchResults" :key="res.id" type="button" @click="addAsset(res)" class="flex w-full items-center justify-between p-3 rounded-lg hover:bg-gray-50 text-left">
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">{{ res.asset_code }}</p>
                                                <p class="text-[9px] uppercase font-black text-gray-400">{{ res.name }} <span class="text-primary/40">• {{ res.department }}</span></p>
                                            </div>
                                            <span class="text-[9px] font-black text-primary uppercase">Add +</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Included Assets ({{ selectedAssets.length }})</p>
                                    <div v-if="selectedAssets.length === 0" class="text-center py-8 bg-gray-50/50 border border-dashed border-gray-100 rounded-xl">
                                        <p class="text-xs text-gray-400 font-medium">No assets assigned.</p>
                                    </div>
                                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="asset in selectedAssets" :key="asset.id" class="flex items-center justify-between p-3 rounded-xl bg-white border border-gray-100 group">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-lg bg-gray-900 flex items-center justify-center text-white text-[10px] font-bold">
                                                    {{ asset.asset_code.split('-').pop().charAt(0) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-gray-900 truncate max-w-[100px]">{{ asset.asset_code }}</p>
                                                    <p class="text-[9px] text-gray-500 font-medium">{{ asset.name }}</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeAsset(asset.id)" class="text-[9px] font-black text-red-400 uppercase tracking-tighter hover:text-red-600 p-1">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="flex items-center justify-end gap-3 pt-2">
                             <Link :href="route('assets.show', asset.id)" class="btn-secondary !h-10 !px-6 !text-xs">Discard Changes</Link>
                             <PrimaryButton :disabled="form.processing" class="!h-10 !px-8 !text-xs !rounded-xl">Complete Configuration</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select {
    @apply h-11 px-4 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-primary/20 transition-all;
}
</style>
