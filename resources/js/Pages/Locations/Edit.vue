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
import imageCompression from 'browser-image-compression';

const props = defineProps({
    location: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    _method: 'PUT',
    name: props.location.name,
    description: props.location.description ?? '',
    image: null,
});

const handleImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const options = {
        maxSizeMB: 1,
        maxWidthOrHeight: 1920,
        useWebWorker: true,
        initialQuality: 0.8
    };

    try {
        const compressedFile = await imageCompression(file, options);
        form.image = compressedFile;
    } catch (error) {
        console.error('Image compression failed:', error);
        form.image = file;
    }
};
</script>

<template>
    <Head title="Edit Location" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Edit Location
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Update location information and spatial visuals.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('locations.index')"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Locations
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-premium"
                    @submit.prevent="form.post(route('locations.update', location.id), {
                        forceFormData: true,
                        onSuccess: () => window.showToast('success', 'Location updated successfully.')
                    })"
                >
                    <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight">Location Profile</h3>
                            <p class="text-[13px] font-medium text-slate-400">Manage spatial details and identifiers.</p>
                        </div>
                        <button
                            v-if="can('location-delete')"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-rose-50 px-4 py-2 text-xs font-bold text-rose-600 border border-rose-100 transition-all hover:bg-rose-100 hover:scale-105 active:scale-95"
                            @click="window.showConfirm({
                                title: 'Decommission Location?',
                                message: `Are you sure you want to decommission ${location.name}? This will affect all mapping associated with this premise.`,
                                confirmText: 'Decommission',
                                onConfirm: () => $inertia.delete(route('locations.destroy', location.id))
                            })"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Decommission
                        </button>
                    </div>

                    <div class="p-6 grid gap-6">
                        <div>
                            <InputLabel for="name" value="Location Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="image" value="Location Image" />
                            <div class="mt-2 flex gap-6">
                                <div class="shrink-0 relative">
                                    <EntityImage
                                        :src="location.image_url"
                                        :alt="location.name"
                                        type="location"
                                        class="h-24 w-24 rounded-lg border border-gray-200"
                                    />
                                    <p class="mt-1 text-center text-xs text-gray-500">Current</p>
                                </div>

                                <div class="grow rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                            <label for="image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-indigo-500">
                                                <span>{{ location.image_url ? 'Replace image' : 'Upload a file' }}</span>
                                                <input
                                                    id="image"
                                                    type="file"
                                                    accept="image/*"
                                                    capture="environment"
                                                    class="sr-only"
                                                    @change="handleImageUpload"
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
                            :href="route('locations.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton
                            v-if="can('location-edit')"
                            :disabled="form.processing"
                        >
                             <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
