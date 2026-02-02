<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    department: {
        type: Object,
        default: null,
    },
    stats: {
        type: Object,
        required: true,
    },
    hasDepartmentSelected: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="hasDepartmentSelected" class="mb-6 rounded bg-white p-6 shadow">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Active Department</p>
                            <h3 class="text-2xl font-semibold text-gray-900">
                                {{ department?.name }}
                            </h3>
                            <p v-if="department?.description" class="mt-1 text-gray-600">
                                {{ department.description }}
                            </p>
                        </div>
                        <Link
                            :href="route('departments.select')"
                            class="rounded bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200"
                        >
                            Switch Department
                        </Link>
                    </div>
                </div>

                <div v-else class="mb-6 rounded border-2 border-dashed border-indigo-300 bg-indigo-50 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Welcome to ERB Management System
                    </h3>
                    <p class="mt-2 text-gray-700">
                        You are viewing system-wide statistics. To access department-specific features like Assets, please select a department.
                    </p>
                    <Link
                        :href="route('departments.select')"
                        class="mt-4 inline-flex rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                    >
                        Select Department
                    </Link>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">
                            {{ hasDepartmentSelected ? 'Department Assets' : 'Total Assets' }}
                        </p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.assets }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Locations</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.locations }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Buildings</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.buildings }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Levels</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.levels }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Rooms</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.rooms }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Categories</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.categories }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Subcategories</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.subCategories }}
                        </p>
                    </div>
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Departments</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.departments }}
                        </p>
                    </div>
                    <div v-if="stats.users" class="rounded bg-white p-4 shadow">
                        <p class="text-sm text-gray-500">Users</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stats.users }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
