<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredLocations = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.locations;
    }

    return props.locations.filter((location) => {
        return (
            location.name.toLowerCase().includes(term) ||
            (location.description || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteLocation = (locationId) => {
    deleteForm.delete(route('locations.destroy', locationId));
};
</script>

<template>
    <Head title="Locations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Locations
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage university locations, zones, and main areas.
                    </p>
                </div>
                <Link
                    v-if="can('location-create')"
                    :href="route('locations.create')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Location
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
                            placeholder="Search locations..."
                        />
                    </div>
                </div>

                <!-- Locations Grid -->
                <div
                    v-if="filteredLocations.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center"
                >
                    <div class="rounded-full bg-white p-3 shadow-sm">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No locations found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating a new campus or site.
                    </p>
                    <div class="mt-6">
                         <Link
                            v-if="can('location-create')"
                            :href="route('locations.create')"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Add Location
                        </Link>
                    </div>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                    <div
                        v-for="location in filteredLocations"
                        :key="location.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                    >
                         <!-- Image Area -->
                        <div class="relative h-56 w-full overflow-hidden">
                            <EntityImage
                                :src="location.image_url"
                                :alt="location.name"
                                type="location"
                                class="h-full w-full"
                                image-class="transition-transform duration-700 group-hover:scale-110"
                            />
                        </div>
                        
                        <div class="flex flex-1 flex-col justify-between p-6">
                            <div>
                                <h3 class="text-xl font-bold tracking-tight text-gray-900 group-hover:text-primary transition-colors">
                                    {{ location.name }}
                                </h3>
                                <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-gray-500">
                                    {{ location.description || 'No description provided.' }}
                                </p>
                            </div>

                             <div class="mt-6 flex items-center justify-between border-t border-gray-50 pt-5">
                                <div class="flex items-center gap-2">
                                     <span class="inline-flex h-2 w-2 rounded-full bg-primary/40 ring-4 ring-primary/5"></span>
                                     <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider pl-1 font-sans">University Location</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Link
                                        v-if="can('location-edit')"
                                        :href="route('locations.edit', location.id)"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-50 hover:text-primary transition-all shadow-sm ring-1 ring-gray-200"
                                        title="Edit Location"
                                    >
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        v-if="can('location-delete')"
                                        type="button"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-all shadow-sm ring-1 ring-gray-200"
                                        :disabled="deleteForm.processing"
                                        @click="() => { if(confirm('Are you sure you want to delete this location?')) deleteLocation(location.id) }"
                                        title="Delete Location"
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
