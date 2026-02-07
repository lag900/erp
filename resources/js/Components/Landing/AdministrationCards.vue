<script setup>
import { ref, onMounted } from 'vue';
import { mockDataService } from '@/Services/mockDataService';

const administrations = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        administrations.value = await mockDataService.getAdministrations();
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section class="py-24 bg-white">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">University Administrations</h2>
                <div class="w-20 h-1.5 bg-blue-700 mx-auto rounded-full mb-6"></div>
                <p class="text-slate-600 max-w-2xl mx-auto">
                    The backbone of BATU's operational excellence, ensuring efficient management across all academic and administrative functions.
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="i in 4" :key="i" class="h-64 bg-slate-50 rounded-2xl animate-pulse"></div>
            </div>

            <!-- Cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div 
                    v-for="admin in administrations" 
                    :key="admin.id"
                    class="group relative bg-slate-50 rounded-2xl p-8 border border-transparent hover:border-blue-200 hover:bg-white hover:shadow-2xl transition-all duration-500"
                >
                    <div class="relative z-10">
                        <div class="w-16 h-16 rounded-xl bg-blue-700/10 flex items-center justify-center mb-6 group-hover:bg-blue-700 transition-colors duration-500">
                             <!-- Dynamic icon placeholder -->
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-700 group-hover:text-white transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-900 mb-4 group-hover:text-blue-700 transition-colors">
                            {{ admin.name }}
                        </h3>
                        
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            {{ admin.message }}
                        </p>

                        <div class="flex items-center gap-3">
                            <img :src="admin.directorImage" :alt="admin.directorName" class="w-10 h-10 rounded-full object-cover ring-2 ring-white" />
                            <div class="text-xs">
                                <div class="font-bold text-slate-900">{{ admin.directorName }}</div>
                                <div class="text-slate-400">Director</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center">
                <button class="px-8 py-3.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-black transition-all shadow-lg active:scale-95">
                    View Full Organization Chart
                </button>
            </div>
        </div>
    </section>
</template>
