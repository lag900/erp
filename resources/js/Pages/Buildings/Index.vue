<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    buildings: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredBuildings = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.buildings;

    return props.buildings.filter((building) => {
        return (
            building.name.toLowerCase().includes(term) ||
            (building.code || '').toLowerCase().includes(term) ||
            (building.location || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const confirmDelete = (building) => {
    window.showConfirm({
        title: 'Decommission Building?',
        message: `Are you sure you want to decommission ${building.name}? This action will cascade to all rooms and assets registered within this facility.`,
        confirmText: 'Decommission',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(building.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('buildings.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Building decommissioned successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to decommission building.');
        }
    });
};
</script>

<template>
    <Head title="Institutions & Buildings" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Institutions & Buildings"
                subtitle="High-level management of university physical structures, academic blocks, and infrastructure."
                addActionText="Register Building"
                addActionRoute="buildings.create"
                :canAdd="can('building-create')"
            />
        </template>

        <div class="py-10">
            <!-- Governance Search & Filtering -->
            <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="relative w-full max-w-xl group">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 transition-colors group-focus-within:text-[#3d4adb]">
                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#3d4adb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <TextInput
                        v-model="search"
                        type="text"
                        class="block w-full rounded-2xl border-slate-200 bg-white pl-11 shadow-soft focus:border-[#3d4adb]/30 focus:ring-[#3d4adb]/20 h-14 text-[15px] font-medium"
                        placeholder="Search institutional buildings by name, code or location..."
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ filteredBuildings.length }} Registered Facilities</span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredBuildings.length === 0"
                class="flex flex-col items-center justify-center rounded-[40px] border border-dashed border-slate-200 bg-white p-24 text-center shadow-soft"
            >
                <div class="rounded-[28px] bg-slate-50 p-8 shadow-inner border border-slate-100 mb-8 text-slate-300">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 tracking-tight">No Buildings Detected</h3>
                <p class="mt-3 text-slate-400 max-w-sm mx-auto font-medium">
                    The institutional database is currently empty. Register academic blocks or administrative offices to begin management.
                </p>
                <div class="mt-10">
                     <AppButton
                        v-if="can('building-create')"
                        :href="route('buildings.create')"
                        label="Register New Building"
                        variant="primary"
                        class="!rounded-xl px-8 shadow-premium"
                    />
                </div>
            </div>

            <!-- Precision Grid -->
            <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <EntityCard
                    v-for="building in filteredBuildings"
                    :key="building.id"
                    :title="building.name"
                    :subtitle="building.location"
                    :image="building.image_url"
                    type="building"
                    :badgeText="building.code"
                >
                    <template #actions>
                        <Link
                            :href="route('buildings.show', building.id)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-[#3d4adb]/10 hover:text-[#3d4adb] border border-slate-100"
                            title="Governance Overview"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </Link>
                        <Link
                            v-if="can('building-edit')"
                            :href="route('buildings.edit', building.id)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-amber-50 hover:text-amber-600 border border-slate-100"
                            title="Edit Record"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('building-delete')"
                            @click="confirmDelete(building)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-600 border border-slate-100"
                            :disabled="deleteForm.processing"
                            title="Decommission Record"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </template>
                </EntityCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
