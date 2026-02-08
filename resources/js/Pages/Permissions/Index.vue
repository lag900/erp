<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    groups: Array,
    ungrouped: Array,
    roles: Array,
});

const search = ref('');
const deleteForm = useForm({});
const page = usePage();
const userPermissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => userPermissions.value.includes(permission);

// Selection & Bulk Actions
const selectedPermissions = ref([]);
const isBulkActionModalOpen = ref(false);
const bulkType = ref('assign'); // assign, remove, move
const bulkForm = useForm({
    permission_ids: [],
    role_ids: [],
    permission_group_id: '',
});

const toggleSelection = (id) => {
    const index = selectedPermissions.value.indexOf(id);
    if (index > -1) {
        selectedPermissions.value.splice(index, 1);
    } else {
        selectedPermissions.value.push(id);
    }
};

const toggleModuleSelection = (perms) => {
    const permIds = perms.map(p => p.id);
    const allSelected = permIds.every(id => selectedPermissions.value.includes(id));
    
    if (allSelected) {
        selectedPermissions.value = selectedPermissions.value.filter(id => !permIds.includes(id));
    } else {
        const newIds = permIds.filter(id => !selectedPermissions.value.includes(id));
        selectedPermissions.value = [...selectedPermissions.value, ...newIds];
    }
};

const openBulkModal = (type, singleId = null) => {
    if (singleId) {
        selectedPermissions.value = [singleId];
    }
    bulkType.value = type;
    bulkForm.permission_ids = [...selectedPermissions.value];
    bulkForm.role_ids = [];
    bulkForm.permission_group_id = '';
    isBulkActionModalOpen.value = true;
};

const submitBulkAction = () => {
    const routeNames = {
        assign: 'permissions.bulk-assign',
        remove: 'permissions.bulk-remove',
        move: 'permissions.bulk-move'
    };
    
    const routeName = routeNames[bulkType.value];

    bulkForm.post(route(routeName), {
        onSuccess: () => {
            isBulkActionModalOpen.value = false;
            selectedPermissions.value = [];
        }
    });
};

// Drag & Drop State
const draggingPermission = ref(null);

// Group Modal State
const isGroupModalOpen = ref(false);
const editingGroup = ref(null);
const groupForm = useForm({
    name: '',
    description: '',
    icon: 'shield-check',
});

// Permission Modal State
const isPermissionModalOpen = ref(false);
const permissionForm = useForm({
    name: '',
    permission_group_id: '',
    description: '',
    status: true,
    is_sidebar_item: false,
    sidebar_label: '',
    route_name: '',
    icon: 'cube',
    sort_order: 0,
});

const openGroupModal = (group = null) => {
    editingGroup.value = group;
    if (group) {
        groupForm.name = group.name;
        groupForm.description = group.description;
        groupForm.icon = group.icon;
    } else {
        groupForm.reset();
    }
    isGroupModalOpen.value = true;
};

const openPermissionModal = (groupId = null) => {
    permissionForm.reset();
    if (groupId) {
        permissionForm.permission_group_id = groupId;
    }
    isPermissionModalOpen.value = true;
};

const submitGroupForm = () => {
    if (editingGroup.value) {
        groupForm.put(route('permission-groups.update', editingGroup.value.id), {
            onSuccess: () => {
                isGroupModalOpen.value = false;
                editingGroup.value = null;
            }
        });
    } else {
        groupForm.post(route('permission-groups.store'), {
            onSuccess: () => {
                isGroupModalOpen.value = false;
            }
        });
    }
};

const submitPermissionForm = () => {
    permissionForm.post(route('permissions.store'), {
        onSuccess: () => {
            isPermissionModalOpen.value = false;
        }
    });
};

// Auto-generate key from name
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
    if (!permissionForm.name) return;
    permissionForm.name = slugify(permissionForm.name);
};

const deleteGroup = (group) => {
    if (confirm(`Purge "${group.name}" module? Permissions will be preserved but ungrouped.`)) {
        router.delete(route('permission-groups.destroy', group.id));
    }
};

const filteredGroups = computed(() => {
    const term = search.value.trim().toLowerCase();
    return props.groups.map(group => {
        const matchedPerms = group.permissions.filter(p => 
            p.name.toLowerCase().includes(term) || 
            (p.description && p.description.toLowerCase().includes(term))
        );
        return { ...group, matchedPerms };
    }).filter(group => 
        group.matchedPerms.length > 0 || 
        group.name.toLowerCase().includes(term)
    );
});

const onDragStart = (permission) => {
    draggingPermission.value = permission;
};

const onDragOver = (event) => {
    event.preventDefault();
};

const onDrop = (group) => {
    if (!draggingPermission.value || draggingPermission.value.permission_group_id === group.id) return;
    
    const permissionId = draggingPermission.value.id;
    const groupId = group.id;

    router.patch(route('permissions.move', permissionId), {
        permission_group_id: groupId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            draggingPermission.value = null;
        }
    });
};

const deletePermission = (permissionId) => {
    if (confirm('Permanently revoke this security gate?')) {
        deleteForm.delete(route('permissions.destroy', permissionId));
    }
};

const formatKey = (name) => {
    return name.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const isModuleAllSelected = (perms) => {
    return perms.length > 0 && perms.every(p => selectedPermissions.value.includes(p.id));
};
</script>

<template>
    <Head title="System Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-gray-900">
                        Security Access Matrix
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Configure organizational functional boundaries and security modules.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton
                        v-if="can('permission-create')"
                        variant="secondary"
                        @click="openGroupModal()"
                    >
                        <template #icon>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        </template>
                        New Module
                    </AppButton>
                    <AppButton
                        v-if="can('permission-create')"
                        variant="primary"
                        @click="openPermissionModal()"
                    >
                        <template #icon>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </template>
                        New Permission
                    </AppButton>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Advanced Search & Statistics -->
                <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative max-w-xl w-full">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full border-gray-100 bg-white pl-12 h-[52px] rounded-2xl shadow-soft transition-all focus:ring-[#1FA6A0]/10"
                            placeholder="Search security keys, modules, or functional descriptions..."
                        />
                    </div>
                    
                    <div class="flex gap-4 overflow-x-auto pb-2 lg:pb-0">
                        <div class="flex flex-shrink-0 items-center gap-3 rounded-xl bg-white px-5 py-3 border border-gray-50 shadow-soft">
                            <div class="h-3 w-3 rounded-full bg-[#1FA6A0] animate-pulse"></div>
                            <span class="text-xs font-semibold uppercase tracking-tight text-gray-500">{{ groups.length }} Modules</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredGroups.length === 0 && ungrouped.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white p-24 text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-50 text-gray-300">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-gray-900 uppercase">Matrix Reset</h3>
                    <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">The security definitions are currently empty or filtered out of scope.</p>
                </div>

                <!-- Sticky Bulk Action Bar -->
                <Transition
                    enter-active-class="transition ease-out duration-300 transform"
                    enter-from-class="translate-y-full opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition ease-in duration-200 transform"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-full opacity-0"
                >
                    <div v-if="selectedPermissions.length > 0" class="fixed bottom-8 left-1/2 z-[100] -translate-x-1/2 w-full max-w-2xl px-4">
                        <div class="flex items-center justify-between rounded-2xl bg-[#1F2937] px-8 py-5 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl">
                            <div class="flex items-center gap-4">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#1FA6A0] text-[10px] font-bold text-white shadow-lg">
                                    {{ selectedPermissions.length }}
                                </span>
                                <span class="text-xs font-semibold text-white tracking-wide">Gates selected for action</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <AppButton variant="ghost" size="sm" class="text-gray-400 hover:text-white" @click="selectedPermissions = []">
                                    Clear
                                </AppButton>
                                <div class="h-4 w-px bg-white/10 mx-2"></div>
                                <AppButton variant="primary" size="sm" @click="openBulkModal('assign')">
                                    <template #icon>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                    </template>
                                    Map To Identity
                                </AppButton>
                                <AppButton variant="danger" size="sm" @click="openBulkModal('remove')">
                                    <template #icon>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
                                    </template>
                                    Strip Authorization
                                </AppButton>
                                <AppButton variant="secondary" size="sm" @click="openBulkModal('move')">
                                    <template #icon>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    </template>
                                    Relocate Gates
                                </AppButton>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Module Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="group in filteredGroups" 
                        :key="group.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft transition-all duration-300 hover:shadow-premium hover:border-[#1FA6A0]/20"
                        @dragover="onDragOver"
                        @drop="onDrop(group)"
                    >
                        <!-- Group Header -->
                        <div class="border-b border-gray-50 bg-gray-50/30 px-6 py-5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <GlobalCheckbox
                                        :id="'group-' + group.id"
                                        :checked="isModuleAllSelected(group.matchedPerms)"
                                        @update:checked="toggleModuleSelection(group.matchedPerms)"
                                    />
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 text-primary">
                                         <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                         </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 leading-none mb-1">Functional Module</h3>
                                    <h4 class="text-base font-semibold text-slate-800 tracking-tight">{{ group.name }}</h4>
                                </div>
                            
                            <div class="flex items-center gap-2">
                                <AppButton 
                                    v-if="can('permission-create')" 
                                    variant="secondary" 
                                    size="xs"
                                    @click="openPermissionModal(group.id)" 
                                    class="!bg-white !px-2 shadow-sm border border-slate-50"
                                    title="Add Gate to Module"
                                >
                                    <template #icon>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                    </template>
                                </AppButton>
                                <span class="rounded-xl bg-white px-3 py-1 text-[10px] font-black uppercase text-gray-400 border border-gray-100 shadow-sm leading-none">
                                    {{ group.permissions.length }} Gates
                                </span>
                            </div>
                        </div>

                        <!-- Permissions List -->
                        <div class="p-5 flex-1 overflow-y-auto max-h-[450px] scrollbar-hide">
                            <ul class="space-y-3">
                                <li 
                                    v-for="permission in group.matchedPerms" 
                                    :key="permission.id"
                                    draggable="true"
                                    @dragstart="onDragStart(permission)"
                                    class="group/item relative flex items-center justify-between rounded-xl border border-transparent bg-[#fcfdfe] px-4 py-4 transition-all cursor-grab active:cursor-grabbing hover:bg-white hover:border-gray-100 hover:shadow-md"
                                    :class="{ 'ring-2 ring-[#1FA6A0] bg-[#1FA6A0]/[0.02] border-[#1FA6A0]/10 shadow-lg': selectedPermissions.includes(permission.id) }"
                                >
                                    <div class="flex items-center gap-4">
                                        <GlobalCheckbox
                                            :id="'perm-' + permission.id"
                                            :checked="selectedPermissions.includes(permission.id)"
                                            @update:checked="toggleSelection(permission.id)"
                                        />
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-gray-400 shadow-sm border border-gray-50 group-hover/item:text-[#1FA6A0] transition-all duration-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="text-sm font-semibold text-slate-700 tracking-tight leading-none">
                                                    {{ formatKey(permission.name) }}
                                                </p>
                                                <span v-if="!permission.status" class="px-1.5 py-0.5 bg-slate-100 text-[8px] font-bold text-slate-400 rounded-lg">Inactive</span>
                                            </div>
                                            <p class="text-[10px] font-medium text-slate-400 tracking-wide leading-none">{{ permission.name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-all duration-300">
                                        <AppButton
                                            variant="ghost"
                                            size="xs"
                                            class="!p-1.5 hover:!bg-[#1FA6A0]/10 hover:!text-[#1FA6A0]"
                                            @click="openBulkModal('assign', permission.id)"
                                            title="Quick Map to Identity"
                                        >
                                            <template #icon>
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                            </template>
                                        </AppButton>
                                        <AppButton
                                            v-if="can('permission-delete') && permission.name !== 'feature-toggle'"
                                            variant="ghost"
                                            size="xs"
                                            class="!p-1.5 hover:!bg-rose-50 hover:!text-rose-500"
                                            @click="deletePermission(permission.id)"
                                        >
                                            <template #icon>
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </template>
                                        </AppButton>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Actions & Modifiers -->
                        <div class="px-8 py-5 border-t border-gray-50 flex items-center justify-between">
                             <div class="flex gap-1.5">
                                 <div @click="openGroupModal(group)" class="p-2 cursor-pointer text-gray-300 hover:text-primary transition-colors bg-gray-50 rounded-xl hover:bg-white hover:shadow-sm ring-1 ring-inset ring-transparent hover:ring-gray-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                 </div>
                                 <div @click="deleteGroup(group)" class="p-2 cursor-pointer text-gray-300 hover:text-rose-500 transition-colors bg-gray-50 rounded-xl hover:bg-white hover:shadow-sm ring-1 ring-inset ring-transparent hover:ring-gray-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                 </div>
                             </div>
                             <div class="flex items-center gap-1.5">
                                 <div v-for="i in Math.min(group.permissions.length, 5)" :key="i" class="h-1.5 w-1.5 rounded-full bg-[#1FA6A0]/20"></div>
                                <div v-if="group.permissions.length > 5" class="text-[8px] font-black text-gray-300 uppercase">+{{ group.permissions.length - 5 }}</div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Ungrouped Content -->
                <div v-if="ungrouped.length > 0" class="mt-16 bg-[#F8FAFC] rounded-2xl p-10 border border-dashed border-gray-200">
                     <div class="mb-10 flex items-center justify-between px-2">
                         <div>
                            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Legacy Repository</h3>
                            <p class="text-sm font-medium text-gray-500">Uncategorized gates awaiting module mapping.</p>
                         </div>
                         <span class="px-5 py-2 bg-white border border-gray-100 shadow-sm rounded-xl text-xs font-semibold text-gray-500 uppercase tracking-widest">{{ ungrouped.length }} Keys</span>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="permission in ungrouped" 
                            :key="permission.id"
                            draggable="true"
                            @dragstart="onDragStart(permission)"
                            class="group/legacy flex items-center justify-between p-5 rounded-2xl bg-white border border-gray-100 shadow-soft cursor-grab active:cursor-grabbing hover:border-[#1FA6A0]/40 hover:shadow-premium transition-all duration-300"
                        >
                            <div class="flex items-center gap-3">
                                <GlobalCheckbox
                                    :id="'perm-un-' + permission.id"
                                    :checked="selectedPermissions.includes(permission.id)"
                                    @update:checked="toggleSelection(permission.id)"
                                />
                                <span class="text-sm font-bold text-gray-700 tracking-tight">{{ formatKey(permission.name) }}</span>
                            </div>
                            <div class="h-8 w-8 rounded-xl bg-gray-50 flex items-center justify-center text-gray-300 group-hover/legacy:bg-primary/10 group-hover/legacy:text-primary transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- Bulk Action Modal -->
        <Modal :show="isBulkActionModalOpen" @close="isBulkActionModalOpen = false" max-width="lg">
            <div class="p-10">
                <div class="mb-8 flex items-center justify-between border-b border-gray-50 pb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl" 
                             :class="bulkType === 'assign' || bulkType === 'move' ? 'bg-primary/10 text-primary' : 'bg-rose-50 text-rose-600'">
                            <svg v-if="bulkType === 'assign'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            <svg v-else-if="bulkType === 'remove'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
                            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 tracking-tight">
                                {{ bulkType === 'assign' ? 'Map Authorizations' : (bulkType === 'move' ? 'Relocate Gates' : 'Strip Authorizations') }}
                            </h3>
                            <p class="text-sm font-medium text-slate-500">Targeting {{ selectedPermissions.length }} selected security gates.</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitBulkAction" class="space-y-8">
                    <!-- Role Selection (Assign/Remove) -->
                    <div v-if="['assign', 'remove'].includes(bulkType)" class="space-y-4">
                        <InputLabel value="Select Target Identities (Roles)" class="text-xs font-black uppercase tracking-widest text-primary" />
                        <div class="grid grid-cols-2 gap-3">
                            <label 
                                v-for="role in roles" 
                                :key="role.id"
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-[1.25rem] border border-gray-100 bg-gray-50/50 p-4 transition-all hover:border-primary/30"
                                :class="{ 'ring-2 ring-primary bg-primary/[0.02] border-primary/20': bulkForm.role_ids.includes(role.id) }"
                            >
                                <div class="flex items-center gap-3">
                                    <GlobalCheckbox
                                        :id="'bulk-role-' + role.id"
                                        v-model:checked="bulkForm.role_ids"
                                        :value="role.id"
                                    />
                                    <span class="text-sm font-bold text-gray-700 uppercase tracking-tight">{{ role.name }}</span>
                                </div>
                                <span class="px-2 py-0.5 bg-white border border-gray-100 rounded-lg text-[10px] font-black text-gray-400 uppercase tracking-tighter">
                                    {{ role.permissions_count }} Gates
                                </span>
                            </label>
                        </div>
                        <InputError :message="bulkForm.errors.role_ids" />
                    </div>

                    <!-- Module Selection (Move) -->
                    <div v-if="bulkType === 'move'" class="space-y-4">
                        <InputLabel for="bulk_target_group" value="Target Security Module" class="text-xs font-black uppercase tracking-widest text-primary" />
                        <select
                            id="bulk_target_group"
                            v-model="bulkForm.permission_group_id"
                            class="block w-full border-gray-100 bg-gray-50 px-4 py-4 rounded-3xl text-sm font-bold transition-all focus:border-primary focus:bg-white focus:ring-primary"
                            required
                        >
                            <option value="" disabled>Select Target Module...</option>
                            <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                        <InputError :message="bulkForm.errors.permission_group_id" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                        <AppButton variant="secondary" type="button" @click="isBulkActionModalOpen = false">
                            Cancel
                        </AppButton>
                        <AppButton 
                            :variant="bulkType === 'assign' || bulkType === 'move' ? 'primary' : 'danger'" 
                            type="submit" 
                            :processing="bulkForm.processing"
                            class="min-w-[140px]"
                        >
                            {{ bulkType === 'assign' ? 'Confirm Logic' : (bulkType === 'move' ? 'Relocate Gates' : 'Revoke Access') }}
                        </AppButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Global Module Management Modal -->
        <Modal :show="isGroupModalOpen" @close="isGroupModalOpen = false" max-width="lg">
            <div class="p-8">
                <div class="mb-8 flex items-center justify-between border-b border-gray-50 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">
                            {{ editingGroup ? 'Refine Module' : 'Initialize Module' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">Define a logical container for related security keys.</p>
                    </div>
                    <AppButton variant="ghost" size="xs" pill @click="isGroupModalOpen = false" class="!px-1.5 h-8 w-8 hover:!bg-slate-100">
                        <template #icon>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </template>
                    </AppButton>
                </div>

                <form @submit.prevent="submitGroupForm" class="space-y-6">
                    <div class="space-y-2">
                        <InputLabel for="group_name" value="Module Title" />
                        <TextInput
                            id="group_name"
                            v-model="groupForm.name"
                            type="text"
                            class="block w-full"
                            placeholder="e.g. Asset Intelligence"
                            required
                        />
                        <InputError :message="groupForm.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="group_desc" value="Purpose / Description" />
                        <textarea
                            id="group_desc"
                            v-model="groupForm.description"
                            class="block w-full rounded-2xl border-gray-100 bg-gray-50 px-4 py-3 text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary h-32"
                            placeholder="Briefly describe what this module controls..."
                        ></textarea>
                        <InputError :message="groupForm.errors.description" />
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <AppButton variant="secondary" type="button" @click="isGroupModalOpen = false">
                            Cancel
                        </AppButton>
                        <AppButton variant="primary" type="submit" :processing="groupForm.processing">
                            {{ editingGroup ? 'Synchronize' : 'Initialize' }}
                        </AppButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Permission Creation Modal -->
        <Modal :show="isPermissionModalOpen" @close="isPermissionModalOpen = false" max-width="lg">
            <div class="p-8">
                <div class="mb-8 flex items-center justify-between border-b border-gray-50 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">
                            Define Security Gate
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">Establish a new logical access key for the ERP matrix.</p>
                    </div>
                    <button @click="isPermissionModalOpen = false" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submitPermissionForm" class="space-y-6">
                    <div class="space-y-2">
                        <InputLabel for="perm_name_input" value="Security Gate Label" />
                        <TextInput
                            id="perm_name_input"
                            v-model="permissionForm.name"
                            @input="updateKey"
                            type="text"
                            class="block w-full"
                            placeholder="e.g. Bulk Asset Upload"
                            required
                            autofocus
                        />
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mt-1">Key: <span class="text-primary">{{ permissionForm.name || '...' }}</span></p>
                        <InputError :message="permissionForm.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="perm_group" value="Module Assignment" />
                        <select
                            id="perm_group"
                            v-model="permissionForm.permission_group_id"
                            class="block w-full border-gray-100 bg-gray-50 px-4 py-3 rounded-2xl text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary"
                            required
                        >
                            <option value="" disabled>Select Module...</option>
                            <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                        <InputError :message="permissionForm.errors.permission_group_id" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="perm_desc" value="Documentation / Scope" />
                        <textarea
                            id="perm_desc"
                            v-model="permissionForm.description"
                            class="block w-full rounded-2xl border-gray-100 bg-gray-50 px-4 py-3 text-sm transition-all focus:border-primary focus:bg-white focus:ring-primary h-24"
                            placeholder="What actions does this gate permit?"
                        ></textarea>
                        <InputError :message="permissionForm.errors.description" />
                    </div>

                    <div class="flex items-center gap-3">
                        <GlobalCheckbox
                            id="perm_status"
                            v-model:checked="permissionForm.status"
                        />
                        <label for="perm_status" class="text-sm font-bold text-gray-700">Active Status</label>
                    </div>

                    <div class="space-y-4 border-t border-gray-50 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Sidebar Presentation</h4>
                                <p class="text-[10px] text-gray-400 font-medium">Configure how this gate appears in the main navigation.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <GlobalCheckbox
                                    id="is_sidebar_item"
                                    v-model:checked="permissionForm.is_sidebar_item"
                                />
                            </div>
                        </div>

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="permissionForm.is_sidebar_item" class="space-y-4 rounded-2xl bg-gray-50/50 p-4 ring-1 ring-inset ring-gray-100">
                                <div>
                                    <InputLabel for="sidebar_label" value="Navigation Label" />
                                    <TextInput id="sidebar_label" v-model="permissionForm.sidebar_label" type="text" class="block w-full text-xs mt-1" placeholder="e.g. Asset Registry" />
                                    <InputError :message="permissionForm.errors.sidebar_label" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="route_name" value="Target Route" />
                                        <TextInput id="route_name" v-model="permissionForm.route_name" type="text" class="block w-full text-xs mt-1" placeholder="e.g. assets.index" />
                                        <InputError :message="permissionForm.errors.route_name" />
                                    </div>
                                    <div>
                                        <InputLabel for="perm_icon" value="Visual Icon Key" />
                                        <select id="perm_icon" v-model="permissionForm.icon" class="block w-full border-gray-100 bg-white px-3 py-2 rounded-xl text-xs transition-all focus:border-primary focus:ring-primary mt-1">
                                            <option value="cube">Cube (Assets)</option>
                                            <option value="map-pin">Map Pin (Locations)</option>
                                            <option value="office-building">Building</option>
                                            <option value="users">Users</option>
                                            <option value="shield-check">Shield</option>
                                            <option value="lock-closed">Lock</option>
                                            <option value="newspaper">News</option>
                                            <option value="cog">Cog</option>
                                            <option value="collection">Collection</option>
                                            <option value="adjustments">Adjustments</option>
                                        </select>
                                        <InputError :message="permissionForm.errors.icon" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel for="sort_order" value="Sort Priority" />
                                    <input id="sort_order" type="number" v-model="permissionForm.sort_order" class="block w-full border-gray-100 bg-white px-3 py-2 rounded-xl text-xs transition-all focus:border-primary focus:ring-primary mt-1" />
                                    <InputError :message="permissionForm.errors.sort_order" />
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <AppButton variant="secondary" type="button" @click="isPermissionModalOpen = false">
                            Cancel
                        </AppButton>
                        <AppButton variant="primary" type="submit" :processing="permissionForm.processing">
                            Establish Gate
                        </AppButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
