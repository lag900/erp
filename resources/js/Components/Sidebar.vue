<script setup>
import { computed, defineComponent, h } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import DepartmentSwitcher from '@/Components/DepartmentSwitcher.vue';

const page = usePage();

const sidebarGroups = computed(() => page.props.departmentContext?.sidebar ?? []);

// Heroicons-like SVG paths for dynamic rendering
const icons = {
    'cube': 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    'map-pin': 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    'office-building': 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'menu-alt-2': 'M4 6h16M4 12h16M4 18h16',
    'cube-transparent': 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    'tag': 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'collection': 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'briefcase': 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'adjustments': 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
    'users': 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    'shield-check': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    'lock-closed': 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
    'newspaper': 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
    'eye': 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    'shield-exclamation': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    'cog': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
};

const Icon = defineComponent({
    props: ['name', 'active'],
    render() {
        const path = icons[this.name] || 'M12 4v16m8-8H4'; // Fallback
        return h('svg', {
            class: ['mr-3 h-5 w-5 flex-shrink-0 transition-colors', this.active ? 'text-[#3d4adb]' : 'text-slate-400 group-hover:text-[#3d4adb]'],
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24',
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: path
            })
        ]);
    }
});
</script>

<template>
    <aside class="flex h-screen w-72 flex-col border-r border-slate-200 bg-white shadow-sm transition-all duration-300">
        <!-- Logo Area -->
        <div class="flex items-center gap-3 border-b border-slate-50 px-6 py-6">
            <Link :href="route('dashboard')" class="flex items-center gap-3 group">
                <img 
                    src="/images/logo.png" 
                    alt="University Logo" 
                    class="h-10 w-auto object-contain transition-transform duration-500 group-hover:scale-105"
                />
                <div class="flex flex-col">
                    <span class="text-[13px] font-bold tracking-tight text-slate-800 leading-none">
                        Borg Al Arab
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 mt-1 leading-none">
                        Technological Uni.
                    </span>
                </div>
            </Link>
        </div>

        <!-- Department Switcher -->
        <div class="px-4 py-5">
            <DepartmentSwitcher />
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 pb-6 space-y-7 scrollbar-hide mt-2">
            <!-- General Items -->
            <div class="space-y-1">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 transition-colors" :class="route().current('dashboard') ? 'text-[#3d4adb]' : 'text-slate-400 group-hover:text-[#3d4adb]'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </NavLink>
            </div>

            <!-- Dynamic Permission-Based Groups -->
            <div v-for="group in sidebarGroups" :key="group.group">
                <h3 class="px-3 mb-2.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400/80 flex items-center justify-between">
                    {{ group.group }}
                    <span class="h-px flex-1 bg-slate-100 ml-4"></span>
                </h3>
                <div class="space-y-1">
                    <NavLink 
                        v-for="item in group.items" 
                        :key="item.permission"
                        :href="route(item.route)" 
                        :active="route().current(item.route) || (item.route.endsWith('.index') && route().current(item.route.replace('.index', '.*')))"
                    >
                        <Icon :name="item.icon" :active="route().current(item.route) || (item.route.endsWith('.index') && route().current(item.route.replace('.index', '.*')))" />
                        {{ item.label }}
                    </NavLink>
                </div>
            </div>



            <!-- Empty State -->
            <div v-if="sidebarGroups.length === 0" class="px-6 py-10 text-center bg-slate-50/50 rounded-3xl border border-dashed border-slate-200">
                <p class="text-[11px] font-bold text-slate-300 uppercase tracking-widest">No modules</p>
            </div>
        </nav>

        <!-- Enterprise Access Card -->
        <div class="p-6 border-t border-slate-50">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100/50">
                <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Secure Session</span>
                </div>
                <p class="text-[10px] text-slate-400 font-medium leading-relaxed">Enterprise access active. Sessions are monitored for security.</p>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

