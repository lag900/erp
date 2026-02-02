<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const deleteForm = useForm({});
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.users;
    }

    return props.users.filter((user) => {
        return (
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term) ||
            user.roles.join(' ').toLowerCase().includes(term) ||
            user.departments.join(' ').toLowerCase().includes(term)
        );
    });
});

const deleteUser = (userId) => {
    deleteForm.delete(route('users.destroy', userId));
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Users
                </h2>
                <Link
                    v-if="can('user-create')"
                    :href="route('users.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add User
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
                        placeholder="Search users..."
                    />
                </div>

                <div v-if="filteredUsers.length === 0" class="rounded bg-white p-6 shadow">
                    <p class="text-gray-600">No users found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Roles</th>
                                <th class="px-4 py-3 font-medium">Departments</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in filteredUsers" :key="user.id">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ user.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ user.roles.join(', ') || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ user.departments.join(', ') || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('user-edit')"
                                            :href="route('users.edit', user.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('user-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteUser(user.id)"
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
