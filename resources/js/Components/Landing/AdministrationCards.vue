<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    administrations: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <section id="administration" class="py-24 bg-white relative overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-96 h-96 bg-purple-50 rounded-full blur-3xl opacity-50"></div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold uppercase tracking-widest mb-4">
                        Governance & Management
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-6">University Administrations</h2>
                    <p class="text-slate-600 text-lg leading-relaxed">
                        The backbone of BATU's operational excellence, ensuring efficient management across all academic and administrative functions.
                    </p>
                </div>
                <div>
                    <button class="group flex items-center gap-2 px-6 py-3 bg-slate-50 hover:bg-slate-100 text-slate-900 font-bold rounded-xl transition-all border border-slate-200 active:scale-95">
                        View Organization Chart
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="administrations.length === 0" class="text-center py-20 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">No Administrations Available</h3>
                <p class="text-slate-500">Check back later for updates on university leadership.</p>
            </div>

            <!-- Cards Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div 
                    v-for="admin in administrations" 
                    :key="admin.id"
                    class="group relative bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 hover:border-indigo-100 transition-all duration-500 flex flex-col h-full"
                >
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600 group-hover:text-white transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-900 mb-4 group-hover:text-indigo-600 transition-colors">
                            {{ admin.title }}
                        </h3>
                        
                        <p class="text-slate-500 text-sm leading-relaxed mb-8 flex-1">
                            {{ admin.description }}
                        </p>

                        <div class="pt-6 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img 
                                        :src="admin.image_url" 
                                        :alt="admin.director_name" 
                                        class="w-11 h-11 rounded-xl object-cover ring-2 ring-white shadow-md grayscale group-hover:grayscale-0 transition-all duration-500" 
                                        loading="lazy"
                                    />
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                                </div>
                                <div class="text-xs">
                                    <div class="font-bold text-slate-900">{{ admin.director_name }}</div>
                                    <div class="text-slate-400 font-medium">{{ admin.director_title }}</div>
                                </div>
                            </div>
                            
                            <Link 
                                :href="route('home')" 
                                class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
