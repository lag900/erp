<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    building: Object,
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
</script>

<template>
    <Head :title="building.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('buildings.index')" class="btn-secondary w-10 p-0 justify-center">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-gray-900 leading-tight">
                        {{ building.name }}
                    </h2>
                    <p class="text-sm font-medium text-gray-500 mt-1">
                        Building Code: <span class="font-mono text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded">{{ building.code || 'N/A' }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Building Banner -->
                <div class="relative h-64 w-full overflow-hidden rounded-3xl shadow-premium border border-gray-100 bg-gray-50">
                    <EntityImage
                        :src="building.image_url"
                        :alt="building.name"
                        type="building"
                        class="h-full w-full object-cover"
                    />
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-8">
                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest text-primary border border-gray-100 shadow-sm">
                            University Building
                        </span>
                    </div>
                </div>

                <!-- Rooms / Details placeholder -->
                <div>
                     <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black uppercase tracking-wide text-gray-900">Rooms & Levels</h3>
                        <Link 
                            v-if="can('room-create')"
                            :href="route('rooms.create', { building_id: building.id })" 
                            class="btn-primary h-8 px-4 text-xs"
                        >
                            + Add Room
                        </Link>
                    </div>

                    <div v-if="building.levels && building.levels.length > 0" class="space-y-6">
                        <div v-for="level in building.levels" :key="level.id" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <h4 class="text-md font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="h-5 w-5 text-[#3d4adb]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ level.name }}
                            </h4>
                            
                            <div v-if="level.rooms && level.rooms.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <Link 
                                    v-for="room in level.rooms" 
                                    :key="room.id" 
                                    :href="route('rooms.show', room.id)"
                                    class="group relative flex items-center gap-3 p-3 rounded-xl border border-dashed border-gray-200 hover:border-[#3d4adb]/30 hover:bg-[#3d4adb]/5 transition-all"
                                >
                                    <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-[#3d4adb] transition-colors">
                                         <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                         <p class="text-sm font-bold text-gray-700 group-hover:text-[#3d4adb]">{{ room.name }}</p>
                                         <p class="text-xs font-mono text-gray-400">{{ room.code || 'No Code' }}</p>
                                    </div>
                                </Link>
                            </div>
                            <p v-else class="text-sm text-gray-400 italic py-2">No rooms registered on this level.</p>
                        </div>
                    </div>
                    
                    <div v-else class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center bg-gray-50/50">
                        <div class="mx-auto h-12 w-12 text-gray-300 mb-3">
                            <svg fill="none" class="h-10 w-10 text-gray-300" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">
                            No levels or rooms have been registered for this building yet.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
