<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    }
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

// State
const search = ref('');
const roleFilter = ref('all');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser = ref(null);

// Forms
const form = useForm({
    name: '',
    email: '',
    password: '',
    role_id: '',
    department_ids: [],
    default_department_id: null,
});

const deleteForm = useForm({});

// Filtering
const filteredUsers = computed(() => {
    let result = props.users;
    const term = search.value.trim().toLowerCase();

    if (term) {
        result = result.filter((user) =>
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term)
        );
    }

    if (roleFilter.value !== 'all') {
        result = result.filter((user) =>
            user.roles.includes(roleFilter.value)
        );
    }

    return result;
});

// Actions
const openCreateModal = () => {
    form.clearErrors();
    form.reset();
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
    form.reset();
};

const submitCreate = () => {
    form.post(route('users.store'), {
        onSuccess: () => closeCreateModal(),
    });
};

const openEditModal = (user) => {
    selectedUser.value = user;
    form.clearErrors();
    
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role_id = user.role_id;
    form.department_ids = user.department_ids;
    form.default_department_id = user.default_department_id;
    
    isEditModalOpen.value = true;
};



const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedUser.value = null;
    form.reset();
};

const submitUpdate = () => {
    form.put(route('users.update', selectedUser.value.id), {
        onSuccess: () => closeEditModal(),
    });
};

const confirmDelete = (user) => {
    selectedUser.value = user;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedUser.value = null;
};

const submitDelete = () => {
    deleteForm.delete(route('users.destroy', selectedUser.value.id), {
        onSuccess: () => closeDeleteModal(),
    });
};

// Helpers
const getRoleBadgeClass = (role) => {
    const base = 'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset';
    switch (role.toLowerCase()) {
        case 'admin':
        case 'super admin':
            return `${base} bg-red-50 text-red-700 ring-red-600/20`;
        case 'media':
            return `${base} bg-purple-50 text-purple-700 ring-purple-600/20`;
        case 'manager':
            return `${base} bg-blue-50 text-blue-700 ring-blue-600/20`;
        default:
            return `${base} bg-gray-50 text-gray-700 ring-gray-600/20`;
    }
};
</script>

<template>
    <Head title="Users Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                        Users Console
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage organizational access, roles, and department permissions.
                    </p>
                </div>
                <PrimaryButton
                    v-if="can('user-create')"
                    @click="openCreateModal"
                    class="!scale-105"
                >
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New User
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="relative flex-1 max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/50 pl-10 text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary shadow-sm"
                            placeholder="Search by name or email..."
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Filter By Role:</span>
                        <div class="flex gap-2">
                             <button 
                                @click="roleFilter = 'all'"
                                :class="roleFilter === 'all' ? 'bg-primary text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            >
                                All
                            </button>
                            <button 
                                v-for="role in roles"
                                :key="role.id"
                                @click="roleFilter = role.name"
                                :class="roleFilter === role.name ? 'bg-primary text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            >
                                {{ role.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Grid/Table -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl shadow-gray-200/50">
                    <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center p-20 text-center">
                        <div class="mb-4 rounded-full bg-gray-50 p-6">
                            <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">No users match your criteria</h3>
                        <p class="mx-auto mt-2 max-w-sm text-sm text-gray-500">
                            We couldn't find any users matching your current search or filter. Try a different term or create a new user.
                        </p>
                        <div class="mt-8">
                             <SecondaryButton @click="search = ''; roleFilter = 'all'">
                                Clear Filters
                            </SecondaryButton>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/50">
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Profile</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Role</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Departments</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Command</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="user in filteredUsers" :key="user.id" class="group transition-all hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border-2 border-white shadow-sm transition-transform group-hover:scale-105">
                                                    <img 
                                                        v-if="user.image_url" 
                                                        :src="user.image_url" 
                                                        :alt="user.name" 
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary to-primary-light text-xl font-black text-white uppercase leading-none">
                                                        {{ user.name.charAt(0) }}
                                                    </div>
                                                </div>
                                                <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white bg-green-500 shadow-sm"></div>
                                            </div>
                                            <div>
                                                <div class="text-base font-bold text-gray-900 group-hover:text-primary transition-colors">{{ user.name }}</div>
                                                <div class="text-xs font-medium text-gray-400">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role"
                                                :class="getRoleBadgeClass(role)"
                                            >
                                                {{ role }}
                                            </span>
                                            <span v-if="user.roles.length === 0" class="text-xs italic text-gray-300">Unassigned</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            <span
                                                v-for="dept in user.departments"
                                                :key="dept"
                                                class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-tight text-gray-600 border border-gray-200"
                                            >
                                                {{ dept }}
                                            </span>
                                            <span v-if="user.departments.length === 0" class="text-xs italic text-gray-300">Universal Access</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button
                                                v-if="can('user-edit')"
                                                @click="openEditModal(user)"
                                                class="p-2 text-gray-400 hover:text-primary transition-colors hover:bg-white rounded-lg"
                                                title="Edit User"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="can('user-delete')"
                                                @click="confirmDelete(user)"
                                                class="p-2 text-gray-400 hover:text-red-500 transition-colors hover:bg-white rounded-lg"
                                                title="Delete User"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="isCreateModalOpen || isEditModalOpen" @close="isCreateModalOpen ? closeCreateModal() : closeEditModal()" max-width="2xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">{{ isCreateModalOpen ? 'Create New User' : 'Edit User Profile' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Fill in the details below to manage organization access.</p>
                    </div>
                    <button @click="isCreateModalOpen ? closeCreateModal() : closeEditModal()" class="text-gray-400 hover:text-gray-600 bg-gray-50 p-2 rounded-full">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="isCreateModalOpen ? submitCreate() : submitUpdate()" class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                         <div class="space-y-1.5">
                            <InputLabel for="name" value="Full Name" />
                            <TextInput id="name" type="text" v-model="form.name" class="w-full" placeholder="John Doe" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-1.5">
                            <InputLabel for="email" value="Email Address" />
                            <TextInput id="email" type="email" v-model="form.email" class="w-full" placeholder="john@university.edu" required />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <InputLabel for="password" :value="isCreateModalOpen ? 'Password' : 'New Password (Leave blank to keep current)'" />
                        <TextInput id="password" type="password" v-model="form.password" class="w-full" :required="isCreateModalOpen" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="border-t border-gray-50 pt-6">
                        <h4 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Access Control</h4>
                        
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <InputLabel for="role_id" value="System Role" />
                                <select 
                                    id="role_id"
                                    v-model="form.role_id"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-primary focus:ring-primary shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                                <InputError :message="form.errors.role_id" />
                            </div>

                            <div class="space-y-1.5">
                                <InputLabel value="Assigned Departments" />
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    <label v-for="dept in departments" :key="dept.id" class="flex items-center gap-3 cursor-pointer group">
                                        <input 
                                            type="checkbox" 
                                            :value="dept.id" 
                                            v-model="form.department_ids"
                                            class="rounded text-primary focus:ring-primary transition-all scale-110"
                                        />
                                        <span class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">{{ dept.name }}</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.department_ids" />
                            </div>

                             <div v-if="form.department_ids.length > 1" class="space-y-1.5">
                                <InputLabel for="default_department_id" value="Default Department" />
                                <select 
                                    id="default_department_id"
                                    v-model="form.default_department_id"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-primary focus:ring-primary shadow-sm"
                                >
                                    <option :value="null">Select default department</option>
                                    <option 
                                        v-for="deptId in form.department_ids" 
                                        :key="deptId" 
                                        :value="deptId"
                                    >
                                        {{ departments.find(d => d.id === deptId)?.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.default_department_id" />
                                <p class="text-[10px] text-gray-400">The department context loaded by default upon login.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-10">
                        <SecondaryButton @click="isCreateModalOpen ? closeCreateModal() : closeEditModal()" :disabled="form.processing">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ isCreateModalOpen ? 'Create Account' : 'Save Changes' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-6">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900">Remove Account?</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                    Are you sure you want to delete <span class="font-bold text-gray-900">{{ selectedUser?.name }}</span>? This action cannot be undone and will revoke all access immediately.
                </p>
                <div class="mt-8 flex flex-col gap-3">
                    <DangerButton class="w-full justify-center py-3" @click="submitDelete" :disabled="deleteForm.processing">
                        Confirm Deletion
                    </DangerButton>
                    <SecondaryButton class="w-full justify-center py-3" @click="closeDeleteModal" :disabled="deleteForm.processing">
                        Keep Account
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scoped styles for refinement if needed */
.shadow-xl {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
}
</style>
