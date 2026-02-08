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
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-2">
        <div>
            <h2 class="text-2xl font-black uppercase tracking-tight text-gray-900 leading-tight">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-1 text-sm font-medium text-gray-500">
                {{ subtitle }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <slot name="extra-actions" />
            <Link
                v-if="canAdd && addActionRoute"
                :href="route(addActionRoute)"
                class="btn-primary"
            >
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ addActionText }}
            </Link>
        </div>
    </div>
</template>
