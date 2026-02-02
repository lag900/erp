<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

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
    if (!term) {
        return props.categories;
    }

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(term)
    );
});

const deleteForm = useForm({});

const deleteCategory = (categoryId) => {
    deleteForm.delete(route('categories.destroy', categoryId));
};
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Categories
                </h2>
                <Link
                    v-if="can('category-create')"
                    :href="route('categories.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Category
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
                        placeholder="Search categories..."
                    />
                </div>

                <div
                    v-if="filteredCategories.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No categories found.</p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        class="rounded bg-white p-4 shadow"
                    >
                        <div v-if="category.image_url" class="mb-3">
                            <img
                                :src="category.image_url"
                                :alt="category.name"
                                class="h-32 w-full rounded object-cover"
                            />
                        </div>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ category.name }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                v-if="can('category-edit')"
                                :href="route('categories.edit', category.id)"
                                class="text-sm text-indigo-600 hover:text-indigo-700"
                            >
                                Edit
                            </Link>
                            <DangerButton
                                v-if="can('category-delete')"
                                type="button"
                                class="text-xs"
                                :disabled="deleteForm.processing"
                                @click="deleteCategory(category.id)"
                            >
                                Delete
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

