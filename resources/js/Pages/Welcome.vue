<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Components/Landing/Header.vue';
import Hero from '@/Components/Landing/Hero.vue';
import PresidentSection from '@/Components/Landing/PresidentSection.vue';
import NewsSection from '@/Components/Landing/NewsSection.vue';
import AdministrationCards from '@/Components/Landing/AdministrationCards.vue';
import Footer from '@/Components/Landing/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    initialNews: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head>
        <title>Borg Al Arab Technological University | Official Portal</title>
        <meta name="description" content="Welcome to the official administrative portal of Borg Al Arab Technological University. Excellence in technology and innovation." />
    </Head>

    <div class="min-h-screen bg-white">
        <!-- Application Shell -->
        <Header />

        <main>
            <!-- Hero Section -->
            <Hero />

            <!-- President Section -->
            <PresidentSection />

            <!-- News & Announcements -->
            <NewsSection :news="initialNews" />

            <!-- Administrations Overview -->
            <AdministrationCards />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style>
/* Global formal styles */
body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Custom transitions and utility classes if needed */
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
