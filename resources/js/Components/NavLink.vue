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
        ? 'group flex items-center rounded-md bg-secondary px-3 py-2 text-sm font-semibold text-primary transition-all'
        : 'group flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-50 hover:text-gray-900'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
