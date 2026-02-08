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
        default: 'Active'
    }
});
</script>

<template>
    <div class="entity-card group h-full">
        <!-- Image Area -->
        <div class="card-image bg-gray-100">
            <EntityImage
                :src="image"
                :alt="title"
                :type="type"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <!-- Meta Badge -->
            <div v-if="badgeText" class="absolute top-3 left-3">
                <span class="badge-code uppercase tracking-widest">
                    {{ badgeText }}
                </span>
            </div>
        </div>
        
        <div class="card-body">
            <h3 class="title group-hover:text-primary transition-colors">
                {{ title }}
            </h3>
            <div class="subtitle font-bold uppercase tracking-tight" v-if="subtitle">
                {{ subtitle }}
            </div>
            <slot name="subtitle-extra" />
            
            <div class="card-footer">
                <span class="status-badge">{{ statusText }}</span>
                <div class="actions">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </div>
</template>
