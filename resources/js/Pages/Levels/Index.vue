<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    levels: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredLevels = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.levels;
    }

    return props.levels.filter((level) => {
        return (
            level.name.toLowerCase().includes(term) ||
            String(level.level_number ?? '').includes(term) ||
            (level.building || '').toLowerCase().includes(term) ||
            (level.location || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteLevel = (levelId) => {
    deleteForm.delete(route('levels.destroy', levelId));
};
</script>

<template>
    <Head title="Levels" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Levels
                </h2>
                <Link
                    v-if="can('level-create')"
                    :href="route('levels.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Level
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
                        placeholder="Search levels..."
                    />
                </div>

                <div
                    v-if="filteredLevels.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No levels found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Number</th>
                                <th class="px-4 py-3 font-medium">Building</th>
                                <th class="px-4 py-3 font-medium">Location</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="level in filteredLevels" :key="level.id">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ level.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ level.level_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ level.building || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ level.location || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('level-edit')"
                                            :href="route('levels.edit', level.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('level-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteLevel(level.id)"
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
