<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    departments: {
        type: Array,
        required: true,
    },
    features: {
        type: Array,
        required: true,
    },
    departmentFeatures: {
        type: Object,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
const activeDepartmentId = ref(props.departments[0]?.id || null);

const filteredDepartments = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.departments;
    }

    return props.departments.filter((department) =>
        department.name.toLowerCase().includes(term)
    );
});

const buildFeaturesPayload = (departmentId) => {
    const enabledMap = props.departmentFeatures?.[departmentId] || {};

    return props.features.map((feature) => ({
        id: feature.id,
        is_enabled: Boolean(enabledMap[feature.id]),
    }));
};

const form = useForm({
    features: buildFeaturesPayload(activeDepartmentId.value),
});

const setDepartment = (departmentId) => {
    activeDepartmentId.value = departmentId;
};

watch(activeDepartmentId, (departmentId) => {
    form.features = buildFeaturesPayload(departmentId);
});

const toggleFeature = (featureId) => {
    form.features = form.features.map((feature) => {
        if (feature.id === featureId) {
            return { ...feature, is_enabled: !feature.is_enabled };
        }

        return feature;
    });
};

const save = () => {
    if (!activeDepartmentId.value) {
        return;
    }

    form.put(route('departments.features.update', activeDepartmentId.value));
};
</script>

<template>
    <Head title="Department Features" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Department Features
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
                    <div class="rounded bg-white p-4 shadow">
                        <p class="text-sm font-semibold text-gray-700">Departments</p>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="mt-3 block w-full"
                            placeholder="Search departments..."
                        />
                        <div class="mt-4 space-y-2">
                            <button
                                v-for="department in filteredDepartments"
                                :key="department.id"
                                type="button"
                                class="w-full rounded border px-3 py-2 text-left text-sm transition"
                                :class="[
                                    department.id === activeDepartmentId
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                        : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50',
                                ]"
                                @click="setDepartment(department.id)"
                            >
                                {{ department.name }}
                            </button>
                        </div>
                    </div>

                    <div class="rounded bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Features
                            </h3>
                            <PrimaryButton
                                v-if="can('feature-toggle')"
                                :disabled="form.processing"
                                @click="save"
                            >
                                Save Changes
                            </PrimaryButton>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="feature in form.features"
                                :key="feature.id"
                                class="flex items-center justify-between rounded border border-gray-200 p-4"
                            >
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{
                                            props.features.find((f) => f.id === feature.id)?.name
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            props.features.find((f) => f.id === feature.id)?.key
                                        }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="rounded px-3 py-1 text-xs font-semibold"
                                    :disabled="!can('feature-toggle')"
                                    :class="feature.is_enabled
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600'"
                                    @click="toggleFeature(feature.id)"
                                >
                                    {{ feature.is_enabled ? 'Enabled' : 'Disabled' }}
                                </button>
                            </div>
                        </div>

                        <p v-if="!activeDepartmentId" class="mt-4 text-sm text-gray-500">
                            Select a department first.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
