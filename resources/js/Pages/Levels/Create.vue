<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    buildings: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    building_id: '',
    name: '',
    level_number: '',
});
</script>

<template>
    <Head title="Add Level" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Add Level
                </h2>
                <Link
                    :href="route('levels.index')"
                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Back to Levels
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="form.post(route('levels.store'))"
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="building_id" value="Building" />
                            <select
                                id="building_id"
                                v-model="form.building_id"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select building</option>
                                <option
                                    v-for="building in buildings"
                                    :key="building.id"
                                    :value="building.id"
                                >
                                    {{ building.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.building_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Level Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                placeholder="First Floor"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <InputLabel for="level_number" value="Level Number" />
                        <TextInput
                            id="level_number"
                            v-model="form.level_number"
                            class="mt-1 block w-full"
                            placeholder="1"
                        />
                        <InputError class="mt-2" :message="form.errors.level_number" />
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton v-if="can('level-create')" :disabled="form.processing">
                            Save Level
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
