<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Locations
                </h2>
                <Link
                    v-if="can('location-create')"
                    :href="route('locations.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Location
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Search locations..."
                    />
                </div>

                <div
                    v-if="filteredLocations.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No locations found.</p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="location in filteredLocations"
                        :key="location.id"
                        class="rounded bg-white p-4 shadow"
                    >
                        <p class="text-lg font-semibold text-gray-900">
                            {{ location.name }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ location.description || 'No description.' }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                v-if="can('location-edit')"
                                :href="route('locations.edit', location.id)"
                                class="text-sm text-indigo-600 hover:text-indigo-700"
                            >
                                Edit
                            </Link>
                            <DangerButton
                                v-if="can('location-delete')"
                                type="button"
                                class="text-xs"
                                :disabled="deleteForm.processing"
                                @click="deleteLocation(location.id)"
                            >
                                Delete
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
