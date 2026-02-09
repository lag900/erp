<script setup>
</script>

<template>
    <section class="py-24 bg-white overflow-hidden relative">
        <!-- Background accents -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-slate-50/50 -skew-x-12 translate-x-1/4 -z-0"></div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <!-- Image Container -->
                <div class="lg:w-2/5 relative">
                    <div class="relative z-10 rounded-[2.5rem] overflow-hidden shadow-[0_32px_64px_-16px_rgba(0,0,0,0.15)] ring-1 ring-slate-200 aspect-[4/5] bg-slate-100">
                            <img 
                                src="/images/president.webp" 
                                alt="Prof. Dr. Mohamed Morsi El-Gohary" 
                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                                @error="(e) => e.target.src = '/images/DR M_G.png'"
                                loading="lazy"
                            />
                         <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                    </div>
                    
                    <!-- Decorative Floating Elements -->
                    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-indigo-600 rounded-[3rem] -z-0 opacity-5 blur-2xl animate-pulse"></div>
                    <div class="absolute top-10 -right-10 w-24 h-24 bg-indigo-600 rounded-full -z-0 opacity-10"></div>
                    
                    <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-3xl shadow-2xl border border-slate-100 z-20 hidden md:block animate-bounce-slow">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Status</div>
                                <div class="text-slate-900 font-black leading-none">Global Rankings Up</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text Content -->
                <div class="lg:w-3/5">
                    <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 text-xs font-bold tracking-widest text-indigo-600 uppercase bg-indigo-50 border border-indigo-100 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                        President's Message
                    </div>
                    
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 leading-tight">
                        Prof. Dr. Mohamed Morsi El-Gohary
                    </h2>
                    
                    <h3 class="text-xl font-bold text-indigo-600/80 mb-10 tracking-wide uppercase text-sm">
                        President of Borg Al Arab Technological University
                    </h3>

                    <div class="relative mb-10">
                        <svg class="absolute -top-6 -left-8 w-16 h-16 text-slate-100 -z-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C15.4647 8 15.017 8.44772 15.017 9V12C15.017 12.5523 14.5693 13 14.017 13H13.017V21H14.017ZM6.017 21L6.017 18C6.017 16.8954 6.91243 16 8.017 16H11.017C11.5693 16 12.017 15.5523 12.017 15V9C12.017 8.44772 11.5693 8 11.017 8H8.017C7.46472 8 7.017 8.44772 7.017 9V12C7.017 12.5523 6.5693 13 6.017 13H5.017V21H6.017Z" />
                        </svg>
                        <div class="space-y-6 text-slate-600 leading-relaxed text-lg lg:text-xl font-medium">
                            <p class="italic">
                                "Our mission at Borg El Arab Technological University is to cultivate a new generation of technical specialists capable of leading Egypt's industrial and health technology sectors. Following Egypt's 2030 vision, we integrate world-class academic standards with intensive practical training."
                            </p>
                            <p class="italic">
                                "We are not just a university; we are an innovation hub that connects scientific research with the heartbeat of the Egyptian industry, ensuring every graduate is prepared for the complex challenges of tomorrow."
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 mt-12 pt-10 border-t border-slate-100">
                        <img src="/images/logo2.png" class="h-12 w-auto grayscale opacity-50" />
                        <div>
                            <div class="text-slate-900 font-black text-lg">Academic Council</div>
                            <div class="text-slate-400 text-sm font-bold uppercase tracking-widest">Vision 2030 Strategy</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce-slow {
    animation: bounce-slow 4s ease-in-out infinite;
}
</style>
