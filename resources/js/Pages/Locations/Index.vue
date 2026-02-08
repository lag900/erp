<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredLocations = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.locations;

    return props.locations.filter((location) => {
        return (
            location.name.toLowerCase().includes(term) ||
            (location.arabic_name || '').indexOf(term) !== -1 ||
            (location.description || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const confirmDelete = (location) => {
    window.showConfirm({
        title: 'Decommission Zone?',
        message: `Are you sure you want to decommission the ${location.name} zone? All institutional buildings and facilities within this boundary will be affected.`,
        confirmText: 'Decommission',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(location.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('locations.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Zone decommissioned successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to decommission zone.');
        }
    });
};
</script>

<template>
    <Head title="Institutional Infrastructure" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="University Locations"
                subtitle="High-level management of campuses, geographical zones, and primary institution sectors."
                addActionText="Register Zone"
                addActionRoute="locations.create"
                :canAdd="can('location-create')"
            />
        </template>

        <div class="py-10">
            <!-- Infrastructure Search -->
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
                        placeholder="Search institutional zones or primary campuses..."
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ filteredLocations.length }} REGISTERED SECTORS</span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredLocations.length === 0"
                class="flex flex-col items-center justify-center rounded-[40px] border border-dashed border-slate-200 bg-white p-24 text-center shadow-soft"
            >
                <div class="rounded-[28px] bg-slate-50 p-8 shadow-inner border border-slate-100 mb-8 text-slate-300">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 tracking-tight">No Infrastructure Nodes</h3>
                <p class="mt-3 text-slate-400 max-w-sm mx-auto font-medium">
                    The institutional physical hierarchy is empty. Define campus zones or administrative sectors to begin organizing buildings.
                </p>
                <div class="mt-10">
                     <AppButton
                        v-if="can('location-create')"
                        :href="route('locations.create')"
                        label="Define Primary Sector"
                        variant="primary"
                        class="!rounded-xl px-8 shadow-premium"
                    />
                </div>
            </div>

            <!-- Precision Grid -->
            <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <EntityCard
                    v-for="location in filteredLocations"
                    :key="location.id"
                    :title="location.name"
                    :subtitle="location.arabic_name"
                    :image="location.image_url"
                    type="location"
                    badgeText="University Sector"
                >
                    <template #actions>
                         <Link
                            :href="route('locations.show', location.id)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-[#3d4adb]/10 hover:text-[#3d4adb] border border-slate-100"
                            title="Governance Overview"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </Link>
                        <Link
                            v-if="can('location-edit')"
                            :href="route('locations.edit', location.id)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-amber-50 hover:text-amber-600 border border-slate-100"
                            title="Edit Record"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('location-delete')"
                            @click="confirmDelete(location)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-600 border border-slate-100"
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
