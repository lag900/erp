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
        ? 'group flex items-center rounded-xl bg-[#3d4adb]/10 px-4 py-2.5 text-[14px] font-bold text-[#3d4adb] shadow-sm shadow-[#3d4adb]/5 transition-all duration-200'
        : 'group flex items-center rounded-xl px-4 py-2.5 text-[14px] font-semibold text-slate-500 transition-all duration-200 hover:bg-slate-50 hover:text-slate-800'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

