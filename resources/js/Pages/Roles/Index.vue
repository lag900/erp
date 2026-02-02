<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const deleteForm = useForm({});
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredRoles = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.roles;
    }

    return props.roles.filter((role) =>
        role.name.toLowerCase().includes(term)
    );
});

const deleteRole = (roleId) => {
    deleteForm.delete(route('roles.destroy', roleId));
};
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Roles
                </h2>
                <Link
                    v-if="can('role-create')"
                    :href="route('roles.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Role
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Search roles..."
                    />
                </div>

                <div v-if="filteredRoles.length === 0" class="rounded bg-white p-6 shadow">
                    <p class="text-gray-600">No roles found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 font-medium">Permissions</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="role in filteredRoles" :key="role.id">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ role.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ role.permissions_count }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('role-edit')"
                                            :href="route('roles.edit', role.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('role-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteRole(role.id)"
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
