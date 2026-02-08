<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    groups: Array,
});

const form = useForm({
    name: '',
    permission_group_id: '',
    description: '',
    status: true,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => userPermissions.value.includes(permission);

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]+/g, '')
        .replace(/--+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const updateKey = () => {
    form.name = slugify(form.name);
};
</script>

<template>
    <Head title="Establish Security Gate" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-gray-900 uppercase">
                        New Security Persona
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Define a new functional identity and its operational boundaries.</p>
                </div>
                <AppButton
                    :href="route('permissions.index')"
                    variant="secondary"
                >
                    <template #icon>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </template>
                    Back to Matrix
                </AppButton>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-10 shadow-premium space-y-8"
                    @submit.prevent="form.post(route('permissions.store'))"
                >
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <InputLabel for="name" value="Security Gate Label" class="text-xs font-black uppercase tracking-widest text-gray-500" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                @input="updateKey"
                                type="text"
                                class="block w-full"
                                placeholder="e.g. Asset Intelligence Dashboard"
                                autofocus
                            />
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Key: <span class="text-primary">{{ form.name || '...' }}</span></p>
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <InputLabel for="permission_group_id" value="Module Assignment" class="text-xs font-black uppercase tracking-widest text-gray-500" />
                            <select
                                id="permission_group_id"
                                v-model="form.permission_group_id"
                                class="block w-full border-gray-100 bg-gray-50 px-4 py-3 rounded-2xl text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary"
                                required
                            >
                                <option value="" disabled>Select Module...</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                            </select>
                            <InputError :message="form.errors.permission_group_id" />
                        </div>

                        <div class="space-y-2">
                            <InputLabel for="description" value="Documentation" class="text-xs font-black uppercase tracking-widest text-gray-500" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="block w-full rounded-2xl border-gray-100 bg-gray-50 px-4 py-3 text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary h-32"
                                placeholder="Briefly describe the security scope..."
                            ></textarea>
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-2xl">
                            <GlobalCheckbox
                                id="status"
                                v-model:checked="form.status"
                            />
                            <label for="status" class="text-sm font-bold text-gray-700 uppercase tracking-tight">Active In Matrix</label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-50 pt-8">
                        <AppButton
                            v-if="can('permission-create')"
                            type="submit"
                            variant="primary"
                            :processing="form.processing"
                            class="h-14 px-10 rounded-2xl text-base"
                        >
                            <template #icon>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </template>
                            Establish Gate
                        </AppButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

