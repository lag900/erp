<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    levels: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredLevels = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.levels;
    }

    return props.levels.filter((level) => {
        return (
            level.name.toLowerCase().includes(term) ||
            String(level.level_number ?? '').includes(term) ||
            (level.building || '').toLowerCase().includes(term) ||
            (level.location || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteLevel = (levelId) => {
    deleteForm.delete(route('levels.destroy', levelId), {
        onSuccess: (page) => {
             // Inertia returns success even if we redirect back with error flash
             if (page.props.flash?.error) {
                 if (window.showToast) window.showToast('error', page.props.flash.error);
             } else {
                 if (window.showToast) window.showToast('success', 'Level successfully removed from registry');
             }
        },
        onError: () => {
             if (window.showToast) window.showToast('error', 'System Error: Deletion failed due to server constraints.');
        }
    });
};
</script>

<template>
    <Head title="Levels & Floors Management" />

    <AuthenticatedLayout is-fluid>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Levels / Floors
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage floors and levels within university buildings.
                    </p>
                </div>
                <Link
                    v-if="can('level-create')"
                    :href="route('levels.create')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Level
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="w-full">
                <!-- Search & Filters -->
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-2xl">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="block w-full rounded-2xl border-slate-200 pl-11 py-3 text-sm shadow-sm focus:border-primary focus:ring-primary transition-all bg-white"
                            placeholder="Search by level name, number, building or location..."
                        />
                    </div>
                </div>

                <div
                    v-if="filteredLevels.length === 0"
                    class="flex flex-col items-center justify-center rounded-[32px] border-2 border-dashed border-slate-200 bg-white p-20 text-center"
                >
                    <div class="rounded-2xl bg-slate-50 p-4 shadow-inner">
                        <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-bold text-slate-800">No Levels Registered</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-xs mx-auto">
                        We couldn't find any floors matching your search criteria.
                    </p>
                    <div class="mt-8">
                         <Link
                            v-if="can('level-create')"
                            :href="route('levels.create')"
                             class="inline-flex items-center rounded-xl bg-[#3d4adb] px-6 py-3 text-sm font-bold text-white shadow-xl hover:bg-[#2d3aba] transition-all hover:-translate-y-0.5"
                        >
                             <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Register New Level
                        </Link>
                    </div>
                </div>

                <div v-else class="overflow-x-auto rounded-[24px] border border-slate-200 bg-white shadow-premium">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100">Level Identity</th>
                                <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 italic">Index</th>
                                <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100">Building Structure</th>
                                <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100">Geographic Location</th>
                                <th class="px-8 py-5 text-right text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="level in filteredLevels" :key="level.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                                <td class="px-8 py-6 font-bold text-slate-800">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-[#3d4adb]/10 group-hover:text-[#3d4adb] transition-colors">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </div>
                                        {{ level.name }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 border border-slate-200/50">
                                        Lvl {{ level.level_number ?? '0' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-slate-600 font-medium">
                                    {{ level.building || 'Independent Structure' }}
                                </td>
                                <td class="px-8 py-6 text-slate-500 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ level.location || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2 isolate">
                                        <Link
                                            v-if="can('level-edit')"
                                            :href="route('levels.edit', level.id)"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-[#3d4adb]/10 hover:text-[#3d4adb] transition-all duration-300 border border-slate-100"
                                            title="Configure Level"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </Link>
                                        <button
                                            v-if="can('level-delete')"
                                            type="button"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all duration-300 border border-slate-100"
                                            :disabled="deleteForm.processing"
                                            @click="() => { if(confirm('Are you sure you want to delete this level and everything inside? This action is permanent.')) deleteLevel(level.id) }"
                                            title="Decommission"
                                        >
                                            <svg v-if="!deleteForm.processing" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <svg v-else class="h-4 w-4 animate-spin text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
