<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import SmartSpecInput from '@/Components/SmartSpecInput.vue';

import axios from 'axios';
import imageCompression from 'browser-image-compression';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    classifications: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    subCategories: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    roomAssetsSummary: { type: Array, default: () => [] },
    recentAdditions: { type: Array, default: () => [] },
});

const page = usePage();
const successMessage = ref(null);
const countInput = ref(null);
const isTableLoading = ref(false);

const form = useForm({
    entry_type: 'individual', 
    room_id: (new URLSearchParams(window.location.search).get('room_id')) || '',
    category_id: '',
    sub_category_id: '',
    note: '',
    count: 1,
    status: 'active',
    is_shared: false,
    shared_department_ids: [],
    infos: [
        {
            key: '',
            value: '',
            image: null,
        },
    ],
    is_parent: false,
    components: [], // For Bundle Mode
});

const isQuantityLocked = computed(() => {
    return form.infos.some(info => info.value && info.value.trim().length > 0);
});

watch(isQuantityLocked, (newVal) => {
    if (newVal) {
        form.count = 1;
    }
});

// Local state for component entry (not part of main form submission until added)
const tempComponent = ref({
    category_id: '',
    sub_category_id: '',
    status: 'active',
    note: '',
    temp_id: null,
    sub_category_name: '',
    infos: [
        { key: '', value: '', image: null }
    ]
});
const tempClassification = ref('');
watch(tempClassification, (newVal) => {
    if (newVal) {
        const [catId, subId] = newVal.split(':');
        tempComponent.value.category_id = parseInt(catId);
        tempComponent.value.sub_category_id = subId ? parseInt(subId) : '';
    } else {
        tempComponent.value.category_id = '';
        tempComponent.value.sub_category_id = '';
    }
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
    window.addEventListener('keydown', handleGlobalKeydown);
    
    // Set initial classification if category_id exists
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
const componentSelectRef = ref(null);

const handleImageUpload = async (e, type, index) => {
    const file = e.target.files[0];
    if (!file) return;

    const options = {
        maxSizeMB: 0.8, // 800KB target max
        maxWidthOrHeight: 1920, // Full HD max dimension
        useWebWorker: true,
        initialQuality: 0.8 // Start at 80% to preserve text clarity
    };

    try {
        const compressedFile = await imageCompression(file, options);
        if (type === 'parent') {
            form.infos[index].image = compressedFile;
        } else {
            tempComponent.value.infos[index].image = compressedFile;
        }
    } catch (error) {
        console.error('Image compression failed:', error);
        // Fallback to original if compression fails
        if (type === 'parent') {
            form.infos[index].image = file;
        } else {
            tempComponent.value.infos[index].image = file;
        }
    }
};

const getImageUrl = (file) => {
    if (!file) return null;
    return URL.createObjectURL(file);
};

// Dynamic suggestions for subcategories
const dynamicSuggestions = computed(() => {
    if (!form.category_id || !props.subCategories) return [];
    return props.subCategories
        .filter(s => s.category_id === form.category_id && s.id !== form.sub_category_id)
        .slice(0, 10);
});

const availableSubCategories = computed(() => {
    if (!form.category_id || !props.subCategories) return [];
    return props.subCategories.filter(s => s.category_id === form.category_id);
});

const availableCompSubCategories = computed(() => {
    if (!tempComponent.value.category_id || !props.subCategories) return [];
    return props.subCategories.filter(s => s.category_id === tempComponent.value.category_id);
});

const selectedCategoryId = computed(() => form.category_id);

const addInfoRow = () => {
    form.infos.push({ key: '', value: '', image: null });
};

const removeInfoRow = (index) => {
    if (form.infos.length === 1) {
        form.infos[0] = { key: '', value: '', image: null };
        return;
    }
    form.infos.splice(index, 1);
};

// Component Management for Bundle Mode
const addCompInfoRow = () => {
    tempComponent.value.infos.push({ key: '', value: '', image: null });
};

const removeCompInfoRow = (index) => {
    if (tempComponent.value.infos.length === 1) {
        tempComponent.value.infos[0] = { key: '', value: '', image: null };
        return;
    }
    tempComponent.value.infos.splice(index, 1);
};

const addComponentRow = () => {
    // 8. Ensure component classification is mandatory before adding
    if (!tempComponent.value.category_id) {
        if (window.showToast) window.showToast('error', 'Please select a component classification');
        return;
    }

    const selectedCat = props.categories.find(c => c.id === tempComponent.value.category_id);
    const selectedSub = props.subCategories.find(s => s.id === tempComponent.value.sub_category_id);
    
    // Deep clone specs to avoid reference sharing, and filter out empty ones
    // 7. Add validation to prevent saving empty label or value
    const specs = tempComponent.value.infos
        .filter(info => info.key && info.key.trim() !== '' && info.value && info.value.trim() !== '')
        .map(info => ({ ...info }));

    // Fix: Using .name instead of .label as defined in props
    const catName = selectedCat ? (selectedCat.name || selectedCat.label || 'Unknown') : 'Unknown';
    const subName = selectedSub ? (selectedSub.name || selectedSub.label || '') : '';
    const label = subName ? `${catName} - ${subName}` : catName;

    form.components = [
        ...form.components,
        {
            category_id: tempComponent.value.category_id,
            sub_category_id: tempComponent.value.sub_category_id,
            status: tempComponent.value.status,
            note: tempComponent.value.note,
            temp_id: Date.now() + Math.random(), 
            sub_category_name: label,
            infos: specs
        }
    ];

    // Reset for next entry
    tempClassification.value = '';
    tempComponent.value.category_id = '';
    tempComponent.value.sub_category_id = '';
    tempComponent.value.note = '';
    tempComponent.value.infos = [{ key: '', value: '' }];
    
    // Auto-focus back to selector for high-speed entry
    setTimeout(() => {
        componentSelectRef.value?.open();
    }, 50);
};

// New: One-click Quick Add functionality
const quickAddComponent = (subCategoryId) => {
    const selectedSub = props.subCategories.find(s => s.id === subCategoryId);
    const selectedCat = props.categories.find(c => c.id === form.category_id);
    if (!selectedSub || !selectedCat) return;

    form.components = [
        ...form.components,
        {
            category_id: form.category_id,
            sub_category_id: subCategoryId,
            status: 'active',
            note: '',
            temp_id: Date.now() + Math.random(),
            sub_category_name: `${selectedCat.name || selectedCat.label || 'Unknown'} - ${selectedSub.name || selectedSub.label || ''}`,
            infos: []
        }
    ];
};

const removeComponentRow = (index) => {
    form.components.splice(index, 1);
};

const loadRoomContext = (roomId) => {
    if (!roomId) return;
    router.get(route('assets.create'), { room_id: roomId }, {
        preserveState: true,
        preserveScroll: true,
        only: ['recentAdditions', 'roomAssetsSummary'],
        onStart: () => { isTableLoading.value = true; },
        onFinish: () => { isTableLoading.value = false; }
    });
};

watch(() => form.room_id, (newVal) => {
    if (newVal) loadRoomContext(newVal);
});

const setMode = (mode) => {
    form.entry_type = mode;
    form.is_parent = (mode === 'bundle');
    form.count = 1;
};

const submitLabel = computed(() => {
    if (form.processing) return 'Saving...';
    if (form.entry_type === 'bundle') return `Register System (${form.components.length} items)`;
    return form.entry_type === 'series' 
        ? `Register ${form.count} Assets` 
        : 'Register Asset';
});

const handleSubmit = () => {
    form.post(route('assets.store'), {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => { isTableLoading.value = true; },
        onFinish: () => { isTableLoading.value = false; },
        onSuccess: () => {
             // For normal assets, we do the standard reset
            successMessage.value = form.is_parent 
                ? 'System Bundle & Components Registered Successfully.' 
                : `${form.count > 1 ? form.count + ' assets' : 'Asset'} registered successfully.`;
            
            const savedRoomId = form.room_id;
            const savedCatId = form.category_id;
            const savedSubCatId = form.sub_category_id; 
            const savedEntryType = form.entry_type;
            
            form.reset('note', 'count', 'infos', 'is_parent', 'components');
            form.count = 1;
            form.infos = [{ key: '', value: '', image: null }];
            form.components = [];
            
            // Restore context
            form.room_id = savedRoomId;
            form.category_id = savedCatId;
            form.sub_category_id = savedSubCatId;
            form.entry_type = savedEntryType;
            form.is_parent = (savedEntryType === 'bundle');

            setTimeout(() => { successMessage.value = null; }, 4000);
            
            // Speed Mode: Auto-focus back to classification for next entry
            if (!form.is_parent) {
                // Focus classification select using our ref if needed or standard DOM
                document.querySelector('input[placeholder*="Classification"]')?.focus();
            }
        },
    });
};

const handleGlobalKeydown = (e) => {
    if (e.key === 'Enter' && e.ctrlKey) {
        handleSubmit();
    }
};

const getStatusLabel = (status) => {
    const labels = {
        active: 'Active',
        maintenance: 'Maintenance',
        damaged: 'Damaged',
        retired: 'Retired',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Asset Registration" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-1">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1 bg-[#1FA6A0] rounded-full"></div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 uppercase tracking-tighter">Fast Registration</h2>
                        <div class="flex items-center gap-1.5 leading-none">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Speed Mode Enabled</p>
                            <span class="text-[8px] px-1 bg-indigo-50 text-indigo-600 border border-indigo-200 rounded font-black tracking-tighter uppercase">Enterprise Serials V2 Active</span>
                        </div>
                    </div>
                </div>
                <Link :href="route('assets.index')" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-[11px] font-bold text-gray-500 hover:bg-gray-50 transition-all uppercase tracking-widest">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to Inventory
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-2">
            <!-- Success Message (Formal Banner) -->
            <transition name="fade">
                <div v-if="successMessage" class="mb-4 flex items-center justify-center gap-2 bg-[#1FA6A0] text-white py-2 px-4 rounded text-sm font-bold shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    {{ successMessage }}
                </div>
            </transition>

            <form @submit.prevent="handleSubmit" class="space-y-6 pb-40">
                
                <!-- Registration Flow -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <!-- Tab Switcher (Enterprise Mode Selector) -->
                    <div class="flex border-b border-gray-100 bg-gray-50/50">
                        <button 
                            type="button"
                            @click="setMode('individual')"
                            :class="['flex-1 py-5 text-[11px] font-black uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-2', form.entry_type === 'individual' ? 'bg-white text-[#1FA6A0] border-b-2 border-[#1FA6A0]' : 'text-gray-400 hover:text-gray-600 border-transparent hover:bg-gray-100']"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            Standard Item
                        </button>
                        <button 
                            type="button"
                            @click="setMode('bundle')"
                            :class="['flex-1 py-5 text-[11px] font-black uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-2', form.entry_type === 'bundle' ? 'bg-white text-indigo-600 border-b-2 border-indigo-500' : 'text-gray-400 hover:text-gray-600 border-transparent hover:bg-gray-100']"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            Bundle System
                        </button>
                    </div>

                    <div class="p-6 md:p-8 flex flex-col gap-8">
                        <!-- Bundle Mode Banner -->
                        <div v-if="form.entry_type === 'bundle'" class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 uppercase">Single-Page System Entry</h4>
                                <p class="text-xs text-blue-700 leading-relaxed mt-1">
                                    Define the <strong>Parent Asset</strong> (e.g. Computer Set) below, then add all its components. 
                                    Everything will be saved in one transaction.
                                </p>
                            </div>
                        </div>

                        <!-- Step 1: Core Identification -->
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <SearchableSelect
                                        v-model="form.room_id"
                                        :options="rooms"
                                        label="Location / Room"
                                        placeholder="Select room..."
                                        class="w-full"
                                    />
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
                                <div class="space-y-4">
                                     <div>
                                         <label for="status" class="form-group-label">Operational Status</label>
                                         <select
                                             id="status"
                                             v-model="form.status"
                                             class="w-full"
                                         >
                                             <option value="active">Active</option>
                                             <option value="maintenance">Maintenance</option>
                                             <option value="damaged">Damaged</option>
                                             <option value="retired">Retired</option>
                                         </select>
                                     </div>
                                     <div v-if="form.entry_type !== 'bundle'">
                                         <label for="count" class="form-group-label">Quantity</label>
                                         <TextInput
                                             id="count"
                                             ref="countInput"
                                             v-model.number="form.count"
                                             type="number"
                                             min="1"
                                             :disabled="isQuantityLocked"
                                             :class="[
                                               'w-full transition-all',
                                               isQuantityLocked ? 'bg-gray-100 text-gray-500 cursor-not-allowed shadow-inner opacity-75' : ''
                                             ]"
                                         />
                                         <div v-if="isQuantityLocked" class="flex items-center gap-1.5 mt-1.5 animate-in fade-in slide-in-from-top-1 duration-300">
                                             <div class="p-0.5 bg-amber-100 text-amber-600 rounded-full">
                                               <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m0-8V7m0 0a2 2 0 100 4 2 2 0 000-4z" /></svg>
                                             </div>
                                             <span class="text-[9px] font-black uppercase tracking-tighter text-amber-600 italic">Unique item detected → quantity locked to 1</span>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <!-- Step 2: Technical Specifications (Applies to Parent) -->
                        <div class="pt-6 border-t border-gray-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Inventory Specifications (Parent)</h4>
                                <button type="button" @click="addInfoRow" class="text-[10px] font-black text-[#1FA6A0] uppercase hover:underline">+ Add Entry</button>
                            </div>
                            
                            <div class="space-y-3">
                                <div v-for="(info, index) in form.infos" :key="index" class="p-3 bg-gray-50/50 rounded flex gap-2 items-center border border-gray-100">
                                    <div class="flex-1">
                                        <SmartSpecInput
                                            v-model="info.key"
                                            :category-id="selectedCategoryId"
                                            placeholder="Label"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <TextInput v-model="info.value" class="w-full text-xs" placeholder="Value..." />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="cursor-pointer group relative">
                                            <input 
                                                type="file" 
                                                class="hidden" 
                                                accept="image/*" 
                                                capture="environment"
                                                @change="handleImageUpload($event, 'parent', index)"
                                            />
                                            <div :class="[
                                                'p-2 rounded border transition-all flex items-center justify-center',
                                                info.image ? 'border-[#1FA6A0] bg-[#1FA6A0]/10 text-[#1FA6A0]' : 'border-gray-200 bg-white text-gray-400 hover:border-[#1FA6A0] hover:text-[#1FA6A0]'
                                            ]">
                                                <svg v-if="!info.image" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                <div v-else class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                    <span class="text-[9px] font-bold uppercase">Ready</span>
                                                </div>
                                            </div>
                                            <!-- Tooltip/Preview on hover -->
                                            <div v-if="info.image" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                <div class="bg-gray-900 text-white p-1 rounded shadow-xl border border-gray-700">
                                                    <img :src="getImageUrl(info.image)" class="w-24 h-24 object-cover rounded" />
                                                </div>
                                            </div>
                                        </label>

                                        <button v-if="form.infos.length > 1" @click="removeInfoRow(index)" type="button" class="text-gray-300 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional Remarks -->
                        <div class="pt-4">
                            <label class="form-group-label">Internal Remarks</label>
                            <textarea 
                                v-model="form.note"
                                rows="2"
                                class="w-full !italic !text-gray-500"
                                placeholder="Add optional inventory remarks here..."
                            />
                        </div>

                        <!-- SYSTEM COMPONENTS (Bundle Only) - Moved to bottom for better UX -->
                        <div v-if="form.entry_type === 'bundle'" class="border-t border-blue-100 pt-6 mt-4 relative">
                            <h4 class="text-xs font-black uppercase tracking-widest text-[#1FA6A0] mb-4 flex items-center gap-2">
                                <span class="bg-[#1FA6A0]/10 p-1 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </span>
                                System Components
                            </h4>

                            <!-- Add Row Form -->
                            <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100 space-y-4">
                                <div class="grid grid-cols-12 gap-3 items-end">
                                    <div class="col-span-12 md:col-span-5 space-y-3">
                                        <SearchableSelect
                                            v-model="tempClassification"
                                            :options="classifications"
                                            label="Component Classification"
                                            placeholder="Search classification..."
                                            class="w-full"
                                        />
                                    </div>
                                    <div class="col-span-6 md:col-span-3">
                                         <label class="form-group-label">Lifecycle</label>
                                         <select v-model="tempComponent.status" class="w-full !bg-white">
                                             <option value="active">Active</option>
                                             <option value="maintenance">Maintenance</option>
                                             <option value="damaged">Damaged</option>
                                         </select>
                                    </div>
                                    <div class="col-span-6 md:col-span-4">
                                        <button 
                                            type="button" 
                                            @click="addComponentRow"
                                            class="w-full h-[40px] bg-gray-800 text-white text-[10px] font-black uppercase tracking-wider rounded hover:bg-black transition-colors flex items-center justify-center gap-1 shadow-sm"
                                        >
                                            Add To System
                                            <kbd class="hidden md:inline font-mono px-1 py-0.5 bg-gray-700 rounded text-[9px] text-gray-300">↵</kbd>
                                        </button>
                                    </div>
                                </div>

                                <!-- Component Specs Entry -->
                                <div class="space-y-2 border-l-2 border-blue-200 pl-4 py-2">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-[9px] font-black uppercase tracking-widest text-blue-400">Component Specs</h5>
                                        <button type="button" @click="addCompInfoRow" class="text-[9px] font-bold text-blue-500 hover:text-blue-700 uppercase">+ Add Spec</button>
                                    </div>
                                    <div v-for="(info, index) in tempComponent.infos" :key="index" class="flex gap-2">
                                        <div class="flex-1">
                                            <TextInput v-model="info.key" class="w-full h-8 text-[11px]" placeholder="Label (e.g. Serial)" />
                                        </div>
                                        <div class="flex-1">
                                            <TextInput v-model="info.value" class="w-full h-8 text-[11px]" placeholder="Value" />
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="cursor-pointer group relative">
                                                <input 
                                                    type="file" 
                                                    class="hidden" 
                                                    accept="image/*" 
                                                    capture="environment"
                                                    @change="handleImageUpload($event, 'component', index)"
                                                />
                                                <div :class="[
                                                    'p-1.5 rounded border transition-all flex items-center justify-center',
                                                    info.image ? 'border-blue-400 bg-blue-50 text-blue-500' : 'border-gray-200 bg-white text-gray-300 hover:border-blue-400 hover:text-blue-400'
                                                ]">
                                                    <svg v-if="!info.image" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                    <div v-else class="flex items-center gap-1">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                        <span class="text-[8px] font-black">OK</span>
                                                    </div>
                                                </div>
                                                <!-- Preview -->
                                                <div v-if="info.image" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                    <div class="bg-gray-900 text-white p-1 rounded shadow-xl border border-gray-700">
                                                        <img :src="getImageUrl(info.image)" class="w-20 h-20 object-cover rounded" />
                                                    </div>
                                                </div>
                                            </label>

                                            <button v-if="tempComponent.infos.length > 1" @click="removeCompInfoRow(index)" type="button" class="text-gray-300 hover:text-red-500">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Suggestions: Dynamic based on Category -->
                                <div class="flex flex-wrap gap-2" v-if="dynamicSuggestions.length > 0">
                                     <button 
                                        v-for="suggestion in dynamicSuggestions" 
                                        :key="suggestion.id"
                                        type="button" 
                                        @click="quickAddComponent(suggestion.id)" 
                                        class="px-2 py-1 rounded border border-gray-200 bg-white text-[10px] font-medium text-gray-500 hover:border-[#1FA6A0] hover:text-[#1FA6A0] transition-all active:scale-95"
                                     >
                                        + {{ suggestion.label }}
                                     </button>
                                </div>
                                <p v-else-if="form.sub_category_id" class="text-[9px] text-gray-400 italic">No specific component suggestions for this type.</p>
                            </div>

                            <!-- Component Table -->
                            <div v-if="form.components.length > 0" class="mt-4 border border-gray-200 rounded overflow-hidden">
                                <table class="w-full text-left bg-white">
                                    <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2">Component</th>
                                            <th class="px-4 py-2">Status</th>
                                            <th class="px-4 py-2 text-right"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="(comp, idx) in form.components" :key="comp.temp_id">
                                            <td class="px-4 py-2">
                                                <div class="text-xs font-bold text-gray-800">{{ comp.sub_category_name || 'Missing Name' }}</div>
                                                <div v-if="comp.infos && comp.infos.length" class="mt-1 flex flex-wrap gap-1">
                                                    <span v-for="spec in comp.infos" :key="spec.key" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-medium bg-gray-100 text-gray-600">
                                                        {{ spec.key || 'Missing' }}: {{ spec.value || 'Data' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 text-[10px] uppercase font-bold text-gray-500">{{ comp.status }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <button type="button" @click="removeComponentRow(idx)" class="text-red-400 hover:text-red-600">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="mt-4 text-center text-xs text-gray-400 italic">No components added yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Live Audit Trail (Enterprise High-Density Table) -->
                <div v-if="form.room_id" class="space-y-3 pt-6">
                    <div class="flex items-center justify-between px-1">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Recent Activity in this Room</h4>
                        <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest px-2 py-0.5 bg-gray-100 rounded">Live View</span>
                    </div>

                    <div class="bg-white border-y sm:border border-gray-200 sm:rounded overflow-hidden">
                        <table class="w-full text-[11px] text-left">
                            <thead class="bg-gray-50/50 border-b border-gray-100">
                                <tr class="text-gray-400 font-bold uppercase tracking-wider">
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Asset Classification</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Added</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" :class="{ 'animate-pulse opacity-50': isTableLoading }">
                                <tr v-for="asset in props.recentAdditions" :key="asset.id" class="border-b border-gray-50 text-[11px] hover:bg-gray-50/50">
                                    <td class="px-4 py-2">
                                        <div class="font-mono text-[#1FA6A0] text-[10px] font-black">{{ asset.full_serial || asset.asset_code }}</div>
                                        <div v-if="asset.bundle_serial" class="text-[8px] font-black text-gray-400 uppercase tracking-tighter mt-0.5">Reference: {{ asset.bundle_serial }}</div>
                                    </td>
                                    <td class="px-4 py-2 font-bold">{{ asset.name }}</td>
                                    <td class="px-4 py-2 text-center text-gray-400 font-black uppercase">{{ asset.status }}</td>
                                    <td class="px-4 py-2 text-right text-gray-400 tabular-nums">{{ asset.time }}</td>
                                </tr>
                                <tr v-if="props.recentAdditions.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">No assets registered in this room today.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sticky Footer Action Bar (Context-Isolated & Sidebar-Aware) -->
                <div v-if="$page.component === 'Assets/Create'" 
                    class="fixed bottom-0 left-0 lg:left-72 right-0 z-[100] bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-8px_30px_rgb(0,0,0,0.04)] px-4 py-4 sm:px-8"
                >
                    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <div class="hidden md:flex flex-col">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Registration Flow</span>
                            <span class="text-xs font-bold text-[#1FA6A0] mt-1 uppercase tracking-tighter">Inventory Mode Active</span>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <Link :href="route('assets.index')" class="flex-1 sm:flex-none px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-500 hover:text-gray-700 transition-colors text-center">
                                CANCEL
                            </Link>
                            
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-[2] sm:flex-none sm:min-w-[240px] h-12 bg-[#1FA6A0] text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-lg shadow-lg shadow-[#1FA6A0]/20 active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2 group"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <template v-else>
                                    <svg class="h-4 w-4 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ submitLabel }}
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
