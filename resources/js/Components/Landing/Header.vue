<script setup>
import { Link } from '@inertiajs/vue3';

const navLinks = [
    { name: 'Home', href: '#' },
    { name: 'Faculties', href: '#' },
    { name: 'Administration', href: '#' },
    { name: 'Departments', href: '#' },
    { name: 'Contact', href: '#' },
];
</script>

<template>
    <header class="sticky top-0 z-50 w-full bg-white border-b border-gray-100 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-4">
                    <img 
                        src="https://i.postimg.cc/zXbSrQdW/BATU-(2).png" 
                        alt="BATU Logo" 
                        class="h-14 w-auto"
                    />
                    <div class="hidden lg:block border-l border-gray-300 pl-4">
                        <span class="text-sm font-bold text-slate-800 uppercase tracking-wider block leading-tight">
                            Borg Al Arab<br>Technological University
                        </span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a 
                        v-for="link in navLinks" 
                        :key="link.name" 
                        :href="link.href"
                        class="text-sm font-medium text-slate-600 hover:text-blue-700 transition-colors duration-200"
                    >
                        {{ link.name }}
                    </a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg active:scale-95"
                    >
                        Portal Login
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg active:scale-95"
                    >
                        Login   
                    </Link>
                    
                    <!-- Mobile Menu Button -->
                    <button class="md:hidden p-2 text-slate-600 hover:bg-gray-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
