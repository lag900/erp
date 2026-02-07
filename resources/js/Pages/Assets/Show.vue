<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    roomAssetsSummary: {
        type: Array,
        default: () => [],
    },
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
            return 'Active / Good';
        case 'maintenance':
            return 'In Maintenance';
        case 'damaged':
            return 'Damaged';
        case 'disposed':
            return 'Disposed';
        default:
            return condition || 'Unknown';
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Asset Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('assets.index')"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:bg-gray-50 hover:text-primary active:scale-95 shadow-soft"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold leading-tight text-gray-800">
                            Asset #{{ asset.id }}
                        </h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-black uppercase tracking-tighter shadow-sm', getStatusBadgeClass(asset.condition)]">
                                {{ getStatusLabel(asset.condition) }}
                            </span>
                            <span v-if="asset.is_shared" class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2.5 py-0.5 text-xs font-black uppercase tracking-tighter text-blue-600 shadow-sm">
                                SHARED RESOURCE
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link
                        v-if="can('asset-edit')"
                        :href="route('assets.edit', asset.id)"
                        class="inline-flex items-center rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-soft transition-all hover:bg-primary-hover hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit Asset Data
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    
                    <!-- Left Column: Core Info -->
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Primary Statistics/ID -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-premium">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Serial Number</span>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-lg font-black text-gray-900 line-clamp-1">{{ asset.serial_number || 'UNASSIGNED' }}</span>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-premium">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Classification</span>
                                <div class="mt-2 text-lg font-black text-primary line-clamp-1">{{ asset.subCategory }}</div>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-premium">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Quantity</span>
                                <div class="mt-2 text-3xl font-black text-gray-900">{{ asset.count || 1 }}</div>
                            </div>
                        </div>

                        <!-- Technical Specs -->
                        <div class="rounded-2xl border border-gray-100 bg-white shadow-premium overflow-hidden">
                            <div class="border-b border-gray-50 bg-gray-50/50 px-6 py-4">
                                <h3 class="text-sm font-black uppercase tracking-widest text-gray-900">Technical Specifications</h3>
                            </div>
                            <div v-if="asset.infos.length === 0" class="p-10 text-center">
                                <p class="text-sm text-gray-400 italic">No technical specifications provided for this asset.</p>
                            </div>
                            <div v-else class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="info in asset.infos" :key="info.id" class="flex flex-col rounded-xl border border-gray-100 p-4 transition-all hover:border-primary/20 hover:bg-primary/5 group">
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 group-hover:text-primary/70 transition-colors">{{ info.key }}</span>
                                        <span class="mt-1 text-base font-bold text-gray-900">{{ info.value || '-' }}</span>
                                        <div v-if="info.image_url" class="mt-4 overflow-hidden rounded-lg shadow-soft border border-gray-100">
                                            <img :src="info.image_url" class="h-40 w-full object-cover transition-transform group-hover:scale-105" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Movement Timeline -->
                        <div class="rounded-2xl border border-gray-100 bg-white shadow-premium overflow-hidden">
                            <div class="border-b border-gray-50 bg-gray-50/50 px-6 py-4">
                                <h3 class="text-sm font-black uppercase tracking-widest text-gray-900">Audit Trail & Movements</h3>
                            </div>
                            <div v-if="asset.movements.length === 0" class="p-10 text-center">
                                <p class="text-sm text-gray-400 italic">This asset is currently in its original deployment location.</p>
                            </div>
                            <div v-else class="p-8">
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        <li v-for="(movement, idx) in asset.movements" :key="movement.id">
                                            <div class="relative pb-8">
                                                <span v-if="idx !== asset.movements.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 ring-8 ring-white">
                                                            <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-0.5">
                                                        <div class="text-sm text-gray-500">
                                                            <span class="font-bold text-gray-900 uppercase text-xs">{{ movement.user?.name }}</span> 
                                                            moved this asset from 
                                                            <span class="font-bold text-primary">{{ movement.from_room?.name }}</span>
                                                            to 
                                                            <span class="font-bold text-primary">{{ movement.to_room?.name }}</span>
                                                            <span class="whitespace-nowrap ml-2 text-gray-400">{{ formatDate(movement.created_at) }}</span>
                                                        </div>
                                                        <p v-if="movement.reason" class="mt-1 text-sm italic text-gray-400">{{ movement.reason }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar Meta -->
                    <div class="space-y-8">
                        
                        <!-- Deployment Location Card -->
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-premium">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-6">Current Placement</h3>
                            <div class="space-y-4">
                                <div class="flex items-start gap-4 p-4 rounded-xl border border-gray-50 bg-gray-50/30">
                                     <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-white text-primary shadow-sm">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                     </div>
                                     <div>
                                        <p class="text-[10px] font-black uppercase tracking-tighter text-gray-400">Exact Room</p>
                                        <p class="font-bold text-gray-900">{{ asset.room?.name }}</p>
                                        <p class="text-xs text-gray-500 uppercase font-black tracking-tighter">{{ asset.room?.code }}</p>
                                     </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 rounded-xl border border-gray-50 bg-gray-50/30">
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-gray-400">Campus</p>
                                        <p class="text-sm font-bold text-gray-900">{{ asset.room?.location || 'Main' }}</p>
                                    </div>
                                    <div class="p-3 rounded-xl border border-gray-50 bg-gray-50/30">
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-gray-400">Building</p>
                                        <p class="text-sm font-bold text-gray-900 truncate" :title="asset.room?.building">{{ asset.room?.building }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ownership & Visibility -->
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-premium">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-6">Ownership & Visibility</h3>
                            <div class="space-y-6">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-tighter text-gray-400">Managing Department</p>
                                    <div class="mt-2 flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                            {{ asset.owner_department?.charAt(0) }}
                                        </div>
                                        <span class="font-bold text-gray-900">{{ asset.owner_department }}</span>
                                    </div>
                                </div>

                                <div v-if="asset.is_shared" class="pt-4 border-t border-gray-50">
                                    <p class="text-[10px] font-black uppercase tracking-tighter text-gray-400">Also Visible To</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span v-for="dept in asset.shared_departments" :key="dept" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-black uppercase tracking-tighter text-gray-600">
                                            {{ dept }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Note -->
                        <div v-if="asset.note" class="rounded-2xl border-2 border-dashed border-gray-100 bg-gray-50/30 p-6">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Internal Note</h3>
                            <p class="text-sm text-gray-600 italic leading-relaxed">{{ asset.note }}</p>
                        </div>

                        <!-- Danger Zone -->
                        <div v-if="can('asset-delete')" class="pt-6">
                             <form @submit.prevent="form.delete(route('assets.destroy', asset.id))">
                                <button
                                    type="submit"
                                    class="w-full rounded-xl border border-red-100 bg-red-50 py-3 text-xs font-black uppercase tracking-widest text-red-600 transition-all hover:bg-red-600 hover:text-white"
                                    :disabled="form.processing"
                                    onclick="return confirm('Are you sure you want to permanently delete this asset record? This action cannot be undone.')"
                                >
                                    Terminate Record
                                </button>
                             </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
