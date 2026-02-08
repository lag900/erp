<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const hasMultiple = computed(() => {
    return page.props.hasMultipleDepartments !== false; // Default to true if not set
});

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
    <div class="px-1 py-1">
        <div v-if="selectedDepartmentName" class="group relative">
            <Link
                v-if="hasMultiple"
                :href="route('departments.select')"
                class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 hover:border-slate-300 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#3d4adb]/20 group"
            >
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#3d4adb]/5 text-[#3d4adb] border border-[#3d4adb]/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="truncate font-bold text-slate-700 tracking-tight">{{ selectedDepartmentName }}</span>
                </div>
                <svg class="ml-2 h-4 w-4 shrink-0 text-slate-300 group-hover:text-slate-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
            </Link>
            
            <div v-else class="flex w-full items-center justify-between rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm cursor-default">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-200/50 text-slate-500 border border-slate-200/50">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <span class="truncate font-bold text-slate-600 tracking-tight">{{ selectedDepartmentName }}</span>
                </div>
            </div>
        </div>

        <div v-else>
            <Link
                :href="route('departments.select')"
                class="flex w-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-4 text-[13px] font-bold text-slate-500 transition-all hover:border-[#3d4adb]/30 hover:bg-white hover:text-[#3d4adb]"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Select Context
            </Link>
        </div>
    </div>
</template>

