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
    if (confirm('Are you sure you want to delete this entity?')) {
        deleteForm.delete(route('departments.destroy', departmentId));
    }
};
</script>

<template>
    <Head title="Faculties & Departments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Faculties / Departments
                </h2>
                <Link
                    v-if="can('department-create')"
                    :href="route('departments.create')"
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all active:scale-95"
                >
                    Add New Entity
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Universal search by name, code, or description..."
                        />
                    </div>
                </div>

                <div v-if="filteredDepartments.length === 0" class="rounded-3xl bg-white p-12 shadow-sm border border-slate-100 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-slate-500 font-bold">No academic entities matched your search.</p>
                </div>

                <div v-else class="overflow-hidden rounded-3xl bg-white shadow-sm border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500 uppercase text-[11px] font-black tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Name & Code</th>
                                <th class="px-6 py-4 text-center">Type</th>
                                <th class="px-6 py-4 text-center">Director</th>
                                <th class="px-6 py-4 text-center">Order</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr 
                                v-for="department in filteredDepartments" 
                                :key="department.id"
                                class="hover:bg-slate-50/50 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400">
                                            {{ department.code || '?' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ department.name }}</div>
                                            <div class="text-[11px] text-slate-400 font-medium">{{ department.code || 'No Code' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span 
                                        :class="[
                                            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border',
                                            department.type === 'faculty' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-50 text-slate-700 border-slate-100'
                                        ]"
                                    >
                                        {{ department.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div v-if="department.director_name">
                                        <div class="text-xs font-bold text-slate-800">{{ department.director_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ department.director_title }}</div>
                                    </div>
                                    <span v-else class="text-slate-300">-</span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs font-bold text-slate-500">
                                    #{{ department.display_order }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span 
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tight',
                                            department.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'
                                        ]"
                                    >
                                        <span :class="['w-1.5 h-1.5 rounded-full', department.status === 'active' ? 'bg-emerald-500' : 'bg-red-500']"></span>
                                        {{ department.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <Link
                                            v-if="can('department-edit')"
                                            :href="route('departments.edit', department.id)"
                                            class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                            title="Edit Entity"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('department-delete')"
                                            @click="deleteDepartment(department.id)"
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                            title="Delete Entity"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    </AuthenticatedLayout>
</template>
