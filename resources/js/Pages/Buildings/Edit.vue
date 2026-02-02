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
    building: {
        type: Object,
        required: true,
    },
    locations: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    location_id: props.building.location_id,
    name: props.building.name,
    code: props.building.code ?? '',
});
</script>

<template>
    <Head title="Edit Building" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Building
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('buildings.index')"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to Buildings
                    </Link>
                    <DangerButton
                        v-if="can('building-delete')"
                        type="button"
                        @click="$inertia.delete(route('buildings.destroy', building.id))"
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
                    @submit.prevent="form.put(route('buildings.update', building.id))"
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="location_id" value="Location" />
                            <select
                                id="location_id"
                                v-model="form.location_id"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select location</option>
                                <option
                                    v-for="location in locations"
                                    :key="location.id"
                                    :value="location.id"
                                >
                                    {{ location.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.location_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Building Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <InputLabel for="code" value="Building Code" />
                        <TextInput
                            id="code"
                            v-model="form.code"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.code" />
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton
                            v-if="can('building-edit')"
                            :disabled="form.processing"
                        >
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
