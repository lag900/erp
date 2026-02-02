<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    permissions: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const deleteForm = useForm({});
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredPermissions = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.permissions;
    }

    return props.permissions.filter((permission) =>
        permission.name.toLowerCase().includes(term)
    );
});

const deletePermission = (permissionId) => {
    deleteForm.delete(route('permissions.destroy', permissionId));
};
</script>

<template>
    <Head title="Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Permissions
                </h2>
                <Link
                    v-if="can('permission-create')"
                    :href="route('permissions.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Permission
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
                        placeholder="Search permissions..."
                    />
                </div>

                <div v-if="filteredPermissions.length === 0" class="rounded bg-white p-6 shadow">
                    <p class="text-gray-600">No permissions found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Permission</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="permission in filteredPermissions"
                                :key="permission.id"
                            >
                                <td class="px-4 py-3 text-gray-800">
                                    {{ permission.name }}
                                </td>
                                <td class="px-4 py-3">
                                    <DangerButton
                                        v-if="can('permission-delete')"
                                        type="button"
                                        class="text-xs"
                                        :disabled="deleteForm.processing"
                                        @click="deletePermission(permission.id)"
                                    >
                                        Delete
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
