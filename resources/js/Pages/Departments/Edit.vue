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
    department: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    _method: 'PUT',
    name: props.department.name,
    arabic_name: props.department.arabic_name ?? '',
    code: props.department.code ?? '',
    description: props.department.description ?? '',
    director_name: props.department.director_name ?? '',
    director_title: props.department.director_title ?? '',
    director_image: null,
    display_order: props.department.display_order ?? 0,
    status: props.department.status ?? 'active',
    type: props.department.type ?? 'department',
});

const submit = () => {
    form.post(route('departments.update', props.department.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Department" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Department / Faculty: {{ department.name }}
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('departments.index')"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to List
                    </Link>
                    <DangerButton
                        v-if="can('department-delete')"
                        type="button"
                        @click="$inertia.delete(route('departments.destroy', department.id))"
                    >
                        Delete
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow space-y-8"
                    @submit.prevent="submit"
                >
                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4 border-b pb-2">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" value="English Name" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>
                            <div>
                                <InputLabel for="arabic_name" value="Arabic Name" />
                                <TextInput
                                    id="arabic_name"
                                    v-model="form.arabic_name"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.arabic_name" />
                            </div>
                            <div>
                                <InputLabel for="code" value="Entity Code" />
                                <TextInput
                                    id="code"
                                    v-model="form.code"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.code" />
                            </div>
                             <div>
                                <InputLabel for="type" value="Type" />
                                <select 
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="department">Department</option>
                                    <option value="faculty">Faculty</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.type" />
                            </div>
                        </div>
                        <div class="mt-6">
                            <InputLabel for="description" value="Full Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <!-- Leadership Info -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4 border-b pb-2">Leadership (Dean/Director)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="director_name" value="Director Name" />
                                <TextInput
                                    id="director_name"
                                    v-model="form.director_name"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.director_name" />
                            </div>
                            <div>
                                <InputLabel for="director_title" value="Director Title" />
                                <TextInput
                                    id="director_title"
                                    v-model="form.director_title"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.director_title" />
                            </div>
                        </div>
                        <div class="mt-6">
                            <div class="flex items-start gap-6">
                                <div v-if="department.director_image" class="w-24 h-24 rounded-2xl overflow-hidden shadow-md shrink-0 border border-slate-200">
                                    <img :src="department.director_image.startsWith('http') ? department.director_image : '/storage/' + department.director_image" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <InputLabel for="director_image" value="Update Director Image" />
                                    <input 
                                        type="file"
                                        @input="form.director_image = $event.target.files[0]"
                                        class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                    <p class="text-[11px] text-slate-400 mt-1">Leave empty to keep current image. Recommended: Square, 400x400px. Max 5MB.</p>
                                    <InputError class="mt-2" :message="form.errors.director_image" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4 border-b pb-2">Display Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="display_order" value="Display Order" />
                                <TextInput
                                    id="display_order"
                                    type="number"
                                    v-model="form.display_order"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.display_order" />
                            </div>
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select 
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="active">Active (Visible)</option>
                                    <option value="inactive">Inactive (Hidden)</option>
                                    <option value="hidden">System Hidden</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end gap-3 border-t pt-6">
                        <Link
                            :href="route('departments.index')"
                            class="rounded-xl border border-gray-300 px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 bg-white"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton
                            v-if="can('department-edit')"
                            :disabled="form.processing"
                            class="rounded-xl px-8 py-2.5 font-bold"
                        >
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Save Changes</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
