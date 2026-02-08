<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';

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
        title: 'Delete Building?',
        message: `Are you sure you want to delete ${building.name}? This will remove all associated rooms and assets.`,
        confirmText: 'Delete Building',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(building.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('buildings.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Building deleted successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to delete building.');
        }
    });
};
</script>

<template>
    <Head title="Buildings Management" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Institutions & Buildings"
                subtitle="Manage university physical structures and academic blocks."
                addActionText="Add Building"
                addActionRoute="buildings.create"
                :canAdd="can('building-create')"
            />
        </template>

        <div class="py-8">
            <!-- Search -->
            <div class="mb-8">
                <div class="relative max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <TextInput
                        v-model="search"
                        type="text"
                        class="block w-full rounded-xl border-gray-200 bg-white pl-10 shadow-sm focus:border-primary focus:ring-primary h-11"
                        placeholder="Search buildings..."
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredBuildings.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-200 bg-gray-50/50 p-16 text-center"
            >
                <div class="rounded-2xl bg-white p-4 shadow-soft border border-gray-100 mb-6 text-gray-300">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">No buildings found</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
                    Add academic blocks, administrative offices, or facility buildings to your institutional database.
                </p>
                <div class="mt-8">
                     <Link
                        v-if="can('building-create')"
                        :href="route('buildings.create')"
                        class="btn-primary"
                    >
                        Add Building
                    </Link>
                </div>
            </div>

            <!-- Grid -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
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
                            class="edit-btn"
                            title="View Details"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </Link>
                        <Link
                            v-if="can('building-edit')"
                            :href="route('buildings.edit', building.id)"
                            class="edit-btn"
                            title="Edit Building"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('building-delete')"
                            @click="confirmDelete(building)"
                            class="delete-btn"
                            :disabled="deleteForm.processing"
                            title="Delete Building"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </template>
                </EntityCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
