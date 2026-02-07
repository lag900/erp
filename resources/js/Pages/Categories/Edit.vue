<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import EntityImage from '@/Components/EntityImage.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    name: props.category.name,
    image: null,
});
</script>

<template>
    <Head title="Edit Category" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Edit Category
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Update category details or manage its media.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('categories.index')"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Categories
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                 <form
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft"
                    @submit.prevent="form.post(route('categories.update', category.id), {
                        method: 'put',
                        forceFormData: true,
                    })"
                >
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Category Details</h3>
                            <p class="text-sm text-gray-500">Update the information below.</p>
                        </div>
                         <DangerButton
                            v-if="can('category-delete')"
                            type="button"
                            class="text-xs"
                            @click="() => { if(confirm('Are you sure you want to delete this category?')) $inertia.delete(route('categories.destroy', category.id)) }"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete Category
                        </DangerButton>
                    </div>

                    <div class="p-6 grid gap-6">
                        <div>
                            <InputLabel for="name" value="Category Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="image" value="Category Image" />
                            
                             <div class="mt-2 flex gap-6">
                                <div class="shrink-0 relative">
                                    <EntityImage
                                        :src="category.image_url"
                                        :alt="category.name"
                                        type="category"
                                        class="h-32 w-32 rounded-lg border border-gray-200"
                                    />
                                    <p class="mt-1 text-center text-xs text-gray-500">Current</p>
                                </div>
                                <div class="grow rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                            <label for="image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-indigo-500">
                                                <span>{{ category.image_url ? 'Replace image' : 'Upload a file' }}</span>
                                                <input
                                                    id="image"
                                                    type="file"
                                                    accept="image/*"
                                                    class="sr-only"
                                                    @input="form.image = $event.target.files[0]"
                                                />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600">PNG, JPG, GIF up to 2MB</p>
                                        <p v-if="form.image" class="mt-2 text-sm font-semibold text-green-600">
                                            Selected: {{ form.image.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.image" />
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                        <Link
                            :href="route('categories.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton v-if="can('category-edit')" :disabled="form.processing">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
