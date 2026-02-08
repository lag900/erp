<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    subtitle: String,
    addActionText: String,
    addActionRoute: String,
    canAdd: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-1">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-800 leading-tight">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-1.5 text-[14px] font-medium text-slate-400 max-w-2xl">
                {{ subtitle }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <slot name="extra-actions" />
            <Link
                v-if="canAdd && addActionRoute"
                :href="route(addActionRoute)"
                class="btn-primary !h-[42px] !rounded-xl"
            >
                <svg class="mr-2 -ml-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ addActionText }}
            </Link>
        </div>
    </div>
</template>

