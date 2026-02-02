<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Category
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('categories.index')"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to Categories
                    </Link>
                    <DangerButton
                        v-if="can('category-delete')"
                        type="button"
                        @click="$inertia.delete(route('categories.destroy', category.id))"
                    >
                        Delete
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="form.post(route('categories.update', category.id), {
                        method: 'put',
                        forceFormData: true,
                    })"
                >
                    <div>
                        <InputLabel for="name" value="Category Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-6">
                        <InputLabel for="image" value="Category Image (Optional)" />
                        <div v-if="category.image_url" class="mb-3">
                            <p class="mb-2 text-sm text-gray-600">Current Image:</p>
                            <img
                                :src="category.image_url"
                                :alt="category.name"
                                class="h-32 w-32 rounded border border-gray-300 object-cover"
                            />
                        </div>
                        <input
                            id="image"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            @input="form.image = $event.target.files[0]"
                        />
                        <InputError class="mt-2" :message="form.errors.image" />
                        <p class="mt-1 text-xs text-gray-500">
                            Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB
                        </p>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton v-if="can('category-edit')" :disabled="form.processing">
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
