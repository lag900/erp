<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    }
});

// متغير بنخزن فيه الأخبار
const newsItems = ref(props.news);
// حالة التحميل
const isLoading = ref(props.news.length === 0);

/**
 * دالة لجلب الأخبار من الـ API الحقيقي
 */
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
        console.error("فشل في تحميل الأخبار الحقيقية:", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchNews();
});

/**
 * دالة لمشاركة الخبر على فيسبوك
 */
const shareOnFacebook = (item) => {
    const title = item.title;
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.origin + '/news/' + item.id)}&quote=${encodeURIComponent(title)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <section class="py-24 bg-[#f8fafc] border-t border-slate-200" id="news-section">
        <div class="container mx-auto px-4 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-16">
                <div>
                    <span class="inline-block px-3 py-1 mb-4 text-xs font-bold tracking-widest text-[#064e3b] uppercase bg-[#d1fae5] rounded-full">
                        الأخبار والفعاليات
                    </span>
                    <h2 class="text-4xl font-black text-slate-900 mb-4">أحدث أخبار الجامعة</h2>
                    <div class="w-24 h-1.5 bg-[#064e3b] rounded-full shadow-sm"></div>
                </div>
                <button class="mt-8 md:mt-0 px-8 py-3 text-sm font-bold text-white bg-[#064e3b] hover:bg-[#065f46] rounded-xl transition-all shadow-lg hover:shadow-xl active:scale-95">
                    كل الأخبار
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                
                <template v-if="isLoading">
                    <div v-for="i in 3" :key="i" class="bg-white rounded-3xl overflow-hidden shadow-sm animate-pulse">
                        <div class="h-64 bg-slate-200"></div>
                        <div class="p-8">
                            <div class="h-4 w-1/4 bg-slate-200 rounded mb-4"></div>
                            <div class="h-8 w-full bg-slate-200 rounded mb-4"></div>
                            <div class="h-20 w-full bg-slate-200 rounded"></div>
                        </div>
                    </div>
                </template>

                <template v-else-if="newsItems.length > 0">
                    <article 
                        v-for="item in newsItems" 
                        :key="item.id" 
                        class="group bg-white rounded-[2rem] overflow-hidden border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_50px_-10px_rgba(0,0,0,0.1)] transition-all duration-500 flex flex-col"
                    >
                        <div class="relative h-64 overflow-hidden">
                            <img 
                                :src="item.image || 'https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg'" 
                                :alt="item.title" 
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            />
                            <div class="absolute top-6 left-6 flex flex-col gap-2">
                                <span class="px-4 py-2 bg-white/90 backdrop-blur-md text-[#064e3b] text-xs font-bold rounded-xl shadow-lg">
                                    {{ formatDate(item.publish_date || item.created_at) }}
                                </span>
                                <span class="px-4 py-2 bg-[#064e3b]/90 backdrop-blur-md text-white text-xs font-bold rounded-xl shadow-lg">
                                    {{ item.category || 'عام' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-8 flex-1 flex flex-col">
                            <h3 class="text-2xl font-bold text-slate-900 mb-4 group-hover:text-[#059669] transition-colors leading-snug">
                                {{ item.title }}
                            </h3>
                            <p class="text-slate-500 text-lg leading-relaxed mb-8 line-clamp-3">
                                {{ item.description }}
                            </p>
                            
                            <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between">
                                <a href="#" class="inline-flex items-center gap-2 text-[#064e3b] font-bold hover:translate-x-1 transition-transform">
                                    اقرأ المزيد
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                                
                                <button 
                                    @click="shareOnFacebook(item)"
                                    class="p-3 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                    title="شارك على فيسبوك"
                                >
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </article>
                </template>

                <template v-else>
                    <div class="col-span-3 py-20 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                        <p class="text-slate-400 font-medium">لا توجد أخبار منشورة حالياً.</p>
                    </div>
                </template>

            </div>
        </div>
    </section>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
