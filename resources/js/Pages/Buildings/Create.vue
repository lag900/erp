<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';
import imageCompression from 'browser-image-compression';

const props = defineProps({
    locations: {
        type: Array,
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
    location_id: '',
    name_en: '',
    name_ar: '',
    is_shared: false,
    department_ids: [],
    image: null,
});

const handleImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const options = {
        maxSizeMB: 1, // Buildings can be a bit higher quality
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
    <Head title="Add Building" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Create Building
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Register a new building under a specific university location.
                    </p>
                </div>
                <Link
                    :href="route('buildings.index')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Buildings
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft"
                    @submit.prevent="form.post(route('buildings.store'), {
                        forceFormData: true,
                    })"
                >
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Building Details</h3>
                        <p class="text-sm text-gray-500">Provide the necessary information to identify the building.</p>
                    </div>

                    <div class="p-6 grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel for="location_id" value="Location" />
                            <div class="relative mt-1">
                                <select
                                    id="location_id"
                                    v-model="form.location_id"
                                    class="block w-full appearance-none rounded-lg border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-primary"
                                >
                                    <option value="" disabled>Select a location...</option>
                                    <option
                                        v-for="location in locations"
                                        :key="location.id"
                                        :value="location.id"
                                    >
                                        {{ location.name }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.location_id" />
                        </div>

                        <div>
                            <InputLabel for="name_en" value="English Name (Required)" />
                            <TextInput
                                id="name_en"
                                v-model="form.name_en"
                                class="mt-1 block w-full"
                                placeholder="e.g. Engineering Block A"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name_en" />
                        </div>

                        <div>
                            <InputLabel for="name_ar" value="Arabic Name (Optional)" />
                            <TextInput
                                id="name_ar"
                                v-model="form.name_ar"
                                class="mt-1 block w-full text-right"
                                dir="rtl"
                                placeholder="مثال: مبنى الهندسة (أ)"
                            />
                            <InputError class="mt-2" :message="form.errors.name_ar" />
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2">
                             <GlobalCheckbox 
                                id="is_shared" 
                                v-model:checked="form.is_shared"
                             />
                             <InputLabel for="is_shared" value="This is a shared building (available across all departments)" />
                        </div>

                        <!-- Department Assignment -->
                        <div class="sm:col-span-2">
                            <InputLabel value="Assigned Departments" />
                            <p class="text-xs text-gray-500 mb-3">Select one or more departments that have access to this building.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 border rounded-lg p-4 bg-gray-50">
                                <div v-for="dept in departments" :key="dept.id" class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        :id="'dept-' + dept.id"
                                        :value="dept.id"
                                        v-model="form.department_ids"
                                        class="rounded border-gray-300 text-primary shadow-sm focus:ring-primary h-4 w-4"
                                    />
                                    <label :for="'dept-' + dept.id" class="text-sm text-gray-700 cursor-pointer select-none">
                                        {{ dept.name }}
                                        <span v-if="dept.name === 'Administration'" class="ml-1 text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold uppercase tracking-wider">Root</span>
                                    </label>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.department_ids" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="image" value="Building Image (Optional)" />
                            <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                        <label for="image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 hover:text-indigo-500">
                                            <span>Upload a file</span>
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
                            <InputError class="mt-2" :message="form.errors.image" />
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                        <Link
                            :href="route('buildings.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton
                            v-if="can('building-create')"
                            :disabled="form.processing"
                        >
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Building
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
