<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    role_id: props.user.role_id || '',
    department_ids: props.user.department_ids || [],
    default_department_id: props.user.default_department_id || '',
});

const toggleDepartment = (departmentId) => {
    const existing = form.department_ids.includes(departmentId);
    if (existing) {
        form.department_ids = form.department_ids.filter((id) => id !== departmentId);
        if (form.default_department_id === departmentId) {
            form.default_department_id = '';
        }
        return;
    }

    form.department_ids = [...form.department_ids, departmentId];
};

const availableDefaultDepartments = computed(() => {
    return props.departments.filter((department) =>
        form.department_ids.includes(department.id)
    );
});
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit User
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('users.index')"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to Users
                    </Link>
                    <DangerButton
                        v-if="can('user-delete')"
                        type="button"
                        @click="$inertia.delete(route('users.destroy', user.id))"
                    >
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="form.put(route('users.update', user.id))"
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="password" value="New Password" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                placeholder="Leave blank to keep"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="role_id" value="Role" />
                            <select
                                id="role_id"
                                v-model="form.role_id"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role_id" />
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Departments Access
                        </h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label
                                v-for="department in departments"
                                :key="department.id"
                                class="flex items-center gap-2 rounded border border-gray-200 p-3 text-sm text-gray-700"
                            >
                                <input
                                    type="checkbox"
                                    :value="department.id"
                                    :checked="form.department_ids.includes(department.id)"
                                    @change="toggleDepartment(department.id)"
                                />
                                {{ department.name }}
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.department_ids" />
                    </div>

                    <div class="mt-6">
                        <InputLabel
                            for="default_department_id"
                            value="Default Department"
                        />
                        <select
                            id="default_department_id"
                            v-model="form.default_department_id"
                            class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Auto select</option>
                            <option
                                v-for="department in availableDefaultDepartments"
                                :key="department.id"
                                :value="department.id"
                            >
                                {{ department.name }}
                            </option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.default_department_id"
                        />
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton v-if="can('user-edit')" :disabled="form.processing">
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
