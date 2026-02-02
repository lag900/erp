<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    departments: {
        type: Array,
        required: true,
    },
    selectedDepartmentId: {
        type: Number,
        default: null,
    },
});

const form = useForm({
    department_id: props.selectedDepartmentId,
});

const search = ref('');

const filteredDepartments = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.departments;
    }

    return props.departments.filter((department) =>
        department.name.toLowerCase().includes(term)
    );
});

const selectDepartment = (id) => {
    form.department_id = id;
    form.post(route('departments.select.store'));
};
</script>

<template>
    <Head title="Select Department" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Select Department
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Search departments..."
                    />
                </div>

                <div v-if="filteredDepartments.length === 0" class="rounded bg-white p-6 shadow">
                    <p class="text-gray-600">No departments available.</p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="department in filteredDepartments"
                        :key="department.id"
                        class="rounded bg-white p-4 shadow"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ department.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    ID: {{ department.id }}
                                </p>
                            </div>
                            <PrimaryButton
                                type="button"
                                :disabled="form.processing"
                                @click="selectDepartment(department.id)"
                            >
                                Enter
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
