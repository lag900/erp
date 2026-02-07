<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    levels: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    level_id: '',
    name: '',
    code: '',
});
</script>

<template>
    <Head title="Add Room" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Create Room
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Register a new room, classroom, or hall space.
                    </p>
                </div>
                <Link
                    :href="route('rooms.index')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Rooms
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-soft"
                    @submit.prevent="form.post(route('rooms.store'))"
                >
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Room Details</h3>
                        <p class="text-sm text-gray-500">Specify the location and details of the room.</p>
                    </div>

                    <div class="p-6 grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel for="level_id" value="Level / Floor" />
                            <div class="relative mt-1">
                                <select
                                    id="level_id"
                                    v-model="form.level_id"
                                    class="block w-full appearance-none rounded-lg border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-primary"
                                >
                                    <option value="" disabled>Select a level...</option>
                                    <option
                                        v-for="level in levels"
                                        :key="level.id"
                                        :value="level.id"
                                    >
                                        {{ level.label }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.level_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Room Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                placeholder="e.g. Lecture Hall 1"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Room Code" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                class="mt-1 block w-full"
                                placeholder="e.g. 101"
                            />
                             <p class="mt-1 text-xs text-gray-500">Unique identifier for the room.</p>
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                        <Link
                            :href="route('rooms.index')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton v-if="can('room-create')" :disabled="form.processing">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Save Room
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
