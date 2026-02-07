<script setup>
</script>

<template>
    <section class="relative w-full h-[600px] flex items-center overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img 
                src="https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg" 
                alt="University Campus" 
                class="w-full h-full object-cover"
            />
            <!-- Subtle Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-slate-900/40"></div>
        </div>

        <!-- Content -->
        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-6 animate-fade-in-up">
                    Borg Al Arab Technological University
                </h1>
                <p class="text-xl md:text-2xl text-slate-200 font-medium mb-8 leading-relaxed max-w-2xl">
                    Excellence in technological education and innovation. Shaping the future of Egyptian industry through academic leadership.
                </p>
                <div class="flex flex-wrap gap-4">
                    <button class="px-8 py-4 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-lg transition-all shadow-xl hover:shadow-2xl active:scale-95">
                        University Portal
                    </button>
                    <button class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold rounded-lg border border-white/30 transition-all active:scale-95">
                        Explore Faculties
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
}
</style>
