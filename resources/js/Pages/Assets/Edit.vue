<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import SmartSpecInput from '@/Components/SmartSpecInput.vue';
import Modal from '@/Components/Modal.vue';
import imageCompression from 'browser-image-compression';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({
    asset: { type: Object, required: true },
    rooms: { type: Array, default: () => [] },
    classifications: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    subCategories: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    room_id: props.asset.room_id,
    category_id: props.asset.category_id,
    sub_category_id: props.asset.sub_category_id,
    count: props.asset.count ?? 1,
    note: props.asset.note ?? '',
    status: props.asset.status ?? 'active',
    is_shared: !!props.asset.is_shared,
    shared_department_ids: props.asset.shared_department_ids ?? [],
    infos: props.asset.infos?.length
        ? props.asset.infos.map((info) => ({
              id: info.id,
              key: info.key,
              value: info.value ?? '',
              image: null,
              image_url: info.image_url ?? null,
          }))
        : [{ key: '', value: '', image: null, image_url: null }],
});

const classification = ref('');

watch(classification, (newVal) => {
    if (newVal) {
        const [catId, subId] = newVal.split(':');
        form.category_id = parseInt(catId);
        form.sub_category_id = subId ? parseInt(subId) : '';
    } else {
        form.category_id = '';
        form.sub_category_id = '';
    }
});

onMounted(() => {
    if (form.category_id) {
        const found = props.classifications.find(c => 
            c.category_id === form.category_id && 
            c.sub_category_id === (form.sub_category_id || null)
        );
        if (found) {
            classification.value = found.id;
        }
    }
});

const selectedCategoryId = computed(() => form.category_id);

const availableSubCategories = computed(() => {
    if (!form.category_id || !props.subCategories) return [];
    return props.subCategories.filter(s => s.category_id === form.category_id);
});

const availableCompSubCategories = computed(() => {
    if (!componentForm.value.category_id || !props.subCategories) return [];
    return props.subCategories.filter(s => s.category_id === componentForm.value.category_id);
});

const addInfoRow = () => {
    form.infos.push({ key: '', value: '', image: null, image_url: null });
};

const removeInfoRow = (index) => {
    if (form.infos.length === 1) {
        form.infos[0] = { key: '', value: '', image: null, image_url: null };
        return;
    }
    form.infos.splice(index, 1);
};

const handleImageUpload = async (e, index) => {
    const file = e.target.files[0];
    if (!file) return;
    const options = { maxSizeMB: 0.8, maxWidthOrHeight: 1920, useWebWorker: true, initialQuality: 0.8 };
    try {
        const compressedFile = await imageCompression(file, options);
        form.infos[index].image = compressedFile;
        form.infos[index].image_url = URL.createObjectURL(compressedFile);
    } catch (e) {
        form.infos[index].image = file;
        form.infos[index].image_url = URL.createObjectURL(file);
    }
};

const getImagePreview = (image, imageUrl) => {
    if (image instanceof File) return URL.createObjectURL(image);
    return imageUrl || null;
};

// --- Component Management ---
const components = ref([...(props.asset.components || [])]);
const showAddModal = ref(false);
const isCreating = ref(false);
const search = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const componentForm = ref({
    category_id: '',
    sub_category_id: '',
    status: 'active',
    note: '',
    infos: [{ key: 'Serial No', value: '' }]
});

const modalClassification = ref('');
watch(modalClassification, (newVal) => {
    if (newVal) {
        const [catId, subId] = newVal.split(':');
        componentForm.value.category_id = parseInt(catId);
        componentForm.value.sub_category_id = subId ? parseInt(subId) : '';
    } else {
        componentForm.value.category_id = '';
        componentForm.value.sub_category_id = '';
    }
});

const performSearch = debounce(async (query) => {
    if (!query || query.length < 2) { searchResults.value = []; return; }
    isSearching.value = true;
    try {
        const res = await axios.get(route('api.assets.search'), { params: { search: query, exclude_id: props.asset.id } });
        searchResults.value = res.data.filter(r => !components.value.some(c => c.id === r.id));
    } catch (e) { console.error(e); } finally { isSearching.value = false; }
}, 300);

watch(search, performSearch);

const attachAsset = async (child) => {
    try {
        await axios.post(route('assets.attach', props.asset.id), { child_id: child.id });
        components.value.push({ id: child.id, asset_code: child.asset_code, name: child.name, status: 'active' });
        search.value = ''; searchResults.value = [];
        if (window.showToast) window.showToast('success', 'Hardware attached');
    } catch (e) { if (window.showToast) window.showToast('error', 'Failed to attach'); }
};

const registerNewComponent = async () => {
    // 8. Ensure component classification is mandatory before adding
    if (!componentForm.value.category_id) {
        if (window.showToast) window.showToast('error', 'Select classification');
        return;
    }

    // 7. Add validation to prevent saving empty label or value
    const validInfos = componentForm.value.infos.filter(i => i.key && i.key.trim() !== '' && i.value && i.value.trim() !== '');

    isCreating.value = true;
    try {
        const payload = {
            ...componentForm.value,
            infos: validInfos
        };
        const res = await axios.post(route('assets.components.store', props.asset.id), payload);
        components.value.push(res.data);
        showAddModal.value = false;
        modalClassification.value = '';
        componentForm.value = { category_id: '', sub_category_id: '', status: 'active', note: '', infos: [{ key: 'Serial No', value: '' }] };
        if (window.showToast) window.showToast('success', 'New component registered');
    } catch (e) { 
        if (window.showToast) window.showToast('error', 'Registration failed'); 
    } finally { 
        isCreating.value = false; 
    }
};

const detachComponent = async (childId) => {
    try {
        await axios.delete(route('assets.detach', props.asset.id), { data: { child_id: childId } });
        components.value = components.value.filter(c => c.id !== childId);
        if (window.showToast) window.showToast('success', 'Hardware detached');
    } catch (e) { if (window.showToast) window.showToast('error', 'Failed to detach'); }
};

const statusMap = {
    active: 'bg-green-50 text-green-700',
    maintenance: 'bg-blue-50 text-blue-700',
    damaged: 'bg-red-50 text-red-700',
    retired: 'bg-gray-50 text-gray-500',
};
</script>

<template>
    <Head title="Edit Hardware System" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('assets.show', asset.id)" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:bg-gray-50 hover:text-primary shadow-soft">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <div class="flex flex-col">
                        <h2 class="text-xl font-bold text-gray-900">Modify Hardware System</h2>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tight">Enterprise Index: <span class="text-primary font-mono tracking-normal">{{ asset.asset_code }}</span></p>
                            <div v-if="asset.bundle_serial" class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-primary/10 border border-primary/20">
                                <span class="text-[9px] font-black text-primary uppercase tracking-tighter">Serial: {{ asset.bundle_serial }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4">
                <form @submit.prevent="form.transform(data => ({ ...data, _method: 'put' })).post(route('assets.update', asset.id), { forceFormData: true })" class="space-y-6">
                    
                    <!-- Deployment Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/20">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Environment & Custody</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2 space-y-2">
                                <SearchableSelect
                                    v-model="classification"
                                    :options="classifications"
                                    label="Asset Classification"
                                    placeholder="Search classification (e.g. Computers - DELL)"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.category_id" />
                                <InputError :message="form.errors.sub_category_id" />
                            </div>
                            <div class="space-y-2">
                                <InputLabel for="room_id" value="Stationed Room" class="!text-[9px] !font-black !uppercase !text-gray-400" />
                                <select id="room_id" v-model="form.room_id" class="w-full h-12 rounded-xl border-gray-100 bg-gray-50 text-sm focus:bg-white transition-all">
                                    <option value="" disabled>Choose Placement</option>
                                    <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.label }}</option>
                                </select>
                                <InputError :message="form.errors.room_id" />
                            </div>
                            <div class="space-y-2">
                                <InputLabel for="status" value="Operational Health" class="!text-[9px] !font-black !uppercase !text-gray-400" />
                                <select id="status" v-model="form.status" class="w-full h-12 rounded-xl border-gray-100 bg-gray-50 text-sm focus:bg-white transition-all">
                                    <option value="active">Operational (Active)</option>
                                    <option value="maintenance">Under Service</option>
                                    <option value="damaged">Faulty / Broken</option>
                                    <option value="retired">Out of Service</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <InputLabel for="note" value="Administrative Remarks" class="!text-[9px] !font-black !uppercase !text-gray-400" />
                                <textarea id="note" v-model="form.note" rows="2" class="w-full rounded-xl border-gray-100 bg-gray-50 text-sm focus:bg-white transition-all p-4" placeholder="Provide context for registry changes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- System Components Section -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 bg-gray-900 text-white flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest text-white/70">Hardware System Composition</h3>
                                <p class="text-[9px] font-bold text-primary uppercase mt-0.5">Manage sub-items linked to this serial</p>
                            </div>
                            <button type="button" @click="showAddModal = true" class="text-[10px] font-black uppercase tracking-widest px-4 py-2 bg-primary rounded-xl text-white shadow-lg active:scale-95 transition-all">+ Register New Item</button>
                        </div>
                        <div class="p-8 space-y-8">
                            <!-- Attach Existing -->
                            <div class="relative max-w-xl mx-auto">
                                <InputLabel value="Attach Existing Hardware" class="text-center !text-[9px] !font-black !uppercase !text-gray-400 !mb-3" />
                                <TextInput v-model="search" type="text" placeholder="Search Serial or Label to link..." class="w-full !h-12 !bg-gray-50 !border-gray-100 !rounded-xl text-center !text-sm" />
                                <div v-if="isSearching" class="absolute right-4 top-10"><svg class="h-4 w-4 animate-spin text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></div>
                                
                                <div v-if="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl border border-gray-100 shadow-2xl z-50 p-2 overflow-hidden">
                                     <button v-for="res in searchResults" :key="res.id" type="button" @click="attachAsset(res)" class="flex w-full items-center justify-between p-4 rounded-xl hover:bg-gray-50 transition-colors text-left group">
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 font-mono">{{ res.asset_code }}</p>
                                            <p class="text-[10px] uppercase font-black text-gray-400 tracking-tight">{{ res.name }}</p>
                                        </div>
                                        <span class="text-[10px] font-black text-primary uppercase opacity-0 group-hover:opacity-100">Attach +</span>
                                     </button>
                                </div>
                            </div>

                            <!-- Components List -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="components.length === 0" class="md:col-span-2 py-12 text-center border-2 border-dashed border-gray-50 rounded-3xl">
                                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-300 mb-4">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    </div>
                                    <p class="text-sm text-gray-400 font-medium italic">Standalone system. No peripheral hardware attached.</p>
                                </div>
                                <div v-for="comp in components" :key="comp.id" class="p-5 rounded-2xl border border-gray-50 bg-gray-50/30 flex items-center justify-between group hover:bg-white hover:shadow-soft transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-300">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 font-mono group-hover:text-primary transition-colors">{{ comp.asset_code || 'NO-CODE' }}</p>
                                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-0.5">{{ comp.name || 'Missing Name' }}</p>
                                            <!-- Render Specs for Component -->
                                            <div v-if="comp.infos && comp.infos.length" class="mt-1 flex flex-wrap gap-1">
                                                <span v-for="spec in comp.infos" :key="spec.key" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-medium bg-gray-100 text-gray-500">
                                                    {{ spec.key || 'Missing' }}: {{ spec.value || 'Data' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span :class="['px-2.5 py-1 rounded-lg text-[9px] font-black uppercase', statusMap[comp.status] || 'bg-gray-100']">{{ comp.status }}</span>
                                        <button type="button" @click="detachComponent(comp.id)" class="p-2 rounded-xl text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Specs Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/20 flex items-center justify-between">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Index Specifications</h3>
                            <button type="button" @click="addInfoRow" class="text-[9px] font-black text-primary uppercase tracking-widest">+ Append Data Point</button>
                        </div>
                        <div class="p-8 space-y-4">
                            <div v-for="(info, index) in form.infos" :key="index" class="relative p-6 rounded-2xl border border-gray-50 bg-gray-50/10 group grid grid-cols-1 md:grid-cols-2 gap-6">
                                <button type="button" @click="removeInfoRow(index)" class="absolute -top-3 -right-3 h-8 w-8 rounded-xl bg-red-50 text-red-500 border border-red-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <div class="space-y-2">
                                    <InputLabel value="Key Identity" class="!text-[8px] !font-black !uppercase !text-gray-400" />
                                    <SmartSpecInput v-model="info.key" :category-id="selectedCategoryId" class="w-full !h-11 !text-sm !bg-white !rounded-xl" placeholder="e.g. Serial, Model..." />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Data Value" class="!text-[8px] !font-black !uppercase !text-gray-400" />
                                    <TextInput v-model="info.value" class="w-full !h-11 !text-sm !bg-white !rounded-xl" placeholder="Registry value..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between py-8 border-t border-gray-100">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight italic leading-relaxed">System update will be strictly logged under your administrator profile.</p>
                        <div class="flex gap-4">
                            <Link :href="route('assets.show', asset.id)" class="px-8 py-3 text-xs font-black text-gray-400 uppercase tracking-[0.2em] hover:text-gray-900 transition-colors">Discard Changes</Link>
                            <PrimaryButton :disabled="form.processing" class="!h-12 !px-10 !rounded-xl !shadow-xl active:scale-95 transition-all">Commit Full Profile Update</PrimaryButton>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <!-- Add Component Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false" max-width="lg">
            <div class="p-8">
                <div class="mb-8">
                    <h3 class="text-xl font-black text-gray-900">Register New Hardware</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Add peripheral to system serial {{ asset.asset_code }}</p>
                </div>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <SearchableSelect
                                v-model="modalClassification"
                                :options="classifications"
                                label="Component Classification"
                                placeholder="Search classification..."
                                class="w-full"
                            />
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Lifecycle Status" class="!text-[9px] !font-black !uppercase !text-gray-400" />
                            <select v-model="componentForm.status" class="w-full h-12 rounded-xl border-gray-100 bg-gray-50 text-sm focus:bg-white transition-all">
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                    </div>

                    <div v-for="(info, i) in componentForm.infos" :key="i" class="grid grid-cols-2 gap-4">
                        <div class="space-y-1"><TextInput v-model="info.key" class="w-full !h-10 border-gray-50 !bg-gray-100/50 !text-xs font-bold" readonly /></div>
                        <div class="space-y-1"><TextInput v-model="info.value" class="w-full !h-10 !text-sm border-gray-100" placeholder="Enter Serial / Label..." /></div>
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Technician Notes" class="!text-[9px] !font-black !uppercase !text-gray-400" />
                        <textarea v-model="componentForm.note" rows="2" class="w-full rounded-xl border-gray-100 bg-gray-50 text-sm p-4 h-24" placeholder="Optional notes for this specific part..."></textarea>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-50 flex gap-4">
                    <SecondaryButton @click="showAddModal = false" class="flex-1 !h-12 !rounded-xl !text-[10px] !font-black !uppercase">Cancel Registry</SecondaryButton>
                    <PrimaryButton @click="registerNewComponent" :disabled="isCreating || !componentForm.category_id" class="flex-1 !h-12 !rounded-xl !text-[10px] !font-black !uppercase">
                        {{ isCreating ? 'Registering...' : 'Add to System' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
