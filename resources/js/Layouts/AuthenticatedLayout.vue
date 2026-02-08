<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import DepartmentSwitcher from '@/Components/DepartmentSwitcher.vue';
import Toast from '@/Components/Toast.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();

const permissions = computed(() => page.props.auth?.permissions ?? []);
const roles = computed(() => page.props.auth?.roles ?? []);
const enabledFeatures = computed(() => page.props.departmentContext?.featuresEnabled ?? []);

const hasFeature = (featureKey) => enabledFeatures.value.includes(featureKey);
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] text-slate-800 font-sans antialiased selection:bg-[#3d4adb]/10 selection:text-[#3d4adb]">
        <div class="flex h-screen overflow-hidden">
            <!-- Global Sidebar (Desktop) -->
            <Sidebar class="hidden lg:flex" />

            <!-- Main Content Area -->
            <div class="flex flex-1 flex-col overflow-hidden">
                <!-- Desktop Top Header -->
                <header class="hidden lg:flex h-20 items-center justify-between border-b border-slate-200/50 bg-white/80 backdrop-blur-md px-10 sticky top-0 z-30">
                    <div class="flex items-center gap-6">
                        <div class="w-[280px]">
                            <DepartmentSwitcher />
                        </div>
                    </div>

                    <div class="flex items-center gap-5">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-3.5 rounded-2xl bg-slate-50/50 p-1.5 pr-4 text-sm transition-all hover:bg-slate-100/50 focus:outline-none border border-slate-200 shadow-sm group">
                                    <div class="relative">
                                        <img 
                                            v-if="$page.props.auth.user.image" 
                                            :src="$page.props.auth.user.image_url" 
                                            alt="User Avatar" 
                                            class="h-9 w-9 rounded-xl object-cover"
                                        />
                                        <div v-else class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#3d4adb]/10 text-[#3d4adb] font-bold text-sm">
                                             {{ $page.props.auth.user.name.charAt(0) }}
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></div>
                                    </div>
                                    <div v-if="$page.props.auth.user" class="flex flex-col items-start text-left leading-none">
                                        <span class="font-bold text-slate-800 tracking-tight">{{ $page.props.auth.user.name }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $page.props.auth.roles && $page.props.auth.roles.length > 0 ? $page.props.auth.roles[0] : 'Member' }}</span>
                                    </div>
                                    <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-5 py-3 border-b border-slate-50">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.15em]">System Access</p>
                                </div>
                                <div class="py-1">
                                    <DropdownLink :href="route('profile.edit')">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            Profile Profile Settings
                                        </div>
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="!text-rose-600">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            Log Out Account
                                        </div>
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden sticky top-0 z-30">
                    <button
                        type="button"
                        class="items-center justify-center rounded-xl p-2 text-slate-500 hover:bg-slate-50 transition-colors active:scale-95"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <Link :href="route('dashboard')">
                         <img 
                            src="/images/logo.png" 
                            alt="Logo" 
                            class="h-8 w-auto object-contain"
                        />
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center rounded-xl bg-slate-50 p-0.5 focus:outline-none">
                                <img 
                                    v-if="$page.props.auth.user.image" 
                                    :src="$page.props.auth.user.image_url" 
                                    alt="User Avatar" 
                                    class="h-8 w-8 rounded-xl object-cover"
                                />
                                <div v-else class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#3d4adb]/10 text-[#3d4adb] font-bold text-xs uppercase">
                                     {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </header>

                <!-- Mobile Navigation Menu -->
                 <div
                    v-if="showingNavigationDropdown"
                    class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden transition-opacity duration-300"
                    @click="showingNavigationDropdown = false"
                ></div>

                <div
                    v-if="showingNavigationDropdown"
                    class="fixed inset-y-0 left-0 z-50 w-[300px] transform overflow-y-auto bg-white shadow-2xl transition-transform duration-300 ease-out lg:hidden"
                >
                     <div class="flex items-center justify-between px-6 py-6 border-b border-slate-50">
                        <div class="flex items-center gap-3">
                            <img src="/images/logo.png" alt="Logo" class="h-9 w-auto" />
                            <span class="font-bold text-slate-800 tracking-tight">University ERP</span>
                        </div>
                        <button @click="showingNavigationDropdown = false" class="text-slate-400 hover:text-slate-600 p-1">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                     </div>

                     <div class="px-4 py-6">
                         <div class="p-1 px-3 bg-slate-50 rounded-2xl border border-slate-100 mb-6">
                             <DepartmentSwitcher />
                         </div>

                        <nav class="space-y-8 px-2">
                            <div class="space-y-1">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" @click="showingNavigationDropdown = false">
                                    Dashboard
                                </NavLink>
                            </div>

                            <!-- Dynamic Sidebar Groups for Mobile -->
                            <div v-for="group in $page.props.departmentContext?.sidebar" :key="group.group">
                                <p class="px-3 mb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ group.group }}</p>
                                <div class="space-y-1">
                                    <NavLink 
                                        v-for="item in group.items" 
                                        :key="item.permission" 
                                        :href="route(item.route)" 
                                        :active="route().current(item.route) || (item.route.endsWith('.index') && route().current(item.route.replace('.index', '.*')))"
                                        @click="showingNavigationDropdown = false"
                                    >
                                        {{ item.label }}
                                    </NavLink>
                                </div>
                            </div>
                        </nav>
                     </div>
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-[#f8fafc] focus:outline-none">
                    <div class="py-8 lg:py-10">
                        <div v-if="$slots.header" class="w-full px-4 sm:px-8 lg:px-12 mb-10">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-800 leading-tight">
                                <slot name="header" />
                            </h1>
                        </div>
                        <div class="w-full px-4 sm:px-8 lg:px-12 pb-20">
                             <slot />
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <Toast />
        <ConfirmModal />
    </div>
</template>

