<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import DepartmentSwitcher from '@/Components/DepartmentSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();

const permissions = computed(() => page.props.auth?.permissions ?? []);
const roles = computed(() => page.props.auth?.roles ?? []);
const enabledFeatures = computed(() => page.props.departmentContext?.featuresEnabled ?? []);

const can = (permission) => permissions.value.includes(permission);
const hasRole = (role) => roles.value.includes(role);
const hasFeature = (featureKey) => enabledFeatures.value.includes(featureKey);
</script>

<template>
    <aside class="flex h-screen w-72 flex-col border-r border-gray-200 bg-white">
        <!-- Logo Area -->
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-5">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <img 
                    src="/images/logo.png" 
                    alt="BATU Logo" 
                    class="h-8 w-auto object-contain"
                />
                <div class="flex flex-col">
                    <span class="text-xs font-bold uppercase tracking-wide text-gray-900 leading-none">
                        Borg Al Arab
                    </span>
                    <span class="text-[9px] uppercase tracking-wider text-gray-500 leading-none mt-1">
                        Technological University
                    </span>
                </div>
            </Link>
        </div>

        <!-- Department Switcher -->
        <div class="px-4 py-4">
            <DepartmentSwitcher />
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 pb-4 space-y-6">
            <!-- General -->
            <div class="space-y-1">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('dashboard') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </NavLink>
            </div>

            <!-- Asset Management -->
            <div v-if="hasFeature('assets')">
                <h3 class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Assets
                </h3>
                <div class="space-y-1">
                    <NavLink v-if="can('asset-list')" :href="route('assets.index')" :active="route().current('assets.*')">
                       <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('assets.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Assets
                    </NavLink>
                    <NavLink v-if="can('location-list')" :href="route('locations.index')" :active="route().current('locations.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('locations.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Locations
                    </NavLink>
                    <NavLink v-if="can('building-list')" :href="route('buildings.index')" :active="route().current('buildings.*')">
                         <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('buildings.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Buildings
                    </NavLink>
                    <NavLink v-if="can('level-list')" :href="route('levels.index')" :active="route().current('levels.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('levels.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        Levels
                    </NavLink>
                    <NavLink v-if="can('room-list')" :href="route('rooms.index')" :active="route().current('rooms.*')">
                         <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('rooms.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        Rooms
                    </NavLink>
                    <NavLink v-if="can('category-list')" :href="route('categories.index')" :active="route().current('categories.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('categories.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Categories
                    </NavLink>
                    <NavLink v-if="can('sub_category-list')" :href="route('subcategories.index')" :active="route().current('subcategories.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('subcategories.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Subcategories
                    </NavLink>
                </div>
            </div>

            <!-- Organization -->
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Organization
                </h3>
                <div class="space-y-1">
                    <NavLink v-if="can('department-list')" :href="route('departments.index')" :active="route().current('departments.index')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('departments.index') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Departments
                    </NavLink>
                    <NavLink v-if="can('feature-toggle')" :href="route('departments.features')" :active="route().current('departments.features')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('departments.features') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Features
                    </NavLink>
                </div>
            </div>

            <!-- Access Control -->
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Access Control
                </h3>
                <div class="space-y-1">
                    <NavLink v-if="can('user-list')" :href="route('users.index')" :active="route().current('users.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('users.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Users
                    </NavLink>
                    <NavLink v-if="can('role-list')" :href="route('roles.index')" :active="route().current('roles.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('roles.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Roles
                    </NavLink>
                    <NavLink v-if="can('permission-list')" :href="route('permissions.index')" :active="route().current('permissions.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('permissions.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        Permissions
                    </NavLink>
                </div>
            </div>

            <!-- Media (Only for Media Role or permissions) -->
            <div v-if="hasRole('Media') || can('news-list') || can('media-settings-manage')">
                <h3 class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Media & Content
                </h3>
                <div class="space-y-1">
                    <NavLink v-if="can('news-list')" :href="route('media.news.index')" :active="route().current('media.news.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('media.news.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        News & Events
                    </NavLink>
                    <NavLink v-if="can('media-settings-manage')" :href="route('media.settings.edit')" :active="route().current('media.settings.*')">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{ 'text-gray-600': route().current('media.settings.*') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Media Settings
                    </NavLink>
                </div>
            </div>
        </nav>


    </aside>
</template>
