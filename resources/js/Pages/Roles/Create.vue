<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    permissions: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    name: '',
    permission_ids: [],
});

const filteredPermissions = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.permissions;
    }

    return props.permissions.filter((permission) =>
        permission.name.toLowerCase().includes(term)
    );
});

const togglePermission = (permissionId) => {
    const existing = form.permission_ids.includes(permissionId);
    if (existing) {
        form.permission_ids = form.permission_ids.filter((id) => id !== permissionId);
        return;
    }

    form.permission_ids = [...form.permission_ids, permissionId];
};
</script>

<template>
    <Head title="Add Role" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Add Role
                </h2>
                <Link
                    :href="route('roles.index')"
                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Back to Roles
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="form.post(route('roles.store'))"
                >
                    <div>
                        <InputLabel for="name" value="Role Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            placeholder="DepartmentManager"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Permissions
                            </h3>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="mt-3 block w-full"
                            placeholder="Search permissions..."
                        />

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="permission in filteredPermissions"
                                :key="permission.id"
                                class="flex items-center gap-2 rounded border border-gray-200 p-3 text-sm text-gray-700"
                            >
                                <input
                                    type="checkbox"
                                    :value="permission.id"
                                    :checked="form.permission_ids.includes(permission.id)"
                                    @change="togglePermission(permission.id)"
                                />
                                {{ permission.name }}
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.permission_ids" />
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton v-if="can('role-create')" :disabled="form.processing">
                            Save Role
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
