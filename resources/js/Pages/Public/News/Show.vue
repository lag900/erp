<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Landing/Header.vue';
import SocialSection from '@/Components/Landing/SocialSection.vue';
import Footer from '@/Components/Landing/Footer.vue';

const props = defineProps({
    article: Object,
    relatedNews: Array,
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
        <title>{{ article.title }} | University News</title>
        <meta name="description" :content="article.description" />
    </Head>

    <div class="min-h-screen bg-white">
        <Header />

        <article class="pt-24 pb-24">
            <!-- Breadcrumbs -->
            <div class="container mx-auto px-4 lg:px-8 pt-8 mb-12">
                <nav class="flex items-center gap-2 text-slate-400 text-xs font-bold uppercase tracking-widest">
                    <Link :href="route('home')" class="hover:text-indigo-600 transition-colors">Home</Link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <Link :href="route('news.index')" class="hover:text-indigo-600 transition-colors">News</Link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-slate-900 truncate max-w-[200px]">{{ article.title }}</span>
                </nav>
            </div>

            <!-- Header -->
            <header class="container mx-auto px-4 lg:px-8 mb-16 max-w-5xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest mb-6">
                    {{ article.category || 'Official Announcement' }}
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-8 leading-[1.1]">
                    {{ article.title }}
                </h1>
                
                <div class="flex flex-wrap items-center gap-8 border-y border-slate-100 py-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Published By</div>
                            <div class="text-slate-900 font-bold leading-none">University Media Center</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Date Posted</div>
                            <div class="text-slate-900 font-bold leading-none">{{ formatDate(article.publish_date) }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div class="container mx-auto px-4 lg:px-8 mb-20">
                <div class="relative rounded-[3rem] overflow-hidden shadow-2xl shadow-indigo-500/10 border border-slate-100 aspect-[21/9]">
                    <img 
                        :src="article.image_url || 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1600&q=80'" 
                        :alt="article.title"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>

            <!-- Content -->
            <div class="container mx-auto px-4 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <div class="prose prose-lg prose-slate max-w-none text-slate-700 leading-[1.8] font-medium font-sans">
                        {{ article.content }}
                    </div>
                    
                    <!-- Share Section -->
                    <div class="mt-20 pt-10 border-t border-slate-100 flex flex-wrap items-center justify-between gap-6">
                        <div class="text-sm font-bold text-slate-900 uppercase tracking-widest">Share this news</div>
                        <div class="flex gap-4">
                            <button class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </button>
                            <button class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-pink-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.848 0-3.204.012-3.584.07-4.849.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related News -->
        <section v-if="relatedNews.length > 0" class="bg-slate-50 py-24">
            <div class="container mx-auto px-4 lg:px-8">
                <div class="flex items-center justify-between mb-16">
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tighter">Related Articles</h2>
                    <Link :href="route('news.index')" class="text-indigo-600 font-bold text-sm hover:underline">View All News</Link>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <article v-for="item in relatedNews" :key="item.id" class="group bg-white rounded-3xl overflow-hidden border border-slate-200/60 shadow-sm hover:shadow-xl transition-all duration-500 flex flex-col">
                        <div class="relative h-48 overflow-hidden">
                            <img 
                                :src="item.image_url || 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80'" 
                                :alt="item.title"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            />
                        </div>
                        <div class="p-8">
                             <div class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-3">{{ formatDate(item.publish_date) }}</div>
                             <h3 class="text-lg font-black text-slate-900 mb-4 line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors">
                                {{ item.title }}
                             </h3>
                             <Link :href="route('news.show', item.id)" class="text-indigo-600 font-bold text-xs uppercase tracking-widest hover:text-slate-900 transition-colors inline-flex items-center gap-2">
                                Read More
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                             </Link>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Social Section -->
        <SocialSection :settings="mediaSettings" />

        <Footer />
    </div>
</template>

<style scoped>
.prose {
    white-space: pre-wrap;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
