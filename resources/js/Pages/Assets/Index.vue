<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    assets: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredAssets = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.assets;
    }

    return props.assets.filter((asset) => {
        const roomParts = [
            asset.room?.location,
            asset.room?.building,
            asset.room?.level,
            asset.room?.name,
            asset.room?.code,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const categoryText = [asset.category, asset.subCategory]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const noteText = asset.note ? asset.note.toLowerCase() : '';
        const serialText = asset.serial_number ? asset.serial_number.toLowerCase() : '';
        const ownerText = asset.owner_department ? asset.owner_department.toLowerCase() : '';

        return (
            roomParts.includes(term) ||
            categoryText.includes(term) ||
            noteText.includes(term) ||
            serialText.includes(term) ||
            ownerText.includes(term)
        );
    });
});

const getStatusBadgeClass = (condition) => {
    switch (condition) {
        case 'active':
            return 'bg-green-100 text-green-700 border-green-200';
        case 'maintenance':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'damaged':
            return 'bg-red-100 text-red-700 border-red-200';
        case 'disposed':
            return 'bg-gray-100 text-gray-700 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const getStatusLabel = (condition) => {
    switch (condition) {
        case 'active':
            return 'Active';
        case 'maintenance':
            return 'Maintenance';
        case 'damaged':
            return 'Damaged';
        case 'disposed':
            return 'Disposed';
        default:
            return condition || 'Unknown';
    }
};
</script>

<template>
    <Head title="Assets Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Asset Inventory
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Manage and track university equipment across all departments.</p>
                </div>
                <Link
                    v-if="can('asset-create')"
                    :href="route('assets.create')"
                    class="inline-flex items-center rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-soft transition-all hover:bg-primary-hover hover:scale-[1.02] active:scale-[0.98]"
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Register Asset
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="w-full">
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative w-full max-w-2xl">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full border-gray-200 pl-11 focus:border-primary focus:ring-primary h-12 rounded-2xl shadow-soft transition-all"
                            placeholder="Search by serial, room, category, or department..."
                        />
                    </div>
                </div>

                <div
                    v-if="filteredAssets.length === 0"
                    class="rounded-2xl border border-dashed border-gray-300 bg-white p-20 text-center shadow-soft"
                >
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">No assets found</h3>
                    <p class="mt-1 text-gray-500">Try adjusting your search filters or add a new asset.</p>
                </div>

                <div v-else class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-premium">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-widest text-gray-500">
                                <th class="px-6 py-5">Asset Identification</th>
                                <th class="px-6 py-5">Placement</th>
                                <th class="px-6 py-5">Classification</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="asset in filteredAssets" :key="asset.id" class="group transition-all hover:bg-gray-50/50">
                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                         <div class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm transition-transform group-hover:scale-110">
                                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                                         </div>
                                         <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ asset.serial_number || 'N/A' }}</span>
                                            <div class="flex items-center mt-1">
                                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-tighter">{{ asset.owner_department }}</span>
                                                <span v-if="asset.is_shared" class="ml-2 flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-black text-blue-600 uppercase tracking-tighter ring-1 ring-inset ring-blue-100">
                                                    Shared
                                                </span>
                                            </div>
                                         </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ asset.room?.name || 'Central Hall' }}</span>
                                        <span class="text-xs text-gray-500">{{ [asset.room?.location, asset.room?.building].filter(Boolean).join(' • ') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                     <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{ asset.subCategory }}</span>
                                        <span class="text-[11px] font-semibold text-gray-400 uppercase">{{ asset.category }}</span>
                                     </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold transition-all', getStatusBadgeClass(asset.condition)]">
                                        {{ getStatusLabel(asset.condition) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <Link
                                        v-if="can('asset-list')"
                                        :href="route('assets.show', asset.id)"
                                        class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-xs font-black text-gray-700 shadow-soft ring-1 ring-gray-200 transition-all hover:bg-gray-50 hover:text-primary hover:ring-primary/30"
                                    >
                                        View Data
                                        <svg class="ml-2 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
