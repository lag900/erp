<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const selectedDepartmentName = computed(() => {
    const payload = page.props.department ?? null;
    if (!payload || !payload.selectedId || !Array.isArray(payload.list)) {
        return null;
    }

    const match = payload.list.find(
        (department) => department.id === payload.selectedId
    );

    return match?.name ?? null;
});

const permissions = computed(() => page.props.auth?.permissions ?? []);
const enabledFeatures = computed(() => page.props.department?.featuresEnabled ?? []);

const can = (permission) => permissions.value.includes(permission);
const hasFeature = (featureKey) => enabledFeatures.value.includes(featureKey);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div class="flex min-h-screen">
            <aside class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex">
                <div class="flex items-center gap-3 px-6 py-5">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                    </Link>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">ERB</p>
                        <p class="text-xs text-gray-500">Management System</p>
                    </div>
                </div>

                <div v-if="selectedDepartmentName" class="border-b border-gray-200 px-6 pb-4">
                    <p class="text-xs uppercase text-gray-400">Active Department</p>
                    <p class="mt-1 text-sm font-semibold text-gray-700">
                        {{ selectedDepartmentName }}
                    </p>
                    <Link
                        :href="route('departments.select')"
                        class="mt-2 inline-flex text-xs font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        Switch Department
                    </Link>
                </div>

                <nav class="flex-1 space-y-1 px-4 pb-6 text-sm">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </NavLink>
                    <NavLink
                        v-if="can('asset-list') && hasFeature('assets')"
                        :href="route('assets.index')"
                        :active="route().current('assets.*')"
                    >
                        Assets
                    </NavLink>
                    <NavLink
                        v-if="can('location-list') && hasFeature('assets')"
                        :href="route('locations.index')"
                        :active="route().current('locations.*')"
                    >
                        Locations
                    </NavLink>
                    <NavLink
                        v-if="can('building-list') && hasFeature('assets')"
                        :href="route('buildings.index')"
                        :active="route().current('buildings.*')"
                    >
                        Buildings
                    </NavLink>
                    <NavLink
                        v-if="can('level-list') && hasFeature('assets')"
                        :href="route('levels.index')"
                        :active="route().current('levels.*')"
                    >
                        Levels
                    </NavLink>
                    <NavLink
                        v-if="can('room-list') && hasFeature('assets')"
                        :href="route('rooms.index')"
                        :active="route().current('rooms.*')"
                    >
                        Rooms
                    </NavLink>
                    <NavLink
                        v-if="can('category-list') && hasFeature('assets')"
                        :href="route('categories.index')"
                        :active="route().current('categories.*')"
                    >
                        Categories
                    </NavLink>
                    <NavLink
                        v-if="can('sub_category-list') && hasFeature('assets')"
                        :href="route('subcategories.index')"
                        :active="route().current('subcategories.*')"
                    >
                        Subcategories
                    </NavLink>
                    <NavLink
                        v-if="can('department-list')"
                        :href="route('departments.index')"
                        :active="route().current('departments.index')"
                    >
                        Departments
                    </NavLink>
                    <NavLink
                        v-if="can('feature-toggle')"
                        :href="route('departments.features')"
                        :active="route().current('departments.features')"
                    >
                        Features
                    </NavLink>
                    <NavLink
                        v-if="can('user-list')"
                        :href="route('users.index')"
                        :active="route().current('users.*')"
                    >
                        Users
                    </NavLink>
                    <NavLink
                        v-if="can('role-list')"
                        :href="route('roles.index')"
                        :active="route().current('roles.*')"
                    >
                        Roles
                    </NavLink>
                    <NavLink
                        v-if="can('permission-list')"
                        :href="route('permissions.index')"
                        :active="route().current('permissions.*')"
                    >
                        Permissions
                    </NavLink>
                </nav>

                <div class="border-t border-gray-200 px-6 py-4">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $page.props.auth.user.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $page.props.auth.user.email }}
                    </p>
                    <div class="mt-3">
                        <Dropdown align="left" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition hover:text-gray-800"
                                    >
                                        Account
                                        <svg
                                            class="ms-2 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden">
                    <button
                        type="button"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <Link :href="route('dashboard')">
                        <ApplicationLogo class="block h-8 w-auto fill-current text-gray-800" />
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition hover:text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </header>

                <div
                    v-if="showingNavigationDropdown"
                    class="border-b border-gray-200 bg-white px-4 py-4 lg:hidden"
                >
                    <nav class="space-y-1 text-sm">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </NavLink>
                        <NavLink
                            v-if="can('asset-list') && hasFeature('assets')"
                            :href="route('assets.index')"
                            :active="route().current('assets.*')"
                        >
                            Assets
                        </NavLink>
                        <NavLink
                            v-if="can('location-list') && hasFeature('assets')"
                            :href="route('locations.index')"
                            :active="route().current('locations.*')"
                        >
                            Locations
                        </NavLink>
                        <NavLink
                            v-if="can('building-list') && hasFeature('assets')"
                            :href="route('buildings.index')"
                            :active="route().current('buildings.*')"
                        >
                            Buildings
                        </NavLink>
                        <NavLink
                            v-if="can('level-list') && hasFeature('assets')"
                            :href="route('levels.index')"
                            :active="route().current('level.*')"
                        >
                            Levels
                        </NavLink>
                        <NavLink
                            v-if="can('room-list') && hasFeature('assets')"
                            :href="route('rooms.index')"
                            :active="route().current('rooms.*')"
                        >
                            Rooms
                        </NavLink>
                        <NavLink
                            v-if="can('category-list') && hasFeature('assets')"
                            :href="route('categories.index')"
                            :active="route().current('categories.*')"
                        >
                            Categories
                        </NavLink>
                        <NavLink
                            v-if="can('sub_category-list') && hasFeature('assets')"
                            :href="route('subcategories.index')"
                            :active="route().current('subcategories.*')"
                        >
                            Subcategories
                        </NavLink>
                        <NavLink
                            v-if="can('department-list')"
                            :href="route('departments.index')"
                            :active="route().current('departments.index')"
                        >
                            Departments
                        </NavLink>
                        <NavLink
                            v-if="can('feature-toggle')"
                            :href="route('departments.features')"
                            :active="route().current('departments.features')"
                        >
                            Features
                        </NavLink>
                        <NavLink
                            v-if="can('user-list')"
                            :href="route('users.index')"
                            :active="route().current('users.*')"
                        >
                            Users
                        </NavLink>
                        <NavLink
                            v-if="can('role-list')"
                            :href="route('roles.index')"
                            :active="route().current('roles.*')"
                        >
                            Roles
                        </NavLink>
                        <NavLink
                            v-if="can('permission-list')"
                            :href="route('permissions.index')"
                            :active="route().current('permissions.*')"
                        >
                            Permissions
                        </NavLink>
                    </nav>
                </div>

                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
