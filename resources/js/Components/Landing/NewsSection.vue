<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    }
});

const newsItems = ref(props.news);
const isLoading = ref(props.news.length === 0);

const fetchNews = async () => {
    if (props.news.length > 0) {
        isLoading.value = false;
        return;
    }

    try {
        const response = await fetch('/api/news');
        if (response.ok) {
            newsItems.value = await response.json();
        }
    } catch (error) {
        console.error("Failed to load news:", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchNews();
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <section class="py-24 bg-slate-50 relative overflow-hidden" id="news">
        <!-- Decoration -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-end justify-between mb-16 gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold uppercase tracking-widest mb-4">
                        Campus Life & Updates
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-6">Latest From BATU</h2>
                    <p class="text-slate-600 text-lg leading-relaxed">
                        Stay informed about our latest research, events, and academic breakthroughs that are shaping the future of technology.
                    </p>
                </div>
                <Link :href="route('news.index')" class="group flex items-center gap-2 px-8 py-3.5 bg-indigo-600 hover:bg-slate-900 text-white text-sm font-bold rounded-2xl transition-all shadow-xl shadow-indigo-600/20 active:scale-95">
                    View All News
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Skeleton Loader -->
                <template v-if="isLoading">
                    <div v-for="i in 3" :key="i" class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 animate-pulse h-[500px]"></div>
                </template>

                <!-- News Cards -->
                <template v-else-if="newsItems.length > 0">
                    <article 
                        v-for="item in newsItems" 
                        :key="item.id" 
                        class="group bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-700 flex flex-col h-full"
                    >
                        <div class="relative h-72 overflow-hidden">
                            <img 
                                :src="item.image || 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80'" 
                                :alt="item.title" 
                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute top-6 left-6 px-4 py-2 bg-white/90 backdrop-blur-md rounded-xl text-xs font-black text-slate-900 shadow-xl">
                                {{ formatDate(item.publish_date) || formatDate(item.created_at) }}
                            </div>
                        </div>

                        <div class="p-10 flex-1 flex flex-col">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-4">{{ item.category || 'Announcement' }}</div>
                            <h3 class="text-2xl font-black text-slate-900 mb-4 leading-[1.3] group-hover:text-indigo-600 transition-colors">
                                {{ item.title }}
                            </h3>
                            <p class="text-slate-500 text-base leading-relaxed mb-10 line-clamp-3">
                                {{ item.description }}
                            </p>
                            
                            <div class="mt-auto pt-8 border-t border-slate-50 flex items-center justify-between">
                                <Link :href="route('news.show', item.id)" class="inline-flex items-center gap-2 px-8 py-3.5 bg-slate-900 text-white text-sm font-bold rounded-2xl hover:bg-indigo-600 transition-all active:scale-95 shadow-xl shadow-slate-900/10 group/btn">
                                    Read Article
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </Link>
                                
                                <div class="flex gap-2">
                                     <button class="w-9 h-9 rounded-xl border border-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                     </button>
                                </div>
                            </div>
                        </div>
                    </article>
                </template>

                <div v-else class="col-span-full py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <p class="text-slate-400 font-bold">No news articles published at this time.</p>
                </div>
            </div>
        </div>
    </section>
</template>
