<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    role: Object,
    groups: Array,
    ungrouped: Array,
});

const submit = () => {
    form.put(route('roles.update', props.role.id), {
        onSuccess: () => {
            if (window.showToast) window.showToast('success', 'Security matrix updated successfully.');
        }
    });
};

const search = ref('');
const page = usePage();
const userPermissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => userPermissions.value.includes(permission);

const form = useForm({
    name: props.role.name,
    permission_ids: props.role.permission_ids || [],
});

const searchMatchedGroups = computed(() => {
    const term = search.value.trim().toLowerCase();
    
    return props.groups.map(group => {
        const matchedPerms = group.permissions.filter(p => 
            p.name.toLowerCase().includes(term) || 
            (p.description && p.description.toLowerCase().includes(term))
        );
        return { ...group, matchedPerms };
    }).filter(group => group.matchedPerms.length > 0);
});

const searchedUngrouped = computed(() => {
    const term = search.value.trim().toLowerCase();
    return props.ungrouped.filter(p => 
        p.name.toLowerCase().includes(term) || 
        (p.description && p.description.toLowerCase().includes(term))
    );
});

const togglePermission = (permissionId) => {
    const index = form.permission_ids.indexOf(permissionId);
    if (index > -1) {
        form.permission_ids.splice(index, 1);
    } else {
        form.permission_ids.push(permissionId);
    }
};

const toggleCategory = (categoryPerms) => {
    const permIds = categoryPerms.map(p => p.id);
    const allSelected = permIds.every(id => form.permission_ids.includes(id));

    if (allSelected) {
        form.permission_ids = form.permission_ids.filter(id => !permIds.includes(id));
    } else {
        const newIds = permIds.filter(id => !form.permission_ids.includes(id));
        form.permission_ids = [...form.permission_ids, ...newIds];
    }
};

const isCategorySelected = (categoryPerms) => {
    return categoryPerms.length > 0 && categoryPerms.every(p => form.permission_ids.includes(p.id));
};

const isCategoryPartial = (categoryPerms) => {
    const selected = categoryPerms.filter(p => form.permission_ids.includes(p.id)).length;
    return selected > 0 && selected < categoryPerms.length;
};

const allPermissions = computed(() => {
    const perms = [];
    props.groups.forEach(g => perms.push(...g.permissions));
    perms.push(...props.ungrouped);
    return perms;
});

const isAllSelected = computed(() => {
    return allPermissions.value.length > 0 && 
           allPermissions.value.every(p => form.permission_ids.includes(p.id));
});

const isAllPartial = computed(() => {
    const selectedCount = allPermissions.value.filter(p => form.permission_ids.includes(p.id)).length;
    return selectedCount > 0 && selectedCount < allPermissions.value.length;
});

const toggleAllGlobal = () => {
    if (isAllSelected.value) {
        form.permission_ids = [];
    } else {
        form.permission_ids = allPermissions.value.map(p => p.id);
    }
};
</script>

<template>
    <Head :title="`Secure Matrix: ${form.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Access Control Matrix
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Fine-tune the operational perimeter for <span class="font-black text-primary">{{ role.name }}</span>.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <AppButton
                        :href="route('roles.index')"
                        variant="secondary"
                    >
                        <template #icon>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        </template>
                        Return to Registry
                    </AppButton>
                    <AppButton
                        v-if="can('role-delete') && role.name !== 'SuperAdmin'"
                        variant="danger"
                        @click="() => { if(window.confirm('Wipe this role and its access keys?')) $inertia.delete(route('roles.destroy', role.id)) }"
                        title="Delete Role"
                    >
                        <template #icon>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </template>
                    </AppButton>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <form
                    class="space-y-8"
                    @submit.prevent="submit"
                >
                    <!-- Role Basic Setup -->
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-8 shadow-premium">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tighter">Identity Core</h3>
                                <p class="text-xs text-gray-400">The primary label used for identifying this role across the system.</p>
                            </div>
                        </div>
                        
                        <div class="max-w-md">
                            <InputLabel for="name" value="Role Designation" class="text-xs font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="block w-full border-gray-200 focus:border-primary focus:ring-primary h-12 rounded-xl shadow-soft"
                                placeholder="e.g., Regional Manager"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>

                    <!-- Matrix Controls -->
                    <div class="space-y-6">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between px-2">
                        <div class="flex items-center gap-6 bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-soft">
                            <div class="flex items-center gap-3">
                                <GlobalCheckbox
                                    id="global-select-all"
                                    :checked="isAllSelected"
                                    :indeterminate="isAllPartial"
                                    @update:checked="toggleAllGlobal"
                                />
                                <label for="global-select-all" class="text-xs font-black uppercase tracking-widest text-gray-900 cursor-pointer select-none">
                                    Global Selector
                                </label>
                            </div>
                            <div class="h-4 w-px bg-gray-200"></div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                {{ form.permission_ids.length }} / {{ allPermissions.length }} Gates Enabled
                            </div>
                        </div>

                        <div class="relative w-full sm:w-80">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <TextInput
                                v-model="search"
                                class="block w-full border-gray-200 pl-10 h-11 rounded-xl text-sm transition-all focus:ring-primary/20 shadow-soft"
                                placeholder="Search authorization keys..."
                            />
                        </div>
                    </div>

                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="group in searchMatchedGroups"
                                :key="group.id"
                                class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft transition-all duration-300 hover:border-primary/20 hover:shadow-lg"
                            >
                                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <GlobalCheckbox
                                            :id="'group-' + group.id"
                                            :checked="isCategorySelected(group.matchedPerms)"
                                            :indeterminate="isCategoryPartial(group.matchedPerms)"
                                            @update:checked="toggleCategory(group.matchedPerms)"
                                        />
                                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-900">
                                            {{ group.name }}
                                        </h4>
                                    </div>
                                    <span class="text-[9px] font-bold uppercase text-gray-400">
                                        {{ group.matchedPerms.length }} Keys
                                    </span>
                                </div>

                                <div class="flex-1 space-y-1 p-4">
                                    <div
                                        v-for="permission in group.matchedPerms"
                                        :key="permission.id"
                                        class="flex items-center justify-between rounded-xl px-3 py-2.5 transition-all hover:bg-gray-50 group/item"
                                    >
                                        <label :for="'perm-' + permission.id" class="flex cursor-pointer items-center gap-4 flex-1">
                                            <GlobalCheckbox
                                                :id="'perm-' + permission.id"
                                                v-model:checked="form.permission_ids"
                                                :value="permission.id"
                                            />
                                            <div>
                                                <span class="block text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">
                                                    {{ permission.name.split('-').join(' ') }}
                                                </span>
                                                <span class="block text-[8px] font-black uppercase text-gray-300 tracking-widest leading-none">{{ permission.name }}</span>
                                            </div>
                                        </label>
                                        <span v-if="form.permission_ids.includes(permission.id)" class="text-[10px] font-black uppercase text-primary/40 tracking-tighter">Authorized</span>
                                    </div>
                                </div>
                                
                                <div class="px-6 py-3 border-t border-gray-50 flex items-center justify-between text-[10px] font-bold text-gray-400">
                                    <span>{{ group.matchedPerms.filter(p => form.permission_ids.includes(p.id)).length }} / {{ group.matchedPerms.length }} ENABLED</span>
                                    <div class="flex gap-0.5">
                                        <div v-for="i in group.matchedPerms.length" :key="i" class="h-1 w-2 rounded-full" :class="i <= group.matchedPerms.filter(p => form.permission_ids.includes(p.id)).length ? 'bg-primary' : 'bg-gray-100'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Ungrouped / Legacy Section -->
                        <div v-if="searchedUngrouped.length > 0" class="mt-12 bg-gray-50/50 rounded-3xl p-8 border border-dashed border-gray-200">
                             <div class="flex items-center gap-3 mb-6 px-2">
                                <GlobalCheckbox
                                    id="group-ungrouped"
                                    :checked="isCategorySelected(searchedUngrouped)"
                                    :indeterminate="isCategoryPartial(searchedUngrouped)"
                                    @update:checked="toggleCategory(searchedUngrouped)"
                                />
                                <h4 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400">Legacy / Uncategorized Gates</h4>
                             </div>
                             <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div
                                    v-for="permission in searchedUngrouped"
                                    :key="permission.id"
                                    class="flex items-center justify-between rounded-2xl bg-white border border-gray-100 p-4 transition-all hover:border-primary/30"
                                >
                                    <label :for="'perm-un-' + permission.id" class="flex cursor-pointer items-center justify-between w-full">
                                        <span class="text-xs font-bold text-gray-600 tracking-tight">{{ permission.name }}</span>
                                        <GlobalCheckbox
                                            :id="'perm-un-' + permission.id"
                                            v-model:checked="form.permission_ids"
                                            :value="permission.id"
                                        />
                                    </label>
                                </div>
                             </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.permission_ids" />
                    </div>

                    <!-- Actions -->
                    <div class="sticky bottom-8 flex items-center justify-between rounded-2xl border border-gray-200 bg-white shadow-2xl p-6 ring-1 ring-black/5">
                        <div class="flex items-center gap-4 text-sm font-bold text-gray-500">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-900 border border-gray-200">
                                {{ form.permission_ids.length }}
                            </span>
                            Permissions Bound
                        </div>
                        <div class="flex gap-3">
                            <AppButton
                                type="button"
                                variant="secondary"
                                @click="form.permission_ids = []"
                            >
                                Clear All
                            </AppButton>
                            <AppButton
                                v-if="can('role-edit')"
                                type="submit"
                                variant="primary"
                                :processing="form.processing"
                                class="min-w-[160px]"
                            >
                                <template #icon>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </template>
                                Apply Changes
                            </AppButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

