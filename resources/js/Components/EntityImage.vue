<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'building', // 'building' | 'category' | 'location'
    },
    imageClass: {
        type: String,
        default: '',
    },
});

const hasError = ref(false);

// Reset error state when src changes
watch(() => props.src, () => {
    hasError.value = false;
});

const iconPath = computed(() => {
    if (props.type === 'category') {
        return "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z";
    }
    if (props.type === 'location') {
        return "M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z";
    }
    return "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4";
});

const bgGradient = computed(() => {
    const types = {
        building: 'from-blue-500/20 to-indigo-500/20',
        location: 'from-emerald-500/20 to-teal-500/20',
        category: 'from-purple-500/20 to-pink-500/20',
    };
    return types[props.type] || types.building;
});

const iconColor = computed(() => {
    const types = {
        building: 'text-indigo-400',
        location: 'text-emerald-400',
        category: 'text-purple-400',
    };
    return types[props.type] || types.building;
});
</script>

<template>
    <div class="relative flex items-center justify-center overflow-hidden bg-white border border-gray-100 shadow-inner">
        <!-- Background Pattern/Gradient when no image -->
        <div v-if="!src || hasError" :class="['absolute inset-0 bg-gradient-to-br transition-all duration-500', bgGradient]"></div>
        
        <img
            v-if="src && !hasError"
            :src="src"
            :alt="alt"
            :class="['h-full w-full object-cover transition-all duration-700', imageClass]"
            @error="hasError = true"
        />
        
        <div v-else class="relative z-10 flex h-full w-full items-center justify-center p-6">
             <div class="flex flex-col items-center gap-3">
                <div :class="['rounded-2xl bg-white p-4 shadow-xl ring-1 ring-gray-100 transition-transform duration-500 group-hover:scale-110', iconColor]">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="iconPath" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400 select-none">No Image Available</span>
             </div>
        </div>
        
        <!-- Premium Overlay on Hover -->
        <div v-if="src && !hasError" class="absolute inset-0 bg-black/0 transition-all duration-300 group-hover:bg-black/5"></div>
    </div>
</template>

