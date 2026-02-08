<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
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
    name: '',
    email: '',
    password: '',
    role_id: '',
    department_ids: [],
    default_department_id: '',
});

// Reactivity is handled by Checkbox v-model:checked binding to form.department_ids array

const availableDefaultDepartments = computed(() => {
    return props.departments.filter((department) =>
        form.department_ids.includes(department.id)
    );
});
</script>

<template>
    <Head title="Add User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Add User
                </h2>
                <Link
                    :href="route('users.index')"
                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Back to Users
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="form.post(route('users.store'), {
                        onSuccess: () => window.showToast('success', 'User created successfully')
                    })"
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                placeholder="User Name"
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
                                placeholder="user@example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                placeholder="******"
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
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">
                                Department Access
                            </h3>
                            <span class="text-xs font-medium text-gray-400">
                                {{ form.department_ids.length }} Selected
                            </span>
                        </div>
                        
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label
                                v-for="department in departments"
                                :key="department.id"
                                :class="[
                                    'flex cursor-pointer items-center justify-between rounded-xl border-2 p-4 transition-all duration-200',
                                    form.department_ids.includes(department.id)
                                        ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                        : 'border-gray-100 bg-white hover:border-gray-200 hover:bg-gray-50'
                                ]"
                            >
                                <div class="flex items-center space-x-3">
                                    <div 
                                        :class="[
                                            'flex h-10 w-10 items-center justify-center rounded-lg text-lg font-bold transition-colors',
                                            form.department_ids.includes(department.id) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400'
                                        ]"
                                    >
                                        {{ department.name.charAt(0) }}
                                    </div>
                                    <span class="font-semibold text-gray-700">{{ department.name }}</span>
                                </div>
                                
                                <GlobalCheckbox
                                    :value="department.id"
                                    v-model:checked="form.department_ids"
                                />
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
                        <PrimaryButton v-if="can('user-create')" :disabled="form.processing">
                            Save User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
