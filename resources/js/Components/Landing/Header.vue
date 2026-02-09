<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const navLinks = [
    { name: 'Home', href: route('home') },
    { name: 'Administration', href: '#administration' },
    { name: 'News', href: '#news' },
    { name: 'Contact', href: '#' },
];

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header 
        :class="[
            'sticky top-0 z-50 w-full transition-all duration-300 border-b',
            isScrolled ? 'bg-white/95 backdrop-blur-md py-3 shadow-md border-gray-200' : 'bg-white py-5 border-transparent'
        ]"
    >
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <Link :href="route('home')" class="flex-shrink-0 flex items-center gap-4 group">
                    <img 
                        src="/images/logo2.png" 
                        alt="BATU Logo" 
                        class="h-14 w-auto transition-transform group-hover:scale-105"
                    />
                    <div class="hidden lg:block border-l-2 border-slate-200 pl-4">
                        <span class="text-sm font-black text-slate-900 uppercase tracking-tighter block leading-tight">
                            Borg El Arab<br>
                            <span class="text-indigo-600">Technological</span> University
                        </span>
                    </div>
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    <Link 
                        v-for="link in navLinks" 
                        :key="link.name" 
                        :href="link.href"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-indigo-600 rounded-lg transition-all"
                    >
                        {{ link.name }}
                    </Link>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-slate-900 hover:bg-indigo-600 rounded-xl transition-all shadow-lg hover:shadow-indigo-500/20 active:scale-95"
                    >
                        Portal Login
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-lg active:scale-95"
                    >
                        Dashboard   
                    </Link>
                    
                    <!-- Mobile Menu Button -->
                    <button 
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                    >
                        <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-show="isMobileMenuOpen" class="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-200 shadow-xl p-4 animate-in slide-in-from-top duration-300">
            <nav class="flex flex-col space-y-2">
                <Link 
                    v-for="link in navLinks" 
                    :key="link.name" 
                    :href="link.href"
                    class="px-4 py-3 text-base font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition-all"
                    @click="isMobileMenuOpen = false"
                >
                    {{ link.name }}
                </Link>
            </nav>
        </div>
    </header>
</template>
