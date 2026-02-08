<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded-xl" v-html="link.label" />
                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded-xl hover:bg-white focus:border-primary focus:text-primary transition-all shadow-sm" :class="{ 'bg-primary text-white font-bold border-primary': link.active, 'bg-gray-50 text-gray-700': !link.active }" :href="link.url" v-html="link.label" preserve-scroll />
            </template>
        </div>
    </div>
</template>
