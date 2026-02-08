<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary', // primary, success, danger, warning, secondary, ghost, outline
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xs
    },
    label: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
    href: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    full: {
        type: Boolean,
        default: false,
    },
    rounded: {
        type: Boolean,
        default: false,
    }
});

const isLink = computed(() => !!props.href);
const tag = computed(() => isLink.value ? Link : 'button');

const variantClasses = {
    primary: 'bg-[#3d4adb] text-white hover:bg-[#2c36b8] shadow-sm',
    success: 'bg-[#10b981] text-white hover:bg-[#059669] shadow-sm',
    danger: 'bg-[#ef4444] text-white hover:bg-[#dc2626] shadow-sm',
    warning: 'bg-[#f59e0b] text-white hover:bg-[#d97706] shadow-sm',
    secondary: 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800',
    ghost: 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-900',
    outline: 'bg-transparent text-[#3d4adb] border border-[#3d4adb]/20 hover:border-[#3d4adb] hover:bg-[#3d4adb]/5',
};

const sizeClasses = {
    xs: 'px-3 py-1 text-[11px] h-8',
    sm: 'px-4 py-1.5 text-xs h-9',
    md: 'px-6 py-2.5 text-[0.875rem] h-[42px]',
    lg: 'px-8 py-3 text-base h-[50px]',
};

const classes = computed(() => {
    return [
        'relative inline-flex items-center justify-center font-semibold tracking-tight transition-all duration-200 focus:outline-none active:scale-[0.98] disabled:active:scale-100 select-none whitespace-nowrap rounded-[14px]',
        variantClasses[props.variant] || variantClasses.primary,
        sizeClasses[props.size] || sizeClasses.md,
        props.full ? 'w-full' : '',
        (props.disabled || props.loading || props.processing) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
    ].join(' ');
});
</script>

<template>
    <component
        :is="tag"
        :type="!isLink ? type : null"
        :href="isLink ? href : null"
        :class="classes"
        :disabled="disabled || loading || processing"
    >
        <div v-if="loading || processing" class="absolute inset-0 z-10 bg-inherit rounded-inherit flex items-center justify-center">
            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <div :class="['flex items-center justify-center gap-2', (loading || processing) ? 'opacity-0' : 'opacity-100']">
            <span v-if="$slots.icon" class="flex-shrink-0">
                <slot name="icon" />
            </span>
            
            <span class="leading-none">
                <template v-if="label">{{ label }}</template>
                <slot v-else />
            </span>

            <span v-if="$slots.trailing" class="flex-shrink-0">
                <slot name="trailing" />
            </span>
        </div>
    </component>
</template>

