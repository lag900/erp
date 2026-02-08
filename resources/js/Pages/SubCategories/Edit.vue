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
    subCategory: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    _method: 'PUT',
    category_id: props.subCategory.category_id,
    name: props.subCategory.name,
    code: props.subCategory.code || '',
    image: null,
});
</script>

<template>
    <Head title="Edit Subcategory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Edit Subcategory
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Update subcategory details or classification.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('subcategories.index')"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Subcategories
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-premium"
                    @submit.prevent="form.post(route('subcategories.update', subCategory.id), {
                        forceFormData: true,
                        onSuccess: () => window.showToast('success', 'Sub-category updated successfully.')
                    })"
                >
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight">Technical Node Profile</h3>
                            <p class="text-[13px] font-medium text-slate-400">Refine specialized asset classification details.</p>
                        </div>
                         <button
                            v-if="can('sub_category-delete')"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-rose-50 px-4 py-2 text-xs font-bold text-rose-600 border border-rose-100 transition-all hover:bg-rose-100 hover:scale-105 active:scale-95"
                            @click="window.showConfirm({
                                title: 'Decommission Sub-category?',
                                message: `Are you sure you want to decommission ${subCategory.name}? This will retire the technical spec.`,
                                confirmText: 'Decommission',
                                onConfirm: () => $inertia.delete(route('subcategories.destroy', subCategory.id))
                            })"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Decommission
                        </button>
                    </div>

                    <div class="p-6 grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel for="category_id" value="Parent Category" />
                            <div class="relative mt-1">
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="block w-full appearance-none rounded-lg border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-primary"
                                >
                                    <option value="" disabled>Select category...</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="name" value="Subcategory Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="code" value="Subcategory Code" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                class="mt-1 block w-full font-mono bg-gray-50 uppercase"
                                placeholder="e.g. DESK"
                            />
                            <p class="mt-1 text-xs text-gray-500">Short unique code used in generating asset identification numbers (QR codes).</p>
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="image" value="Subcategory Image" />
                            <div class="mt-2 flex gap-6">
                                <div v-if="subCategory.image_url" class="shrink-0 relative">
                                    <div class="relative h-24 w-24 overflow-hidden rounded-lg border border-gray-200">
                                        <img
                                            :src="subCategory.image_url"
                                            :alt="subCategory.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <p class="mt-1 text-center text-xs text-gray-500">Current</p>
                                </div>

                                <div class="grow rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                            <label for="image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-indigo-500">
                                                <span>{{ subCategory.image_url ? 'Replace image' : 'Upload a file' }}</span>
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
                            :href="route('subcategories.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton v-if="can('sub_category-edit')" :disabled="form.processing">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
