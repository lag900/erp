<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    room: Object,
    inventory: Array, // [{id,name,image,total, sub_categories: [{id,name,total}]}]
    stats: Object,
});
</script>

<template>
    <Head :title="room.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('buildings.show', { building: room.building_id || 1 }) /* Ideally navigate back to specific building */" class="btn-secondary w-10 p-0 justify-center">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                     <div class="flex items-center gap-2">
                         <h2 class="text-2xl font-black uppercase tracking-tight text-gray-900 leading-tight">
                            {{ room.name }}
                        </h2>
                        <span v-if="room.code" class="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600 border border-gray-200">
                            {{ room.code }}
                        </span>
                     </div>
                    <p class="text-sm font-medium text-gray-500 mt-1 flex items-center gap-2">
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            {{ room.building }}
                        </span>
                        <span class="text-gray-300">/</span>
                        <span class="flex items-center gap-1">
                             <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            {{ room.level }}
                        </span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Assets</p>
                            <p class="text-2xl font-black text-gray-900">{{ stats.total_assets }}</p>
                        </div>
                    </div>
                    <!-- Add more stats if needed -->
                </div>

                <!-- Inventory Breakdown -->
                <div class="space-y-6">
                    <h3 class="text-lg font-black uppercase tracking-wide text-gray-900 flex items-center gap-2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        Room Inventory
                    </h3>

                    <div v-if="inventory.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center bg-gray-50/50">
                        <div class="mx-auto h-12 w-12 text-gray-300 mb-3">
                             <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">
                            No assets registered in this room (visible to your department).
                        </p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-8">
                        <div v-for="category in inventory" :key="category.id" class="animate-fade-in-up">
                            <!-- Category Header -->
                            <div class="flex items-center gap-4 mb-4">
                                <div class="h-10 w-10 overflow-hidden rounded-lg bg-gray-100 flex-shrink-0 border border-gray-200">
                                    <EntityImage :src="category.image_url" :alt="category.name" class="h-full w-full object-cover" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">{{ category.name }}</h4>
                                    <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                        {{ category.total }} item{{ category.total !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Sub Categories Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="sub in category.sub_categories" :key="sub.id" class="group relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all hover:border-primary/20">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3">
                                             <div class="h-10 w-10 overflow-hidden rounded-lg bg-gray-50 flex-shrink-0 border border-gray-100">
                                                <EntityImage :src="sub.image_url" :alt="sub.name" class="h-full w-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" />
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-bold text-gray-800 group-hover:text-primary transition-colors">{{ sub.name }}</h5>
                                                <p class="text-xs text-gray-500">{{ sub.total }} assets</p>
                                            </div>
                                        </div>
                                        <!-- Optional: Action button or link to filtered asset list -->
                                        <!-- <Link :href="route('assets.index', { room_id: room.id, sub_category_id: sub.id })" class="text-gray-300 hover:text-primary">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </Link> -->
                                    </div>
                                    
                                    <!-- Progress bar visual -->
                                    <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-primary h-1.5 rounded-full" :style="{ width: Math.min((sub.total / category.total) * 100, 100) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
