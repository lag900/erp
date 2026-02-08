<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import EntityCard from '@/Components/EntityCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredCategories = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.categories;

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(term) ||
        (category.code || '').toLowerCase().includes(term)
    );
});

const deleteForm = useForm({});

const confirmDelete = (category) => {
    window.showConfirm({
        title: 'Decommission Category?',
        message: `Are you sure you want to decommission ${category.name}? This classification will be removed from future registrations.`,
        confirmText: 'Decommission',
        cancelText: 'Cancel',
        onConfirm: () => executeDelete(category.id),
    });
};

const executeDelete = (id) => {
    deleteForm.delete(route('categories.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
             window.showToast('success', 'Category decommissioned successfully.');
        },
        onError: () => {
             window.showToast('error', 'Failed to decommission category.');
        }
    });
};
</script>

<template>
    <Head title="Asset Classifications" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Asset Classifications"
                subtitle="High-level grouping and taxonomy management for institutional property and equipment."
                addActionText="Define Classification"
                addActionRoute="categories.create"
                :canAdd="can('category-create')"
            />
        </template>

        <div class="py-10">
            <!-- Taxonomy Search -->
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
                        placeholder="Search classifications by name, code or technical grouping..."
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ filteredCategories.length }} ACTIVE TAXONOMIES</span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredCategories.length === 0"
                class="flex flex-col items-center justify-center rounded-[40px] border border-dashed border-slate-200 bg-white p-24 text-center shadow-soft"
            >
                <div class="rounded-[28px] bg-slate-50 p-8 shadow-inner border border-slate-100 mb-8 text-slate-300">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Taxonomy Empty</h3>
                <p class="mt-3 text-slate-400 max-w-sm mx-auto font-medium">
                    No asset classifications have been defined. Create primary categories like Electronics, Infrastructure, or Laboratory Assets.
                </p>
                <div class="mt-10">
                     <AppButton
                        v-if="can('category-create')"
                        :href="route('categories.create')"
                        label="Define New Category"
                        variant="primary"
                        class="!rounded-xl px-8 shadow-premium"
                    />
                </div>
            </div>

            <!-- Grid -->
            <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <EntityCard
                    v-for="category in filteredCategories"
                    :key="category.id"
                    :title="category.name"
                    :subtitle="category.code"
                    :image="category.image_url"
                    type="category"
                    :badgeText="category.code"
                >
                    <template #title-extra>
                        <p v-if="category.name_ar" dir="rtl" class="text-sm font-bold text-slate-500 mt-1">
                            {{ category.name_ar }}
                        </p>
                    </template>
                    <template #subtitle-extra>
                        <div v-if="category.department_names" class="mt-3 flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center rounded-lg bg-[#3d4adb]/5 px-2.5 py-1 text-[9px] font-bold text-[#3d4adb] border border-[#3d4adb]/10 uppercase tracking-widest">
                                {{ category.department_names }}
                            </span>
                        </div>
                    </template>
                    <template #actions>
                        <Link
                            v-if="can('category-edit')"
                            :href="route('categories.edit', category.id)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-amber-50 hover:text-amber-600 border border-slate-100"
                            title="Edit Record"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </Link>
                        <button
                            v-if="can('category-delete')"
                            @click="confirmDelete(category)"
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
