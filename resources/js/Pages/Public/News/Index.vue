<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Landing/Header.vue';
import Footer from '@/Components/Landing/Footer.vue';
import Pagination from '@/Components/Pagination.vue'; 
import SocialSection from '@/Components/Landing/SocialSection.vue';

const props = defineProps({
    news: Object,
    mediaSettings: {
        type: Object,
        default: () => ({})
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head>
        <title>University News | Borg El Arab Technological University</title>
        <meta name="description" content="Stay updated with the latest news, events, and academic breakthroughs at Borg El Arab Technological University (BATU)." />
    </Head>

    <div class="min-h-screen bg-slate-50">
        <Header />

        <!-- Hero Header -->
        <header class="bg-slate-900 pt-32 pb-20 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 left-0 w-96 h-96 bg-indigo-500 rounded-full blur-[120px] -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>
            </div>
            
            <div class="container mx-auto px-4 lg:px-8 relative z-10 text-center">
                <nav class="flex justify-center items-center gap-2 mb-6 text-indigo-400 text-xs font-bold uppercase tracking-widest">
                    <Link :href="route('home')" class="hover:text-white transition-colors">Home</Link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-white/60">News & Updates</span>
                </nav>
                <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">University News</h1>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
                    The latest stories from our campus, research labs, and global partnerships.
                </p>
            </div>
        </header>

        <main class="container mx-auto px-4 lg:px-8 -mt-10 mb-24 relative z-20">
            <!-- Search & Filters (Optional) -->
            <div class="bg-white rounded-3xl p-4 shadow-xl shadow-slate-200/50 mb-12 border border-slate-100 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex gap-2">
                    <button class="px-6 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all">All News</button>
                    <button class="px-6 py-2.5 text-slate-500 hover:bg-slate-50 text-xs font-bold rounded-xl transition-all">Academic</button>
                    <button class="px-6 py-2.5 text-slate-500 hover:bg-slate-50 text-xs font-bold rounded-xl transition-all">Industrial</button>
                </div>
                <div class="relative w-full md:w-auto">
                    <input type="text" placeholder="Search news..." class="w-full md:w-64 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 rounded-xl px-4 py-2 text-sm transition-all" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <article v-for="item in news.data" :key="item.id" class="group bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-700 flex flex-col">
                    <div class="relative h-64 overflow-hidden">
                        <img 
                            :src="item.image_url || 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80'" 
                            :alt="item.title"
                            class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div class="absolute top-6 left-6 px-4 py-2 bg-white/95 backdrop-blur-md rounded-xl text-[10px] font-black text-slate-900 shadow-xl uppercase tracking-widest">
                            {{ item.category || 'Announcement' }}
                        </div>
                    </div>
                    
                    <div class="p-8 flex-1 flex flex-col">
                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ formatDate(item.publish_date) }}
                        </div>
                        <h2 class="text-xl font-black text-slate-900 mb-4 leading-snug group-hover:text-indigo-600 transition-colors">
                            {{ item.title }}
                        </h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3">
                            {{ item.description }}
                        </p>
                        
                        <div class="mt-auto pt-8 border-t border-slate-50">
                            <Link :href="route('news.show', item.id)" class="inline-flex items-center gap-2 px-8 py-3.5 bg-slate-900 text-white text-sm font-bold rounded-2xl hover:bg-indigo-600 transition-all active:scale-95 shadow-xl shadow-slate-900/10">
                                Read Article
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-if="news.data.length === 0" class="py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-100 max-w-4xl mx-auto mt-12">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <p class="text-slate-400 font-bold">No news articles published yet.</p>
                <Link :href="route('home')" class="inline-block mt-8 text-indigo-600 font-bold text-sm hover:underline">Return Home</Link>
            </div>

            <!-- Pagination -->
            <div class="mt-16 flex justify-center">
                <Link 
                    v-for="(link, k) in news.links" 
                    :key="k" 
                    :href="link.url" 
                    v-html="link.label"
                    class="px-4 py-2 text-sm font-bold border-2 mx-1 transition-all rounded-xl"
                    :class="{ 
                        'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-600/20': link.active,
                        'bg-white border-slate-100 text-slate-600 hover:border-indigo-600 hover:text-indigo-600': !link.active && link.url,
                        'opacity-50 cursor-not-allowed border-slate-100 text-slate-300': !link.url
                    }"
                ></Link>
            </div>
        </main>

        <!-- Instagram Integration Section -->
        <SocialSection :settings="mediaSettings" />

        <Footer />
    </div>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
