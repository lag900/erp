<script setup>
</script>

<template>
    <section class="relative min-h-[90vh] flex items-center overflow-hidden bg-slate-900 pt-20">
        <!-- Background Elements -->
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-900/90 to-indigo-900/50 z-10"></div>
            <img src="/images/campus.webp" class="w-full h-full object-cover opacity-60 mix-blend-overlay"
                alt="Borg El Arab Technological University Campus"
                @error="(e) => e.target.src = '/images/collage.jpg'" />
            <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-slate-50 to-transparent z-20"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-4xl">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold uppercase tracking-widest mb-8 animate-in fade-in slide-in-from-bottom duration-700">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    Now Enrolling for 2026 Academic Year
                </div>

                <h1
                    class="text-5xl lg:text-7xl font-black text-white mb-8 leading-[1.1] animate-in fade-in slide-in-from-bottom duration-700 delay-100">
                    Borg El Arab
                    <span class="text-indigo-500">Technological</span> University
                </h1>

                <p
                    class="text-xl text-slate-300 mb-10 leading-relaxed max-w-2xl animate-in fade-in slide-in-from-bottom duration-700 delay-200">
                    Borg El Arab Technological University (BATU) is committed to excellence in industry-led education,
                    bridging the gap between academic innovation and the global labor market.
                </p>

                <div class="flex flex-wrap gap-4 animate-in fade-in slide-in-from-bottom duration-700 delay-300">
                    <button
                        class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-xl shadow-indigo-600/30 hover:scale-105 active:scale-95">
                        Apply Now 2026
                    </button>
                    <button
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl transition-all backdrop-blur-md border border-white/10 hover:border-white/20 active:scale-95">
                        Explore Faculties
                    </button>
                </div>
            </div>
        </div>

        <!-- Decorative UI Element -->
        <div
            class="hidden lg:block absolute right-0 bottom-24 w-1/3 animate-in fade-in slide-in-from-right duration-1000 delay-500">
            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-l-3xl p-8 shadow-2xl">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-white font-bold text-lg leading-tight text-white">Innovation First</div>
                        <div class="text-slate-400 text-sm">Industry-driven curriculum</div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full w-3/4 bg-indigo-500 rounded-full"></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <span>Success Rate</span>
                        <span>85% Employment</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
