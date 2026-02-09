<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    faculties: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <section id="faculties" class="py-24 bg-slate-50 relative overflow-hidden">
        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 border border-indigo-200 text-indigo-700 text-[10px] font-bold uppercase tracking-widest mb-4">
                    Academic Units
                </div>
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-6">Our Faculties & Departments</h2>
                <p class="text-slate-600 text-lg leading-relaxed max-w-3xl mx-auto">
                    Explore our diverse range of technological faculties, each dedicated to providing specialized education and cutting-edge research opportunities.
                </p>
            </div>

            <div v-if="faculties.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <p class="text-slate-500">No faculties currently listed.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div 
                    v-for="faculty in faculties" 
                    :key="faculty.id"
                    class="group bg-white rounded-3xl overflow-hidden border border-slate-200 hover:border-indigo-300 transition-all duration-500 hover:shadow-2xl hover:shadow-indigo-500/10"
                >
                    <div class="flex flex-col h-full">
                        <div class="relative h-64 overflow-hidden">
                            <img 
                                :src="faculty.director_image || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80'" 
                                :alt="faculty.name"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                            <div class="absolute bottom-6 left-6 right-6">
                                <div class="text-white/60 text-xs font-bold uppercase tracking-widest mb-1">{{ faculty.code }}</div>
                                <h3 class="text-2xl font-black text-white leading-tight">{{ faculty.name }}</h3>
                            </div>
                        </div>
                        
                        <div class="p-8 flex-1 flex flex-col">
                            <p class="text-slate-600 mb-8 line-clamp-3 flex-1">
                                {{ faculty.description }}
                            </p>
                            
                            <div class="flex items-center justify-between mt-auto pt-6 border-t border-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-400">
                                        {{ faculty.name.charAt(0) }}
                                    </div>
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">{{ faculty.director_name || 'Acting Dean' }}</div>
                                        <div class="text-slate-500">Dean of Faculty</div>
                                    </div>
                                </div>
                                
                                <button class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition-all active:scale-95 shadow-lg shadow-indigo-600/20">
                                    Learn More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center">
                <p class="text-slate-500 font-medium mb-6 italic">Looking for a specific department within a faculty?</p>
                <div class="flex justify-center gap-4">
                     <button class="px-8 py-3 bg-white text-slate-900 font-bold rounded-xl border border-slate-200 hover:bg-slate-50 transition-all active:scale-95">
                        Academic Calendar
                    </button>
                    <button class="px-8 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-black transition-all active:scale-95 shadow-lg">
                        Admission Guide
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
