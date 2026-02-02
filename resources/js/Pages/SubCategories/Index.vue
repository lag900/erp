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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Subcategories
                </h2>
                <Link
                    v-if="can('sub_category-create')"
                    :href="route('subcategories.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Subcategory
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Search subcategories..."
                    />
                </div>

                <div
                    v-if="filteredSubCategories.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No subcategories found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Image</th>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Category</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="subCategory in filteredSubCategories"
                                :key="subCategory.id"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        v-if="subCategory.image_url"
                                        :src="subCategory.image_url"
                                        :alt="subCategory.name"
                                        class="h-16 w-16 rounded object-cover"
                                    />
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ subCategory.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ subCategory.category || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('sub_category-edit')"
                                            :href="route('subcategories.edit', subCategory.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('sub_category-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteSubCategory(subCategory.id)"
                                        >
                                            Delete
                                        </DangerButton>
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
