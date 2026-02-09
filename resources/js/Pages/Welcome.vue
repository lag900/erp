<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Components/Landing/Header.vue';
import Hero from '@/Components/Landing/Hero.vue';
import PresidentSection from '@/Components/Landing/PresidentSection.vue';
import NewsSection from '@/Components/Landing/NewsSection.vue';
import AdministrationCards from '@/Components/Landing/AdministrationCards.vue';
import SocialSection from '@/Components/Landing/SocialSection.vue';
import Footer from '@/Components/Landing/Footer.vue';

defineProps({
    initialNews: {
        type: Array,
        default: () => []
    },
    administrations: {
        type: Array,
        default: () => []
    },
    faculties: {
        type: Array,
        default: () => []
    },
    mediaSettings: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>
    <Head>
        <title>Borg El Arab Technological University | Official Website</title>
        <meta name="description" content="Official website of Borg El Arab Technological University (BATU), Egypt. Explore our faculties of Industry, Energy, and Health Sciences. Empowering innovation and technical excellence." />
        <meta name="keywords" content="BATU, Borg El Arab University, Technological University Egypt, Industry and Energy Technology, Health Sciences Technology" />
    </Head>

    <div class="min-h-screen bg-white selection:bg-indigo-100 selection:text-indigo-900">
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
            <AdministrationCards :administrations="administrations" />

            <!-- Social Media Feed -->
            <SocialSection :settings="mediaSettings" />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style>
/* Smooth entrance animations for sections */
section {
    opacity: 0;
    transform: translateY(20px);
    animation: slideUp 0.8s ease-out forwards;
}

@keyframes slideUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Staggered delay for sections */
main section:nth-child(1) { animation-delay: 0.1s; }
main section:nth-child(2) { animation-delay: 0.2s; }
main section:nth-child(3) { animation-delay: 0.3s; }
main section:nth-child(4) { animation-delay: 0.4s; }
</style>
