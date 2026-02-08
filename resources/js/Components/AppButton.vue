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
    pill: {
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
    primary: 'bg-[#1FA6A0] text-white hover:bg-[#168C87] focus:ring-[#1FA6A0]/20 shadow-sm border border-transparent',
    success: 'bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-500/20 shadow-sm border border-transparent',
    danger: 'bg-[#EF4444] text-white hover:bg-[#DC2626] focus:ring-[#EF4444]/20 shadow-sm border border-transparent',
    warning: 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500/20 shadow-sm border border-transparent',
    secondary: 'bg-white text-[#374151] border border-[#E5E7EB] hover:bg-slate-50 hover:border-slate-300 focus:ring-slate-100 shadow-sm font-medium',
    ghost: 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-900 focus:ring-slate-100 border border-transparent',
    outline: 'bg-transparent text-[#1FA6A0] border border-[#1FA6A0]/20 hover:border-[#1FA6A0] hover:bg-[#1FA6A0]/5 focus:ring-[#1FA6A0]/10',
};

const sizeClasses = {
    xs: 'px-3 py-1 text-[11px] h-8',
    sm: 'px-4 py-1.5 text-xs h-9',
    md: 'px-[18px] py-2.5 text-[14.5px] h-[42px]',
    lg: 'px-6 py-3 text-base h-12',
};

const classes = computed(() => {
    return [
        'relative inline-flex items-center justify-center font-medium font-["Inter"] tracking-tight transition-all duration-200 focus:outline-none focus:ring-4 active:scale-[0.98] disabled:active:scale-100 select-none whitespace-nowrap overflow-hidden',
        variantClasses[props.variant] || variantClasses.primary,
        sizeClasses[props.size] || sizeClasses.md,
        props.pill ? 'rounded-full' : (props.rounded ? 'rounded-2xl' : 'rounded-[10px]'),
        props.full ? 'w-full' : '',
        (props.disabled || props.loading || props.processing) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer',
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
        <!-- Overlay for Loading/Disabled state to prevent interaction -->
        <div v-if="loading || processing" class="absolute inset-0 z-10 bg-inherit pointer-events-none flex items-center justify-center">
            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <div :class="['flex items-center justify-center gap-2 transition-opacity duration-200', (loading || processing) ? 'opacity-0' : 'opacity-100']">
            <!-- Icon Slot (Left) -->
            <span v-if="$slots.icon" class="flex-shrink-0 transition-transform duration-200 translate-y-[0.5px]">
                <slot name="icon" />
            </span>
            
            <!-- Default Content / Label -->
            <span class="leading-none flex items-center h-full">
                <template v-if="label">{{ label }}</template>
                <slot v-else />
            </span>

            <!-- Trailing Icon Slot -->
            <span v-if="$slots.trailing" class="flex-shrink-0 transition-transform duration-200 translate-y-[0.5px]">
                <slot name="trailing" />
            </span>
        </div>

        <!-- Hover State Ripple-like Effect -->
        <div class="absolute inset-0 bg-white/0 transition-colors duration-200 group-hover:bg-white/5 pointer-events-none"></div>
    </component>
</template>
