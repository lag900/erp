<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const activeTab = ref('individual');

const props = defineProps({
    rooms: {
        type: Array,
        required: true,
    },
    subCategories: {
        type: Array,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    },
    roomAssetsSummary: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

// Watch roomAssetsSummary prop changes
watch(() => props.roomAssetsSummary, (newSummary) => {
    console.log('roomAssetsSummary prop updated:', newSummary);
}, { deep: true });

const form = useForm({
    entry_type: 'individual', // 'individual' or 'series'
    room_id: '',
    sub_category_id: '',
    note: '',
    count: 1,
    serial_number: '',
    condition: 'active',
    is_shared: false,
    shared_department_ids: [],
    infos: [
        {
            key: '',
            value: '',
            image: null,
        },
    ],
    // For series entry
    base_asset: {
        room_id: '',
        sub_category_id: '',
        note: '',
        condition: 'active',
        is_shared: false,
        shared_department_ids: [],
    },
    peered_assets: [],
});

const canAddMoreInfo = computed(() =>
    form.infos.some((info) => info.key || info.value || info.image)
);

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

const getImagePreview = (image) => {
    if (!image) return null;
    if (typeof image === 'string' && image.startsWith('http')) {
        return image;
    }
    if (image instanceof File) {
        return URL.createObjectURL(image);
    }
    return null;
};

// Series functions
const addPeeredAsset = () => {
    form.peered_assets.push({
        room_id: form.base_asset.room_id || '', // Copy room_id from base asset
        sub_category_id: '',
        serial_number: '',
        infos: [
            {
                key: '',
                value: '',
                image: null,
            },
        ],
    });
};

// Function to load room assets summary
const loadRoomAssetsSummary = (roomId) => {
    if (!roomId) return;
    
    console.log('Loading room assets summary for room:', roomId);
    router.get(route('assets.create'), {
        room_id: roomId,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['roomAssetsSummary'],
        onSuccess: (page) => {
            console.log('Room assets summary loaded:', page.props.roomAssetsSummary);
        },
        onError: (errors) => {
            console.error('Error loading room assets summary:', errors);
        },
    });
};

// Watch individual entry room_id
watch(() => form.room_id, (newRoomId, oldRoomId) => {
    console.log('Individual Room changed:', { newRoomId, oldRoomId, activeTab: activeTab.value });
    if (newRoomId && activeTab.value === 'individual' && newRoomId !== oldRoomId) {
        loadRoomAssetsSummary(newRoomId);
    }
});

// Watch base asset room_id and update all peered assets
watch(() => form.base_asset.room_id, (newRoomId, oldRoomId) => {
    console.log('Base Room changed:', { newRoomId, oldRoomId, activeTab: activeTab.value });
    
    // Update all peered assets room_id
    form.peered_assets.forEach((asset) => {
        asset.room_id = newRoomId;
    });
    
    // Load room assets summary when room is selected (in series tab)
    if (newRoomId && activeTab.value === 'series' && newRoomId !== oldRoomId) {
        loadRoomAssetsSummary(newRoomId);
    }
});

// Get room label for display
const getRoomLabel = (roomId) => {
    if (!roomId) return '';
    const room = props.rooms.find((r) => String(r.id) === String(roomId));
    return room ? room.label : '';
};

const removePeeredAsset = (index) => {
    form.peered_assets.splice(index, 1);
};

const canAddMorePeeredAsset = computed(() =>
    form.peered_assets.length === 0 ||
    form.peered_assets.some((asset) => asset.room_id || asset.sub_category_id)
);

const addPeeredInfoRow = (peeredIndex) => {
    form.peered_assets[peeredIndex].infos.push({ key: '', value: '', image: null });
};

const removePeeredInfoRow = (peeredIndex, infoIndex) => {
    const infos = form.peered_assets[peeredIndex].infos;
    if (infos.length === 1) {
        infos[0] = { key: '', value: '', image: null };
        return;
    }
    infos.splice(infoIndex, 1);
};

const canAddMorePeeredInfo = (peeredIndex) => {
    return form.peered_assets[peeredIndex]?.infos?.some((info) => info.key || info.value || info.image) ?? false;
};

const handleSubmit = () => {
    form.entry_type = activeTab.value;
    
    // Clean up unused fields based on entry type to avoid validation errors
    if (activeTab.value === 'individual') {
        // Remove series fields completely
        delete form.base_asset;
        delete form.peered_assets;
    } else {
        // Remove individual fields completely
        delete form.room_id;
        delete form.sub_category_id;
        delete form.note;
        delete form.count;
        delete form.infos;
        // Remove count from base_asset if exists
        if (form.base_asset) {
            delete form.base_asset.count;
        }
        // Ensure all peered assets have the same room_id as base asset
        form.peered_assets.forEach((asset) => {
            asset.room_id = form.base_asset.room_id;
        });
    }
    
    form.post(route('assets.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Add Asset" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Create New Asset
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Register individual assets or connected asset series into the system.
                    </p>
                </div>
                <Link
                    :href="route('assets.index')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Assets
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Mode Switcher -->
                <div class="mb-8 flex justify-center">
                    <div class="inline-flex rounded-lg bg-gray-100 p-1 shadow-inner">
                        <button
                            type="button"
                            :class="[
                                activeTab === 'individual'
                                    ? 'bg-white text-primary shadow-sm ring-1 ring-black/5'
                                    : 'text-gray-500 hover:text-gray-700',
                                'relative flex items-center rounded-md px-6 py-2.5 text-sm font-semibold transition-all duration-200',
                            ]"
                            @click="activeTab = 'individual'"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Individual Entry
                        </button>
                        <button
                            type="button"
                            :class="[
                                activeTab === 'series'
                                    ? 'bg-white text-primary shadow-sm ring-1 ring-black/5'
                                    : 'text-gray-500 hover:text-gray-700',
                                'relative flex items-center rounded-md px-6 py-2.5 text-sm font-semibold transition-all duration-200',
                            ]"
                            @click="activeTab = 'series'"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Connect Series of Assets
                        </button>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-8">
                    
                    <!-- INDIVIDUAL ENTRY MODE -->
                    <div v-if="activeTab === 'individual'" class="space-y-6">
                        
                        <!-- Card 1: Primary Information -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft">
                            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                <p class="text-sm text-gray-500">Define the core classification of the asset.</p>
                            </div>
                            <div class="p-6 grid gap-6 sm:grid-cols-2">
                                <div>
                                    <SearchableSelect
                                        v-model="form.room_id"
                                        :options="rooms"
                                        label="Location / Room"
                                        placeholder="Select location..."
                                        search-placeholder="Search rooms..."
                                        class="w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.room_id" />
                                </div>

                                <div>
                                    <SearchableSelect
                                        v-model="form.sub_category_id"
                                        :options="subCategories"
                                        label="Asset Category"
                                        placeholder="Select category..."
                                        search-placeholder="Search subcategories..."
                                        class="w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.sub_category_id" />
                                </div>

                                <div>
                                    <InputLabel for="count" value="Quantity" />
                                    <TextInput
                                        id="count"
                                        v-model.number="form.count"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full"
                                        placeholder="1"
                                    />
                                    <p class="mt-1 text-xs text-gray-400">Total number of identical items.</p>
                                    <InputError class="mt-2" :message="form.errors.count" />
                                </div>

                                <div>
                                    <InputLabel for="serial_number" value="Serial Number" />
                                    <TextInput
                                        id="serial_number"
                                        v-model="form.serial_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="e.g. SN12345678"
                                    />
                                    <InputError class="mt-2" :message="form.errors.serial_number" />
                                </div>

                                <div>
                                    <InputLabel for="condition" value="Asset Condition" />
                                    <select
                                        id="condition"
                                        v-model="form.condition"
                                        class="mt-1 block w-full rounded-lg border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-primary"
                                    >
                                        <option value="active">Active / Good Condition</option>
                                        <option value="maintenance">Maintenance Required</option>
                                        <option value="damaged">Damaged / Broken</option>
                                        <option value="disposed">Disposed / Out of Service</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.condition" />
                                </div>

                                <div>
                                    <InputLabel for="note" value="Notes / Remarks" />
                                    <textarea
                                        id="note"
                                        v-model="form.note"
                                        rows="2"
                                        class="mt-1 block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                        placeholder="Any additional details..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.note" />
                                </div>
                            </div>
                        </div>

                        <!-- Card: Ownership & Sharing -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft">
                            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                                <h3 class="text-lg font-semibold text-gray-900">Ownership & Sharing</h3>
                                <p class="text-sm text-gray-500">Define which departments can access this asset.</p>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_shared"
                                            class="peer sr-only"
                                        />
                                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Share this asset with other departments</span>
                                    </label>
                                </div>

                                <div v-if="form.is_shared" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div v-for="dept in departments" :key="dept.id" class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input
                                                :id="`dept-${dept.id}`"
                                                v-model="form.shared_department_ids"
                                                :value="dept.id"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label :for="`dept-${dept.id}`" class="font-medium text-gray-700 cursor-pointer">{{ dept.name }}</label>
                                        </div>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.shared_department_ids" />
                            </div>
                        </div>

                        <!-- Card 2: Asset Details -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft">
                            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-6 py-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Asset Specifications</h3>
                                    <p class="text-sm text-gray-500">Add technical details (e.g., Serial Number, IP Address).</p>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-hover hover:underline disabled:opacity-50"
                                    :disabled="!canAddMoreInfo"
                                    @click="addInfoRow"
                                >
                                    + Add Specification
                                </button>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <TransitionGroup name="list" tag="div" class="space-y-4">
                                    <div
                                        v-for="(info, index) in form.infos"
                                        :key="index"
                                        class="relative rounded-lg border border-gray-100 bg-gray-50/50 p-4 transition-all hover:bg-gray-50"
                                    >
                                        <div class="grid gap-4 sm:grid-cols-12">
                                            <div class="sm:col-span-4">
                                                <InputLabel :for="`info-key-${index}`" value="Label (Key)" />
                                                <TextInput
                                                    :id="`info-key-${index}`"
                                                    v-model="info.key"
                                                    class="mt-1 block w-full"
                                                    placeholder="e.g. Serial Number"
                                                />
                                            </div>
                                            <div class="sm:col-span-5">
                                                <InputLabel :for="`info-value-${index}`" value="Value" />
                                                <TextInput
                                                    :id="`info-value-${index}`"
                                                    v-model="info.value"
                                                    class="mt-1 block w-full"
                                                    placeholder="e.g. SN-123456"
                                                />
                                            </div>
                                            <div class="sm:col-span-3">
                                                 <InputLabel :for="`info-image-${index}`" value="Attachment" />
                                                 <div class="mt-1 flex items-center gap-3">
                                                    <label :for="`info-image-${index}`" class="cursor-pointer inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        Upload
                                                    </label>
                                                    <span v-if="info.image" class="text-xs text-green-600 font-medium truncate max-w-[8rem]">
                                                        {{ info.image.name || 'Image Selected' }}
                                                    </span>
                                                    <input
                                                        :id="`info-image-${index}`"
                                                        type="file"
                                                        accept="image/*"
                                                        class="hidden"
                                                        @input="info.image = $event.target.files[0]"
                                                    />
                                                 </div>
                                                 <InputError :message="form.errors[`infos.${index}.image`]" />
                                            </div>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <button
                                            v-if="form.infos.length > 1"
                                            type="button"
                                            class="absolute -top-2 -right-2 rounded-full bg-white p-1 text-gray-400 shadow hover:text-red-500 hover:shadow-md transition-all"
                                            @click="removeInfoRow(index)"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>

                                        <!-- Preview -->
                                        <div v-if="getImagePreview(info.image)" class="mt-3">
                                            <img :src="getImagePreview(info.image)" class="h-16 w-16 rounded-md border border-gray-200 object-cover" />
                                        </div>
                                    </div>
                                </TransitionGroup>
                            </div>
                        </div>

                    </div>

                    <!-- SERIES ENTRY MODE -->
                    <div v-if="activeTab === 'series'" class="space-y-6">
                         <!-- Info Card -->
                        <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4 flex gap-3">
                            <svg class="h-5 w-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="text-sm text-blue-800">
                                <strong>Series Mode:</strong> Create a "Parent" asset (e.g., Computer Tower) and link multiple "Child" assets (e.g., Monitor, Keyboard) to it. All assets will be assigned to the same room.
                            </div>
                        </div>

                        <!-- Card 1: Base Asset -->
                        <div class="overflow-hidden rounded-xl border-2 border-primary/20 bg-white shadow-soft">
                            <div class="border-b border-primary/10 bg-primary/5 px-6 py-4">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">1</span>
                                    Base Asset
                                </h3>
                                <p class="text-sm text-gray-500 ml-8">The main equipment unit.</p>
                            </div>
                            <div class="p-6 grid gap-6 sm:grid-cols-2">
                                <div>
                                    <SearchableSelect
                                        v-model="form.base_asset.room_id"
                                        :options="rooms"
                                        label="Room / Location"
                                        placeholder="Select shared location..."
                                        class="w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors['base_asset.room_id']" />
                                </div>
                                <div>
                                    <SearchableSelect
                                        v-model="form.base_asset.sub_category_id"
                                        :options="subCategories"
                                        label="Category"
                                        placeholder="Select main category..."
                                        class="w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors['base_asset.sub_category_id']" />
                                </div>
                                <div>
                                    <InputLabel for="base_condition" value="Asset Condition" />
                                    <select
                                        id="base_condition"
                                        v-model="form.base_asset.condition"
                                        class="mt-1 block w-full rounded-lg border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-primary"
                                    >
                                        <option value="active">Active / Good Condition</option>
                                        <option value="maintenance">Maintenance Required</option>
                                        <option value="damaged">Damaged / Broken</option>
                                        <option value="disposed">Disposed / Out of Service</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['base_asset.condition']" />
                                </div>
                                <div class="col-span-full">
                                    <InputLabel for="base_note" value="Notes" />
                                    <textarea
                                        id="base_note"
                                        v-model="form.base_asset.note"
                                        rows="2"
                                        class="mt-1 block w-full rounded-lg border-gray-300"
                                        placeholder="Notes for the base unit..."
                                    />
                                </div>

                                <div class="col-span-full pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex cursor-pointer items-center">
                                            <input
                                                type="checkbox"
                                                v-model="form.base_asset.is_shared"
                                                class="peer sr-only"
                                            />
                                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                            <span class="ml-3 text-sm font-medium text-gray-900">Share this series with other departments</span>
                                        </label>
                                    </div>

                                    <div v-if="form.base_asset.is_shared" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        <div v-for="dept in departments" :key="dept.id" class="flex items-start">
                                            <div class="flex h-5 items-center">
                                                <input
                                                    :id="`base-dept-${dept.id}`"
                                                    v-model="form.base_asset.shared_department_ids"
                                                    :value="dept.id"
                                                    type="checkbox"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label :for="`base-dept-${dept.id}`" class="font-medium text-gray-700 cursor-pointer">{{ dept.name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Connected Assets -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                     <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-xs text-gray-600">2</span>
                                     Connected Components
                                </h3>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg bg-white border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                                    :disabled="!canAddMorePeeredAsset"
                                    @click="addPeeredAsset"
                                >
                                    <svg class="mr-2 h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Add Component
                                </button>
                            </div>

                            <TransitionGroup name="list" tag="div" class="space-y-4">
                                <div v-if="form.peered_assets.length === 0" key="empty" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                                    <p class="text-gray-500">No components added yet.</p>
                                    <button @click="addPeeredAsset" type="button" class="mt-2 text-primary font-medium hover:underline">Add the first component</button>
                                </div>

                                <div
                                    v-for="(peeredAsset, index) in form.peered_assets"
                                    :key="index"
                                    class="relative rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md"
                                >
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="font-semibold text-gray-800">Component #{{ index + 1 }}</h4>
                                        <button
                                            type="button"
                                            class="text-xs text-red-600 hover:text-red-800 px-2 py-1 font-medium bg-red-50 rounded hover:bg-red-100 transition-colors"
                                            @click="removePeeredAsset(index)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    
                                    <div class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <InputLabel value="Room" class="text-gray-500" />
                                            <div class="mt-1 p-2 bg-gray-100 rounded text-sm text-gray-600 border border-gray-200">
                                                {{ getRoomLabel(form.base_asset.room_id) || 'Inherited from Base Asset' }}
                                            </div>
                                        </div>
                                        <div>
                                            <SearchableSelect
                                                v-model="peeredAsset.sub_category_id"
                                                :options="subCategories"
                                                label="Component Category"
                                                placeholder="Select category..."
                                                class="w-full"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`peered_assets.${index}.sub_category_id`]" />
                                        </div>
                                        <div>
                                            <InputLabel :for="`serial-${index}`" value="Serial Number" />
                                            <TextInput
                                                :id="`serial-${index}`"
                                                v-model="peeredAsset.serial_number"
                                                class="mt-1 block w-full text-sm"
                                                placeholder="Component serial..."
                                            />
                                        </div>
                                    </div>

                                    <!-- Peered Info Rows -->
                                    <div class="mt-4 border-t border-gray-100 pt-4">
                                        <div class="mb-2 flex justify-between">
                                             <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Specifications</label>
                                             <button type="button" @click="addPeeredInfoRow(index)" class="text-xs text-primary font-medium hover:underline">+ Add Spec</button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(info, infoIndex) in peeredAsset.infos" :key="infoIndex" class="flex gap-2 items-start">
                                                <div class="w-1/3">
                                                    <TextInput v-model="info.key" placeholder="Key" class="w-full text-sm" />
                                                </div>
                                                <div class="w-1/3">
                                                    <TextInput v-model="info.value" placeholder="Value" class="w-full text-sm" />
                                                </div>
                                                <div class="flex-1 flex items-center gap-2">
                                                     <label class="cursor-pointer text-gray-500 hover:text-primary">
                                                         <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                         <input type="file" class="hidden" @input="info.image = $event.target.files[0]" />
                                                     </label>
                                                     <button type="button" @click="removePeeredInfoRow(index, infoIndex)" class="text-gray-400 hover:text-red-500">
                                                         <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                     </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TransitionGroup>
                        </div>
                    </div>

                    <!-- Room Summary (Common) -->
                     <div v-if="(activeTab === 'individual' && form.room_id) || (activeTab === 'series' && form.base_asset.room_id)" class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-4">
                            Current Assets in Room
                        </h4>
                        
                        <div v-if="props.roomAssetsSummary.length === 0" class="text-sm text-gray-500 italic">
                            No existing assets found in this room.
                        </div>
                        
                        <div v-else class="overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 bg-white text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Subcategory</th>
                                        <th class="px-4 py-2 text-center font-medium text-gray-600">Records</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-600">Total Count</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="summary in props.roomAssetsSummary" :key="summary.subcategory_id">
                                        <td class="px-4 py-2 text-gray-900 font-medium">{{ summary.subcategory_name }}</td>
                                        <td class="px-4 py-2 text-center text-gray-500">{{ summary.asset_count }}</td>
                                        <td class="px-4 py-2 text-right text-gray-900 font-bold">{{ summary.total_count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="sticky bottom-0 -mx-8 -mb-8 gap-4 border-t border-gray-200 bg-white px-8 py-4 sm:flex sm:flex-row-reverse sm:items-center sm:justify-between shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] mt-12 z-10">
                         <div class="flex gap-4">
                            <PrimaryButton
                                :disabled="form.processing"
                                class="w-full sm:w-auto justify-center text-base py-2.5 px-6"
                            >
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ activeTab === 'individual' ? 'Save Asset' : 'Save Asset Series' }}
                            </PrimaryButton>
                            
                             <Link
                                :href="route('assets.index')"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto"
                             >
                                Cancel
                             </Link>
                         </div>
                         <p class="text-xs text-gray-400 hidden sm:block">
                             Fields marked with * are required.
                         </p>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
