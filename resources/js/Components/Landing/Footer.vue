<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-slate-900 pt-24 pb-12 border-t border-slate-800">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-20">
                <!-- Brand -->
                <div class="lg:col-span-1">
                    <Link :href="route('home')" class="inline-flex items-center gap-4 mb-8">
                        <img src="/images/logo2.png" alt="BATU Logo" class="h-16 w-auto brightness-0 invert" />
                        <div class="border-l border-slate-700 pl-4 text-white">
                             <span class="text-xs font-black uppercase tracking-tighter block leading-tight">
                                Borg El Arab<br>
                                <span class="text-indigo-500">Technological</span> University
                            </span>
                        </div>
                    </Link>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8">
                        Egypt's leading institution for technological excellence. Shaping the industry through innovation, world-class education, and advanced research since sustainable industrial development.
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition-all transform hover:-translate-y-1">
                             <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-sky-500 hover:text-white transition-all transform hover:-translate-y-1">
                             <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-slate-700 hover:text-white transition-all transform hover:-translate-y-1">
                             <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-white font-black text-lg mb-8">Academic Units</h4>
                    <ul class="space-y-4">
                        <li><Link href="#faculties" class="text-slate-400 hover:text-indigo-400 transition-colors font-medium">Faculties</Link></li>
                        <li><Link href="#administration" class="text-slate-400 hover:text-indigo-400 transition-colors font-medium">University Leadership</Link></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors font-medium">Quality Assurance</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors font-medium">International Relations</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors font-medium">Research Centers</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-black text-lg mb-8">Get In Touch</h4>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="text-slate-400 text-sm leading-relaxed">New Borg El Arab City, Alexandria Governorate, Egypt | Official Academic District</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-slate-400 text-sm">info@batu.edu.eg<br>registrar@batu.edu.eg</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="text-white font-black text-lg mb-8">Newsletter</h4>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">Stay updated with our academic breakthroughs and campus events.</p>
                    <div class="relative group">
                        <input 
                            type="email" 
                            placeholder="Your institutional email" 
                            class="w-full bg-slate-800 border-slate-700 text-white rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                        />
                        <button class="absolute right-2 top-2 bottom-2 px-5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-12 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-8">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">&copy; {{ new Date().getFullYear() }} Borg Al Arab Technological University. Academic Portal.</p>
                <div class="flex gap-10 text-xs font-bold uppercase tracking-widest">
                    <a href="#" class="text-slate-500 hover:text-indigo-400 transition-all">Privacy Policy</a>
                    <a href="#" class="text-slate-500 hover:text-indigo-400 transition-all">Terms of Service</a>
                    <a href="#" class="text-slate-500 hover:text-indigo-400 transition-all">Accessibility</a>
                </div>
            </div>
        </div>
    </footer>
</template>
