<script setup>
import { Link } from '@inertiajs/vue3';
import EntityImage from '@/Components/EntityImage.vue';

defineProps({
    title: String,
    subtitle: String,
    image: String,
    type: String, // 'building' | 'category' | 'location'
    badgeText: String,
    statusText: {
        type: String,
        default: 'Operational'
    }
});
</script>

<template>
    <div class="group relative flex flex-col overflow-hidden rounded-[24px] border border-slate-200/60 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-premium">
        <!-- Image Area -->
        <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-50">
            <EntityImage
                :src="image"
                :alt="title"
                :type="type"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <!-- Meta Badge Overlay -->
            <div v-if="badgeText" class="absolute top-4 left-4">
                <span class="inline-flex items-center rounded-lg bg-white/90 px-2.5 py-1 text-[10px] font-bold text-slate-800 shadow-sm backdrop-blur-md border border-white/20 uppercase tracking-[0.1em]">
                    {{ badgeText }}
                </span>
            </div>
            
            <!-- Hover Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
        
        <div class="flex flex-1 flex-col p-5">
            <h3 class="text-[17px] font-bold text-slate-800 tracking-tight leading-snug group-hover:text-[#3d4adb] transition-colors line-clamp-1">
                {{ title }}
            </h3>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-widest" v-if="subtitle">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ subtitle }}
            </div>
            
            <div class="mt-auto pt-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ statusText }}</span>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <slot name="actions" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
