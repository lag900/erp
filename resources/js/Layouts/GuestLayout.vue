<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-white pt-7 sm:pt-0"
    >
        <div class="mb-10 flex flex-col items-center text-center">
            <Link href="/" class="mb-0 flex justify-center">
                <!-- Local optimized logo with eager loading -->
                <img 
                    src="/images/logo.png" 
                    alt="Borg Al Arab Technological University" 
                    class="h-44 w-auto object-contain transition-transform duration-300 hover:scale-[1.01]"
                    loading="eager"
                />
            </Link>
            
            <div class="space-y-1">
                <h1 class="text-xl font-bold tracking-tight text-gray-700">
                    Borg Al Arab Technological University
                </h1>
                <p class="text-sm font-medium uppercase tracking-wider text-primary">
                    Administrative ERP System
                </p>
            </div>
        </div>

        <div
            class="w-full overflow-hidden rounded-xl border border-gray-100 bg-white px-8 py-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:max-w-[440px]"
        >
            <slot />
        </div>
        
        <div class="mt-8 flex flex-col items-center space-y-2 text-center text-xs text-gray-400">
            <p>&copy; {{ new Date().getFullYear() }} Borg Al Arab Technological University</p>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-primary">Privacy Policy</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-primary">Support</a>
            </div>
        </div>
    </div>
</template>
