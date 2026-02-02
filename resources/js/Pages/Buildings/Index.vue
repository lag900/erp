<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Buildings
                </h2>
                <Link
                    v-if="can('building-create')"
                    :href="route('buildings.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Building
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
                        placeholder="Search buildings..."
                    />
                </div>

                <div
                    v-if="filteredBuildings.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No buildings found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Code</th>
                                <th class="px-4 py-3 font-medium">Location</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="building in filteredBuildings"
                                :key="building.id"
                            >
                                <td class="px-4 py-3 text-gray-800">
                                    {{ building.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ building.code || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ building.location || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('building-edit')"
                                            :href="route('buildings.edit', building.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('building-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteBuilding(building.id)"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
