<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    subCategories: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredSubCategories = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.subCategories;
    }

    return props.subCategories.filter((subCategory) => {
        return (
            subCategory.name.toLowerCase().includes(term) ||
            (subCategory.category || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteSubCategory = (subCategoryId) => {
    deleteForm.delete(route('subcategories.destroy', subCategoryId));
};
</script>

<template>
    <Head title="Subcategories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Subcategories
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage specific asset types under main categories.
                    </p>
                </div>
                <Link
                    v-if="can('sub_category-create')"
                    :href="route('subcategories.create')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Subcategory
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-6">
                    <div class="relative max-w-md">
                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            placeholder="Search subcategories..."
                        />
                    </div>
                </div>

                <div
                    v-if="filteredSubCategories.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center"
                >
                    <div class="rounded-full bg-white p-3 shadow-sm">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No subcategories found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by adding a subcategory to a parent category.
                    </p>
                    <div class="mt-6">
                        <Link
                            v-if="can('sub_category-create')"
                            :href="route('subcategories.create')"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                             <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Add Subcategory
                        </Link>
                    </div>
                </div>

                <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Image</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Parent Category</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="subCategory in filteredSubCategories"
                                :key="subCategory.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                                        <img
                                            v-if="subCategory.image_url"
                                            :src="subCategory.image_url"
                                            :alt="subCategory.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ subCategory.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ subCategory.category || 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <Link
                                            v-if="can('sub_category-edit')"
                                            :href="route('subcategories.edit', subCategory.id)"
                                            class="text-gray-400 hover:text-primary transition-colors"
                                            title="Edit"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </Link>
                                        <button
                                            v-if="can('sub_category-delete')"
                                            type="button"
                                            class="text-gray-400 hover:text-red-600 transition-colors"
                                            :disabled="deleteForm.processing"
                                            @click="() => { if(confirm('Are you sure you want to delete this subcategory?')) deleteSubCategory(subCategory.id) }"
                                            title="Delete"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
