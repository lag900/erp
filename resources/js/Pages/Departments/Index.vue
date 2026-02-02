<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    departments: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
const deleteForm = useForm({});

const filteredDepartments = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.departments;
    }

    return props.departments.filter((department) => {
        return (
            department.name.toLowerCase().includes(term) ||
            (department.code || '').toLowerCase().includes(term) ||
            (department.description || '').toLowerCase().includes(term)
        );
    });
});

const deleteDepartment = (departmentId) => {
    deleteForm.delete(route('departments.destroy', departmentId));
};
</script>

<template>
    <Head title="Departments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Departments
                </h2>
                <Link
                    v-if="can('department-create')"
                    :href="route('departments.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Department
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
                        placeholder="Search departments..."
                    />
                </div>

                <div v-if="filteredDepartments.length === 0" class="rounded bg-white p-6 shadow">
                    <p class="text-gray-600">No departments found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Code</th>
                                <th class="px-4 py-3 font-medium">Description</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="department in filteredDepartments" :key="department.id">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ department.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ department.code || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ department.description || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('department-assign-users')"
                                            :href="route('departments.members', department.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Members
                                        </Link>
                                        <Link
                                            v-if="can('department-edit')"
                                            :href="route('departments.edit', department.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('department-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteDepartment(department.id)"
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
