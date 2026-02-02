<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    roomAssetsSummary: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({});
</script>

<template>
    <Head title="Asset Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Asset Details
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-if="can('asset-edit')"
                        :href="route('assets.edit', asset.id)"
                        class="rounded border border-indigo-600 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-50"
                    >
                        Edit Asset
                    </Link>
                    <Link
                        :href="route('assets.index')"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to Assets
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded bg-white p-6 shadow">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ asset.room?.location ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{
                                    [
                                        asset.room?.building,
                                        asset.room?.level,
                                        asset.room?.name,
                                        asset.room?.code,
                                    ]
                                        .filter(Boolean)
                                        .join(' - ')
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Category</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ asset.category ?? '-' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ asset.subCategory ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Count</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ asset.count ?? 1 }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Note</p>
                        <p class="text-gray-700">
                            {{ asset.note ?? 'No notes provided.' }}
                        </p>
                    </div>
                </div>

                <div class="rounded bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Asset Information
                        </h3>
                        <PrimaryButton
                            v-if="can('asset-edit')"
                            type="button"
                            @click="$inertia.visit(route('assets.edit', asset.id))"
                        >
                            Update Info
                        </PrimaryButton>
                    </div>

                    <div v-if="asset.infos.length === 0" class="mt-4 text-gray-600">
                        No additional information.
                    </div>
                    <div v-else class="mt-4 space-y-3">
                        <div
                            v-for="info in asset.infos"
                            :key="info.id"
                            class="rounded border border-gray-200 p-4"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm text-gray-500">Key</p>
                                    <p class="text-gray-900">{{ info.key }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Value</p>
                                    <p class="text-gray-700">{{ info.value ?? '-' }}</p>
                                </div>
                            </div>
                            <div v-if="info.image_url" class="mt-3">
                                <p class="mb-2 text-sm text-gray-500">Image</p>
                                <img
                                    :src="info.image_url"
                                    :alt="info.key"
                                    class="h-32 w-32 rounded border border-gray-300 object-cover"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">
                        Room Assets Summary
                    </h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Summary of assets by subcategory in
                        <span class="font-medium">{{ asset.room?.name }}</span>
                    </p>
                    <div v-if="roomAssetsSummary.length === 0" class="text-gray-500">
                        No other assets in this room.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700">
                                        Category
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700">
                                        Subcategory
                                    </th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-700">
                                        Asset Records
                                    </th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-700">
                                        Total Count
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="summary in roomAssetsSummary"
                                    :key="summary.subcategory_id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ summary.category_name }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ summary.subcategory_name }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        {{ summary.asset_count }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-indigo-600">
                                        {{ summary.total_count }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Danger zone</p>
                            <p class="text-gray-700">Delete this asset permanently.</p>
                        </div>
                        <form
                            v-if="can('asset-delete')"
                            @submit.prevent="
                                form.delete(route('assets.destroy', asset.id))
                            "
                        >
                            <DangerButton
                                type="submit"
                                :disabled="form.processing"
                            >
                                Delete Asset
                            </DangerButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
