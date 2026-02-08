<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredCategories = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.categories;

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(term) ||
        (category.code || '').toLowerCase().includes(term)
    );
});

const deleteForm = useForm({});

const confirmDelete = (category) => {
    window.showConfirm({
        title: 'Delete Category?',
        message: `Are you sure you want to delete ${category.name}? This will remove all associated configurations.`,
        confirmText: 'Delete Category',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(category.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('categories.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Category deleted successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to delete category.');
        }
    });
};
</script>

<template>
    <Head title="Asset Categories" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Asset Categories"
                subtitle="Manage primary classifications and hierarchical groupings for university assets."
                addActionText="Add Category"
                addActionRoute="categories.create"
                :canAdd="can('category-create')"
            />
        </template>

        <div class="py-8">
            <!-- Search -->
            <div class="mb-8">
                <div class="relative max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <TextInput
                        v-model="search"
                        type="text"
                        class="block w-full rounded-xl border-gray-200 bg-white pl-10 shadow-sm focus:border-primary focus:ring-primary h-11 transition-all"
                        placeholder="Search by name or code..."
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredCategories.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-200 bg-gray-50/50 p-16 text-center"
            >
                <div class="rounded-2xl bg-white p-4 shadow-soft border border-gray-100 mb-6 text-gray-300">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">No categories found</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
                    Organize your assets by creating classifications like Electronics, Furniture, or Lab Equipment.
                </p>
                <div class="mt-8">
                     <Link
                        v-if="can('category-create')"
                        :href="route('categories.create')"
                        class="btn-primary"
                    >
                        Add Category
                    </Link>
                </div>
            </div>

            <!-- Grid -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <EntityCard
                    v-for="category in filteredCategories"
                    :key="category.id"
                    :title="category.name"
                    :subtitle="category.code"
                    :image="category.image_url"
                    type="category"
                    :badgeText="category.code"
                >
                    <template #subtitle-extra>
                        <div v-if="category.department_names" class="mt-2 flex flex-wrap gap-1">
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600 ring-1 ring-inset ring-indigo-600/10 uppercase tracking-tight">
                                {{ category.department_names }}
                            </span>
                        </div>
                    </template>
                    <template #actions>
                        <Link
                            v-if="can('category-edit')"
                            :href="route('categories.edit', category.id)"
                            class="edit-btn"
                            title="Edit Category"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('category-delete')"
                            @click="confirmDelete(category)"
                            class="delete-btn"
                            :disabled="deleteForm.processing"
                            title="Delete Category"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </template>
                </EntityCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
