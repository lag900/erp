<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex w-full items-center rounded-lg border-l-4 border-indigo-500 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition'
        : 'flex w-full items-center rounded-lg border-l-4 border-transparent px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-800',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
