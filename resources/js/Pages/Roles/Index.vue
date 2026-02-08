<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const deleteForm = useForm({});
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredRoles = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.roles;
    }

    return props.roles.filter((role) =>
        role.name.toLowerCase().includes(term)
    );
});

const deleteRole = (roleId) => {
    if(window.confirm('Permanently de-authorize this role? This cannot be undone.')) {
        deleteForm.delete(route('roles.destroy', roleId));
    }
};
</script>

<template>
    <Head title="Role Registry" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Identity Registry
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Manage organizational roles and their high-level access tiers.</p>
                </div>
                <AppButton
                    v-if="can('role-create')"
                    :href="route('roles.create')"
                    variant="primary"
                >
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                    </template>
                    Add Security Persona
                </AppButton>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Advanced Search & Stats -->
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:w-96">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full border-gray-200 pl-10 h-11 rounded-xl shadow-soft focus:ring-primary/20"
                            placeholder="Search identity titles..."
                        />
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-soft">
                            <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                            <span class="text-xs font-black uppercase text-gray-500 tracking-tighter">{{ roles.length }} Roles Defined</span>
                        </div>
                    </div>
                </div>

                <div v-if="filteredRoles.length === 0" class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-200 bg-white p-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 uppercase">Silence in the Registry</h3>
                    <p class="mt-1 text-sm text-gray-500">No organizational roles match your search criteria.</p>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="role in filteredRoles"
                        :key="role.id"
                        class="group relative flex flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-primary/20 hover:shadow-xl"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                <svg v-if="role.name === 'SuperAdmin'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="rounded-lg bg-gray-100 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-gray-500 ring-1 ring-inset ring-gray-200">
                                    {{ role.permissions_count }} Gates
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-1 flex-col">
                            <h3 class="text-xl font-black tracking-tighter text-gray-900 group-hover:text-primary transition-colors">
                                {{ role.name }}
                            </h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-500">
                                Defined functional boundaries for {{ role.name }} across all institutional modules.
                            </p>
                        </div>

                        <div class="mt-8 flex items-center justify-between border-t border-gray-50 pt-6">
                            <div class="flex gap-2">
                                <AppButton
                                    v-if="can('role-edit')"
                                    :href="route('roles.edit', role.id)"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Map Permissions
                                </AppButton>
                            </div>
                            <AppButton
                                v-if="can('role-delete') && role.name !== 'SuperAdmin'"
                                variant="ghost"
                                size="xs"
                                class="!p-2 hover:!bg-rose-50 hover:!text-rose-500"
                                @click="deleteRole(role.id)"
                                title="De-authorize Role"
                            >
                                <template #icon>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </template>
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

