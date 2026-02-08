<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';

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
    if (!term) return props.subCategories;

    return props.subCategories.filter((subCategory) =>
        subCategory.name.toLowerCase().includes(term) ||
        (subCategory.category || '').toLowerCase().includes(term)
    );
});

const deleteForm = useForm({});

const confirmDelete = (sub) => {
    window.showConfirm({
        title: 'Delete Sub-category?',
        message: `Are you sure you want to delete ${sub.name}? This might affect assets assigned to this specific type.`,
        confirmText: 'Delete Sub-category',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(sub.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('subcategories.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Sub-category deleted successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to delete sub-category.');
        }
    });
};
</script>

<template>
    <Head title="Sub-categories" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Sub-categories"
                subtitle="Specialized asset classifications nested under primary categories."
                addActionText="Add Sub-category"
                addActionRoute="subcategories.create"
                :canAdd="can('sub_category-create')"
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
                        class="block w-full rounded-xl border-gray-200 bg-white pl-10 shadow-sm focus:border-primary focus:ring-primary h-11"
                        placeholder="Search sub-categories..."
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredSubCategories.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-200 bg-gray-50/50 p-16 text-center"
            >
                <div class="rounded-2xl bg-white p-4 shadow-soft border border-gray-100 mb-6 text-gray-300">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">No sub-categories found</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
                    Start by refining your categories into more specific sub-categories for better inventory management.
                </p>
                <div class="mt-8">
                     <Link
                        v-if="can('sub_category-create')"
                        :href="route('subcategories.create')"
                        class="btn-primary"
                    >
                        Add Sub-category
                    </Link>
                </div>
            </div>

            <!-- Grid -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <EntityCard
                    v-for="sub in filteredSubCategories"
                    :key="sub.id"
                    :title="sub.name"
                    :subtitle="sub.category"
                    :image="sub.image_url"
                    type="category"
                    badgeText="Refined Category"
                >
                    <template #actions>
                        <Link
                            v-if="can('sub_category-edit')"
                            :href="route('subcategories.edit', sub.id)"
                            class="edit-btn"
                            title="Edit Sub-category"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('sub_category-delete')"
                            @click="confirmDelete(sub)"
                            class="delete-btn"
                            :disabled="deleteForm.processing"
                            title="Delete Sub-category"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </template>
                </EntityCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
