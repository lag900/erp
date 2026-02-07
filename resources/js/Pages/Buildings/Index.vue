<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    buildings: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredBuildings = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.buildings;
    }

    return props.buildings.filter((building) => {
        return (
            building.name.toLowerCase().includes(term) ||
            (building.code || '').toLowerCase().includes(term) ||
            (building.location || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteBuilding = (buildingId) => {
    deleteForm.delete(route('buildings.destroy', buildingId));
};
</script>

<template>
    <Head title="Buildings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Buildings
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage university buildings and physical infrastructure.
                    </p>
                </div>
                <Link
                    v-if="can('building-create')"
                    :href="route('buildings.create')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Add Building
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="w-full">
                <!-- Search -->
                <div class="mb-6">
                    <div class="relative max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            placeholder="Search buildings..."
                        />
                    </div>
                </div>

                <!-- Buildings Grid -->
                <div
                    v-if="filteredBuildings.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center"
                >
                    <div class="rounded-full bg-white p-3 shadow-sm">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No buildings found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by adding a new building.
                    </p>
                    <div class="mt-6">
                        <Link
                            v-if="can('building-create')"
                            :href="route('buildings.create')"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Add Building
                        </Link>
                    </div>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                    <div
                        v-for="building in filteredBuildings"
                        :key="building.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                    >
                        <!-- Image Area -->
                        <div class="relative h-56 w-full overflow-hidden">
                            <EntityImage
                                :src="building.image_url"
                                :alt="building.name"
                                type="building"
                                class="h-full w-full"
                                image-class="transition-transform duration-700 group-hover:scale-110"
                            />
                            <!-- Badge/Code overlay -->
                            <div class="absolute top-4 right-4 transition-all duration-300">
                                <span class="inline-flex items-center rounded-lg bg-white/90 px-3 py-1.5 text-xs font-bold text-gray-900 shadow-sm backdrop-blur-md ring-1 ring-gray-900/10">
                                    {{ building.code || 'NO-CODE' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content Area -->
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-4">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-xl font-bold tracking-tight text-gray-900 group-hover:text-primary transition-colors">
                                        {{ building.name }}
                                    </h3>
                                </div>
                                <p class="mt-2 flex items-center text-sm font-medium text-gray-500">
                                     <svg class="mr-2 h-4 w-4 text-primary/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ building.location || 'Unassigned Location' }}
                                </p>
                            </div>

                            <div class="mt-auto flex items-center justify-between border-t border-gray-50 pt-5">
                                <div class="flex gap-1">
                                     <span class="inline-flex h-2 w-2 rounded-full bg-green-500 ring-4 ring-green-50"></span>
                                     <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider pl-1">Operational</span>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <Link
                                        v-if="can('building-edit')"
                                        :href="route('buildings.edit', building.id)"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-50 hover:text-primary transition-all shadow-sm ring-1 ring-gray-200"
                                        title="Edit Building"
                                    >
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        v-if="can('building-delete')"
                                        type="button"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-all shadow-sm ring-1 ring-gray-200"
                                        :disabled="deleteForm.processing"
                                        @click="() => { if(confirm('Are you sure you want to delete this building?')) deleteBuilding(building.id) }"
                                        title="Delete Building"
                                    >
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
