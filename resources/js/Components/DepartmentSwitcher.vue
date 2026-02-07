<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const selectedDepartmentName = computed(() => {
    const payload = page.props.departmentContext ?? null;
    if (!payload || !payload.selectedId || !Array.isArray(payload.list)) {
        return null;
    }

    const match = payload.list.find(
        (department) => department.id === payload.selectedId
    );

    return match?.name ?? null;
});
</script>

<template>
    <div class="px-3 py-2">
        <div v-if="selectedDepartmentName" class="group relative">
            <Link
                :href="route('departments.select')"
                class="flex w-full items-center justify-between rounded-lg border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-200 transition-all hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary"
            >
                <div class="flex items-center gap-2 overflow-hidden">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-secondary text-primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="truncate font-semibold text-gray-800">{{ selectedDepartmentName }}</span>
                </div>
                <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
            </Link>
        </div>

        <div v-else>
            <Link
                :href="route('departments.select')"
                class="flex w-full items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-all hover:border-primary-light hover:bg-white hover:text-primary"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Select Department
            </Link>
        </div>
    </div>
</template>
