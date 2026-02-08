<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    location: Object,
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
</script>

<template>
    <Head :title="location.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('locations.index')" class="btn-secondary w-10 p-0 justify-center">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-gray-900 leading-tight">
                        {{ location.name }}
                    </h2>
                    <p class="text-sm font-arabic font-bold text-gray-500 mt-1">
                        {{ location.arabic_name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Location Banner -->
                <div class="relative h-64 w-full overflow-hidden rounded-3xl shadow-premium border border-gray-100 bg-gray-50">
                    <EntityImage
                        :src="location.image_url"
                        :alt="location.name"
                        type="location"
                        class="h-full w-full object-cover"
                    />
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-8">
                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest text-primary border border-gray-100 shadow-sm">
                            University Campus
                        </span>
                        <p class="mt-2 text-white/90 text-sm max-w-2xl leading-relaxed font-medium">
                            {{ location.description || 'No description provided.' }}
                        </p>
                    </div>
                </div>

                <!-- Buildings Grid -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black uppercase tracking-wide text-gray-900">Associated Buildings</h3>
                        <Link 
                            v-if="can('building-create')"
                            :href="route('buildings.create', { location_id: location.id })" 
                            class="btn-primary h-8 px-4 text-xs"
                        >
                            + Add Building
                        </Link>
                    </div>

                    <div v-if="!location.buildings || location.buildings.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center bg-gray-50/50">
                        <div class="mx-auto h-12 w-12 text-gray-300 mb-3">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">No buildings assigned to this location yet.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="building in location.buildings" :key="building.id" class="rounded-2xl bg-white p-6 shadow-soft border border-gray-100 hover:border-primary/30 transition-all group">
                            <div class="flex items-start justify-between">
                                <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-black uppercase px-2 py-1 rounded">
                                    {{ building.rooms_count }} Rooms
                                </span>
                            </div>
                            <h4 class="mt-4 text-sm font-black text-gray-900 uppercase tracking-tight">{{ building.name }}</h4>
                            <p class="text-xs text-gray-400 font-mono mt-1">{{ building.code || 'NO-CODE' }}</p>
                            
                            <div class="mt-6 pt-4 border-t border-gray-50 flex justify-end">
                                <Link :href="route('buildings.show', building.id)" class="btn-secondary h-8 px-3 text-xs w-full justify-between">
                                    View Details <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap');

.font-arabic {
    font-family: 'Cairo', sans-serif;
}
</style>
