<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    location_id: '',
    name: '',
    code: '',
    image: null,
});
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
                            <InputLabel for="name" value="Building Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                placeholder="e.g. Engineering Block A"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Building Code" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                class="mt-1 block w-full"
                                placeholder="e.g. BLD-01"
                            />
                            <p class="mt-1 text-xs text-gray-500">Optional shorthand code.</p>
                            <InputError class="mt-2" :message="form.errors.code" />
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
