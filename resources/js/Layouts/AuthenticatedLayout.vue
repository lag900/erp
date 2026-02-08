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
    <div class="min-h-screen bg-[#F8FAFC] antialiased">
        <div class="flex h-screen overflow-hidden">
            <!-- Global Sidebar (Desktop) -->
            <Sidebar class="hidden flex-shrink-0 lg:flex" />

            <!-- Main Content Area -->
            <div class="flex flex-1 flex-col overflow-hidden">
                <!-- Desktop Top Header -->
                <header class="hidden lg:flex h-16 items-center justify-between border-b border-gray-200 bg-white px-8">
                    <div class="flex items-center gap-4">
                        <!-- Left side of header (e.g., Search or Breadcrumbs can go here) -->
                        <div class="w-64">
                            <DepartmentSwitcher />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-full bg-white p-1 pr-3 text-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1FA6A0] focus:ring-offset-2 border border-gray-100 shadow-sm">
                                    <img 
                                        v-if="$page.props.auth.user.image" 
                                        :src="$page.props.auth.user.image_url" 
                                        alt="User Avatar" 
                                        class="h-8 w-8 rounded-full object-cover"
                                    />
                                    <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E6F4F3] text-[#1FA6A0] font-bold uppercase text-xs">
                                         {{ $page.props.auth.user.name.charAt(0) }}
                                    </div>
                                    <div v-if="$page.props.auth.user" class="flex flex-col items-start text-left leading-none">
                                        <span class="font-bold text-gray-800 tracking-tight">{{ $page.props.auth.user.name }}</span>
                                        <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mt-0.5">{{ $page.props.auth.roles && $page.props.auth.roles.length > 0 ? $page.props.auth.roles[0] : 'Member' }}</span>
                                    </div>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Account</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile Settings
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden">
                    <button
                        type="button"
                        class="items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 focus:outline-none"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <Link :href="route('dashboard')">
                         <img 
                            src="/images/logo.png" 
                            alt="BATU Logo" 
                            class="h-8 w-auto object-contain"
                        />
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center rounded-full bg-gray-100 p-1 text-sm focus:outline-none focus:ring-2 focus:ring-[#1FA6A0] focus:ring-offset-2">
                                <span class="sr-only">Open user menu</span>
                                <img 
                                    v-if="$page.props.auth.user.image" 
                                    :src="$page.props.auth.user.image_url" 
                                    alt="User Avatar" 
                                    class="h-8 w-8 rounded-full object-cover"
                                />
                                <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E6F4F3] text-[#1FA6A0] font-bold uppercase text-xs">
                                     {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </header>

                <!-- Mobile Navigation Menu -->
                 <div
                    v-if="showingNavigationDropdown"
                    class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
                    @click="showingNavigationDropdown = false"
                ></div>

                <div
                    v-if="showingNavigationDropdown"
                    class="fixed inset-y-0 left-0 z-50 w-72 transform overflow-y-auto bg-white shadow-xl transition-transform lg:hidden"
                >
                     <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <img src="/images/logo.png" alt="Logo" class="h-8 w-8" />
                            <span class="font-bold text-gray-800">BATU Admin</span>
                        </div>
                        <button @click="showingNavigationDropdown = false" class="text-gray-500 hover:text-gray-700">
                             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                     </div>

                     <div class="px-4 py-4">
                         <DepartmentSwitcher />
                     </div>

                    <nav class="space-y-1 px-4 pb-4">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')" @click="showingNavigationDropdown = false">
                            Dashboard
                        </NavLink>

                        <!-- Dynamic Sidebar Groups for Mobile -->
                        <div v-for="group in $page.props.departmentContext?.sidebar" :key="group.group" class="pt-4">
                            <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ group.group }}</p>
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
                    </nav>
                </div>


                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-[#F8FAFC] focus:outline-none">
                    <div class="py-6">
                        <div v-if="$slots.header" class="w-full px-4 sm:px-6 lg:px-10 mb-8">
                            <h1 class="text-[28px] font-semibold tracking-tight text-gray-900">
                                <slot name="header" />
                            </h1>
                        </div>
                        <div class="w-full px-4 sm:px-6 lg:px-10">
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
