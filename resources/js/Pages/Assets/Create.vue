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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Add Asset
                </h2>
                <Link
                    :href="route('assets.index')"
                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Back to Assets
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="mb-6 rounded bg-white shadow">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button
                                type="button"
                                :class="[
                                    activeTab === 'individual'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'w-1/2 border-b-2 px-6 py-4 text-center text-sm font-medium',
                                ]"
                                @click="activeTab = 'individual'"
                            >
                                Individual Entry
                            </button>
                            <button
                                type="button"
                                :class="[
                                    activeTab === 'series'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'w-1/2 border-b-2 px-6 py-4 text-center text-sm font-medium',
                                ]"
                                @click="activeTab = 'series'"
                            >
                                Connect Series of Assets
                            </button>
                        </nav>
                    </div>
                </div>

                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="handleSubmit"
                >
                    <!-- Individual Entry Tab -->
                    <div v-if="activeTab === 'individual'">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <SearchableSelect
                                    v-model="form.room_id"
                                    :options="rooms"
                                    label="Room"
                                    placeholder="Select room..."
                                    search-placeholder="Search rooms..."
                                />
                                <InputError class="mt-2" :message="form.errors.room_id" />
                            </div>

                            <div>
                                <SearchableSelect
                                    v-model="form.sub_category_id"
                                    :options="subCategories"
                                    label="Subcategory"
                                    placeholder="Select subcategory..."
                                    search-placeholder="Search by subcategory or category name..."
                                />
                                <InputError class="mt-2" :message="form.errors.sub_category_id" />
                            </div>
                        </div>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="count" value="Count" />
                                <TextInput
                                    id="count"
                                    v-model.number="form.count"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    placeholder="Number of items"
                                />
                                <InputError class="mt-2" :message="form.errors.count" />
                            </div>

                            <div>
                                <InputLabel for="note" value="Note" />
                                <textarea
                                    id="note"
                                    v-model="form.note"
                                    rows="3"
                                    class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Additional notes"
                                />
                                <InputError class="mt-2" :message="form.errors.note" />
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Asset Information
                                </h3>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    :disabled="!canAddMoreInfo"
                                    @click="addInfoRow"
                                >
                                    Add Row
                                </button>
                            </div>

                            <div class="mt-4 space-y-4">
                                <div
                                    v-for="(info, index) in form.infos"
                                    :key="index"
                                    class="rounded border border-gray-200 p-4"
                                >
                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <div>
                                            <InputLabel
                                                :for="`info-key-${index}`"
                                                value="Key"
                                            />
                                            <TextInput
                                                :id="`info-key-${index}`"
                                                v-model="info.key"
                                                class="mt-1 block w-full"
                                                placeholder="e.g. ip"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                :for="`info-value-${index}`"
                                                value="Value"
                                            />
                                            <TextInput
                                                :id="`info-value-${index}`"
                                                v-model="info.value"
                                                class="mt-1 block w-full"
                                                placeholder="e.g. 192.168.1.10"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                :for="`info-image-${index}`"
                                                value="Image (optional)"
                                            />
                                            <div v-if="getImagePreview(info.image)" class="mb-2">
                                                <img
                                                    :src="getImagePreview(info.image)"
                                                    alt="Preview"
                                                    class="h-24 w-24 rounded border border-gray-300 object-cover"
                                                />
                                            </div>
                                            <input
                                                :id="`info-image-${index}`"
                                                type="file"
                                                accept="image/*"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                @input="info.image = $event.target.files[0]"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`infos.${index}.image`]" />
                                            <p class="mt-1 text-xs text-gray-500">
                                                Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button
                                            type="button"
                                            class="text-xs text-red-600 hover:text-red-700"
                                            @click="removeInfoRow(index)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <InputError class="mt-2" :message="form.errors['infos.*.key']" />
                        </div>

                        <!-- Room Assets Summary for Individual Entry -->
                        <div v-if="form.room_id" class="mt-8 rounded border border-gray-200 bg-white p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">
                                Room Assets Summary
                            </h3>
                            <p class="mb-4 text-sm text-gray-600">
                                Current assets in <span class="font-medium">{{ getRoomLabel(form.room_id) }}</span>
                            </p>
                            
                            <div v-if="props.roomAssetsSummary.length === 0" class="rounded border border-gray-200 bg-gray-50 p-4 text-center text-gray-500">
                                No assets found in this room.
                            </div>
                            
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-gray-700">
                                                Category
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-gray-700">
                                                Subcategory
                                            </th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-700">
                                                Asset Records
                                            </th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-700">
                                                Total Count
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr
                                            v-for="summary in props.roomAssetsSummary"
                                            :key="summary.subcategory_id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ summary.category_name }}
                                            </td>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ summary.subcategory_name }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-600">
                                                {{ summary.asset_count }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-semibold text-indigo-600">
                                                {{ summary.total_count }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Series Entry Tab -->
                    <div v-if="activeTab === 'series'">
                        <div class="mb-6 rounded border border-indigo-200 bg-indigo-50 p-4">
                            <p class="text-sm text-indigo-800">
                                <strong>Connect Series:</strong> Create a base asset and link related assets to it (e.g., Computer connected to Mouse, Keyboard, etc.)
                            </p>
                        </div>

                        <!-- Base Asset -->
                        <div class="mb-8 rounded border-2 border-indigo-200 bg-indigo-50 p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">
                                Base Asset
                            </h3>
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <SearchableSelect
                                        v-model="form.base_asset.room_id"
                                        :options="rooms"
                                        label="Room"
                                        placeholder="Select room..."
                                        search-placeholder="Search rooms..."
                                    />
                                    <InputError class="mt-2" :message="form.errors['base_asset.room_id']" />
                                </div>

                                <div>
                                    <SearchableSelect
                                        v-model="form.base_asset.sub_category_id"
                                        :options="subCategories"
                                        label="Subcategory"
                                        placeholder="Select subcategory..."
                                        search-placeholder="Search by subcategory or category name..."
                                    />
                                    <InputError class="mt-2" :message="form.errors['base_asset.sub_category_id']" />
                                </div>
                            </div>

                            <div class="mt-6">
                                <InputLabel for="base_note" value="Note" />
                                <textarea
                                    id="base_note"
                                    v-model="form.base_asset.note"
                                    rows="3"
                                    class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Additional notes"
                                />
                                <InputError class="mt-2" :message="form.errors['base_asset.note']" />
                            </div>
                        </div>

                        <!-- Peered Assets -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Connected Assets
                                </h3>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    :disabled="!canAddMorePeeredAsset"
                                    @click="addPeeredAsset"
                                >
                                    Add Connected Asset
                                </button>
                            </div>

                            <div v-if="form.peered_assets.length === 0" class="mt-4 rounded border border-gray-200 bg-gray-50 p-4 text-center text-gray-500">
                                No connected assets yet. Click "Add Connected Asset" to add related assets.
                            </div>

                            <div v-else class="mt-4 space-y-4">
                                <div
                                    v-for="(peeredAsset, index) in form.peered_assets"
                                    :key="index"
                                    class="rounded border border-gray-200 bg-white p-4"
                                >
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="font-medium text-gray-700">
                                            Connected Asset #{{ index + 1 }}
                                        </h4>
                                        <button
                                            type="button"
                                            class="text-xs text-red-600 hover:text-red-700"
                                            @click="removePeeredAsset(index)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <InputLabel :for="`peered-room-${index}`" value="Room" />
                                            <TextInput
                                                :id="`peered-room-${index}`"
                                                :value="getRoomLabel(form.base_asset.room_id) || 'Please select Base Asset room first'"
                                                disabled
                                                class="mt-1 block w-full bg-gray-100"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">
                                                Room is automatically set from Base Asset
                                            </p>
                                            <InputError class="mt-2" :message="form.errors[`peered_assets.${index}.room_id`]" />
                                        </div>
                                        <div>
                                            <SearchableSelect
                                                v-model="peeredAsset.sub_category_id"
                                                :options="subCategories"
                                                label="Subcategory"
                                                placeholder="Select subcategory..."
                                                search-placeholder="Search by subcategory or category name..."
                                            />
                                            <InputError class="mt-2" :message="form.errors[`peered_assets.${index}.sub_category_id`]" />
                                        </div>
                                    </div>

                                    <!-- Asset Information for each peered asset -->
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-sm font-semibold text-gray-700">
                                                Asset Information
                                            </h5>
                                            <button
                                                type="button"
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                                                :disabled="!canAddMorePeeredInfo(index)"
                                                @click="addPeeredInfoRow(index)"
                                            >
                                                Add Info Row
                                            </button>
                                        </div>

                                        <div class="mt-3 space-y-3">
                                            <div
                                                v-for="(info, infoIndex) in peeredAsset.infos"
                                                :key="infoIndex"
                                                class="rounded border border-gray-200 bg-gray-50 p-3"
                                            >
                                                <div class="grid gap-3 sm:grid-cols-3">
                                                    <div>
                                                        <InputLabel
                                                            :for="`peered-${index}-info-key-${infoIndex}`"
                                                            value="Key"
                                                        />
                                                        <TextInput
                                                            :id="`peered-${index}-info-key-${infoIndex}`"
                                                            v-model="info.key"
                                                            class="mt-1 block w-full"
                                                            placeholder="e.g. ip"
                                                        />
                                                    </div>
                                                    <div>
                                                        <InputLabel
                                                            :for="`peered-${index}-info-value-${infoIndex}`"
                                                            value="Value"
                                                        />
                                                        <TextInput
                                                            :id="`peered-${index}-info-value-${infoIndex}`"
                                                            v-model="info.value"
                                                            class="mt-1 block w-full"
                                                            placeholder="e.g. 192.168.1.10"
                                                        />
                                                    </div>
                                                    <div>
                                                        <InputLabel
                                                            :for="`peered-${index}-info-image-${infoIndex}`"
                                                            value="Image (optional)"
                                                        />
                                                        <div v-if="getImagePreview(info.image)" class="mb-2">
                                                            <img
                                                                :src="getImagePreview(info.image)"
                                                                alt="Preview"
                                                                class="h-20 w-20 rounded border border-gray-300 object-cover"
                                                            />
                                                        </div>
                                                        <input
                                                            :id="`peered-${index}-info-image-${infoIndex}`"
                                                            type="file"
                                                            accept="image/*"
                                                            class="mt-1 block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                            @input="info.image = $event.target.files[0]"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="mt-2 flex justify-end">
                                                    <button
                                                        type="button"
                                                        class="text-xs text-red-600 hover:text-red-700"
                                                        @click="removePeeredInfoRow(index, infoIndex)"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Room Assets Summary -->
                        <div v-if="form.base_asset.room_id" class="mb-8 rounded border border-gray-200 bg-white p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">
                                Room Assets Summary
                            </h3>
                            <p class="mb-4 text-sm text-gray-600">
                                Current assets in <span class="font-medium">{{ getRoomLabel(form.base_asset.room_id) }}</span>
                            </p>
                            
                            <div v-if="props.roomAssetsSummary.length === 0" class="rounded border border-gray-200 bg-gray-50 p-4 text-center text-gray-500">
                                No assets found in this room.
                            </div>
                            
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-gray-700">
                                                Category
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-gray-700">
                                                Subcategory
                                            </th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-700">
                                                Asset Records
                                            </th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-700">
                                                Total Count
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr
                                            v-for="summary in props.roomAssetsSummary"
                                            :key="summary.subcategory_id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ summary.category_name }}
                                            </td>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ summary.subcategory_name }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-600">
                                                {{ summary.asset_count }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-semibold text-indigo-600">
                                                {{ summary.total_count }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <PrimaryButton
                            v-if="can('asset-create')"
                            :disabled="form.processing"
                        >
                            {{ activeTab === 'individual' ? 'Save Asset' : 'Save Asset Series' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
