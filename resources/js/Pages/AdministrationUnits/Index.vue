<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    units: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);
const deleteForm = useForm({});

const filteredUnits = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.units;

    return props.units.filter((unit) =>
        unit.title.toLowerCase().includes(term) ||
        unit.director_name.toLowerCase().includes(term) ||
        (unit.description || '').toLowerCase().includes(term)
    );
});

const deleteUnit = (id) => {
    if (confirm('Are you sure you want to delete this administration unit?')) {
        deleteForm.delete(route('administration.destroy', id));
    }
};
</script>

<template>
    <Head title="Administration Directory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                        Organization Directory
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage university administration units, faculties, and departments.
                    </p>
                </div>
                <AppButton
                    v-if="can('administration-create')"
                    variant="primary"
                    :href="route('administration.create')"
                >
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </template>
                    Add Unit
                </AppButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-10 max-w-md">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full rounded-2xl border-gray-200 bg-white py-4 pl-12 pr-4 text-sm shadow-sm transition-all focus:border-primary focus:ring-primary h-14"
                            placeholder="Find by title, director, or description..."
                        />
                    </div>
                </div>

                <!-- Grid -->
                <div v-if="filteredUnits.length === 0" class="flex flex-col items-center justify-center rounded-3xl bg-white p-20 text-center shadow-lg border border-gray-100">
                    <div class="mb-6 rounded-full bg-gray-50 p-6">
                        <svg class="h-16 w-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No Administration Units Found</h3>
                    <p class="mt-2 text-gray-500 max-w-sm">
                        Start by creating your first university administration card.
                    </p>
                    <div class="mt-8">
                        <AppButton v-if="can('administration-create')" variant="primary" :href="route('administration.create')">Create New Unit</AppButton>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div 
                        v-for="unit in filteredUnits" 
                        :key="unit.id"
                        class="group relative flex flex-col overflow-hidden rounded-[2.5rem] bg-white transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-primary/10 border border-gray-100"
                    >
                        <!-- Status Badge -->
                        <div class="absolute right-6 top-6 z-10">
                            <span :class="[
                                'inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest shadow-sm ring-1 ring-inset',
                                unit.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-50 text-gray-500 ring-gray-600/20'
                            ]">
                                {{ unit.status }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col p-8">
                            <div class="mb-6">
                                <h3 class="text-2xl font-black leading-tight text-gray-900 group-hover:text-primary transition-colors">
                                    {{ unit.title }}
                                </h3>
                                <div class="mt-4 flex h-1.5 w-12 rounded-full bg-primary/20 group-hover:w-24 transition-all duration-500"></div>
                            </div>
                            
                            <p class="mb-8 text-sm leading-relaxed text-gray-500 line-clamp-3">
                                {{ unit.description || 'No description provided for this administration unit.' }}
                            </p>

                            <!-- Director Info Card -->
                            <div class="mt-auto flex items-center gap-4 rounded-3xl bg-gray-50 p-4 border border-gray-100 group-hover:bg-primary/5 transition-colors">
                                <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-full border-2 border-white shadow-md">
                                    <img :src="unit.image_url" :alt="unit.director_name" class="h-full w-full object-cover" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate text-sm font-bold text-gray-900">{{ unit.director_name }}</h4>
                                    <p class="truncate text-[11px] font-black uppercase tracking-wider text-gray-400">{{ unit.director_title }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Overlay (Only for admins) -->
                        <div v-if="can('administration-edit')" class="flex border-t border-gray-50 bg-gray-50/50 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Link :href="route('administration.edit', unit.id)" class="flex-1 text-center text-xs font-bold text-gray-600 hover:text-primary transition-colors">
                                Edit Profile
                            </Link>
                            <div class="w-px bg-gray-200 mx-4"></div>
                            <button @click="deleteUnit(unit.id)" class="flex-1 text-center text-xs font-bold text-rose-600 hover:text-rose-700 transition-colors">
                                Delete Unit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
