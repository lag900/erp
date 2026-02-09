<script setup>
import { computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});

const isEnabled = computed(() => props.settings?.ig_enabled);
const igUrl = computed(() => props.settings?.ig_page_url || 'https://instagram.com/batu.edu.eg');
const embedToken = computed(() => props.settings?.ig_embed_token);

</script>

<template>
    <section v-if="isEnabled" class="bg-white py-24 border-t border-slate-100">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between mb-16 gap-8">
                <div class="max-w-xl text-center md:text-left text-black">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-50 border border-pink-100 text-pink-600 text-[10px] font-bold uppercase tracking-widest mb-4">
                        Social Connect
                    </div>
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-6">Stay Connected on Instagram</h2>
                    <p class="text-slate-600 text-lg leading-relaxed">
                        Follow our daily campus life, achievement stories, and student activities on our official Instagram account.
                    </p>
                </div>
                <a :href="igUrl" target="_blank" class="group flex items-center gap-3 px-10 py-5 bg-gradient-to-tr from-yellow-500 via-pink-600 to-indigo-700 text-white font-black rounded-2xl transition-all shadow-xl hover:shadow-pink-600/30 hover:scale-105 active:scale-95">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.848 0-3.204.012-3.584.07-4.849.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    Follow Official Instagram
                </a>
            </div>

            <!-- Instagram Grid Placeholder / Embed -->
            <div v-if="embedToken" class="instagram-embed-outer" v-html="embedToken"></div>
            
            <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div v-for="i in 6" :key="i" class="aspect-square bg-slate-100 rounded-2xl overflow-hidden relative group cursor-pointer">
                    <img 
                        :src="'https://picsum.photos/seed/' + (i+50) + '/400/400'" 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-80 group-hover:opacity-100"
                        loading="lazy"
                    />
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.instagram-embed-outer :deep(iframe) {
    width: 100% !important;
    border-radius: 1.5rem !important;
    border: 1px solid #f1f5f9 !important;
}
</style>
