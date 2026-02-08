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
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    name: props.category.name,
    name_ar: props.category.name_ar || '',
    code: props.category.code || '',
    image: null,
    department_ids: props.category.department_ids || [],
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
                        Update category details or manage its department assignments.
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
                    class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-premium"
                    @submit.prevent="form.transform((data) => ({
                        ...data,
                        _method: 'put',
                    })).post(route('categories.update', category.id), {
                        forceFormData: true,
                        onSuccess: () => window.showToast('success', 'Category updated successfully.')
                    })"
                >
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight">Taxonomy Profile</h3>
                            <p class="text-[13px] font-medium text-slate-400">Manage primary asset classification nodes.</p>
                        </div>
                         <button
                            v-if="can('category-delete')"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-rose-50 px-4 py-2 text-xs font-bold text-rose-600 border border-rose-100 transition-all hover:bg-rose-100 hover:scale-105 active:scale-95"
                            @click="window.showConfirm({
                                title: 'Decommission Category?',
                                message: `Are you sure you want to decommission ${category.name}? This will retire the classification from future use.`,
                                confirmText: 'Decommission',
                                onConfirm: () => $inertia.delete(route('categories.destroy', category.id))
                            })"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Decommission
                        </button>
                    </div>

                    <div class="p-6 grid gap-6">
                        <div>
                            <InputLabel for="name" value="Category Name (English)" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="name_ar" value="Category Name (Arabic)" />
                            <TextInput
                                id="name_ar"
                                v-model="form.name_ar"
                                dir="rtl"
                                class="mt-1 block w-full text-right"
                            />
                            <InputError class="mt-2" :message="form.errors.name_ar" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Category Code" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                class="mt-1 block w-full font-mono bg-gray-50 uppercase"
                                placeholder="e.g. ELEC"
                            />
                             <p class="mt-1 text-xs text-gray-500">Short unique code used in generating asset identification numbers (QR codes).</p>
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>

                        <div>
                            <InputLabel value="Assigned Departments" />
                            <p class="text-xs text-gray-500 mt-1 mb-3">Select which departments can use this category. Shared categories help in cross-departmental reporting.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-3 border border-gray-100 rounded-lg bg-gray-50/50">
                                <label 
                                    v-for="dept in props.departments" 
                                    :key="dept.id"
                                    class="relative flex items-start cursor-pointer group"
                                >
                                    <div class="flex h-6 items-center">
                                        <GlobalCheckbox
                                            :id="'dept-' + dept.id"
                                            v-model:checked="form.department_ids"
                                            :value="dept.id"
                                        />
                                    </div>
                                    <div class="ml-3 text-sm leading-6">
                                        <span class="font-medium text-gray-700 group-hover:text-primary transition-colors">{{ dept.name }}</span>
                                    </div>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.department_ids" />
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
                                <div class="grow rounded-lg border border-dashed border-gray-900/25 px-6 py-10 transition-colors hover:border-primary/50">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                            <label for="image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-indigo-500 transition-colors">
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
                                        <div v-if="form.image" class="mt-2 flex items-center justify-center gap-2">
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                Selected: {{ form.image.name }}
                                            </span>
                                            <button type="button" @click="form.image = null" class="text-xs text-red-500 hover:text-red-700 underline">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.image" />
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                        <Link
                            :href="route('categories.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton v-if="can('category-edit')" :disabled="form.processing" class="shadow-sm">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
