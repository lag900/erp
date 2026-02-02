<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    assets: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredAssets = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.assets;
    }

    return props.assets.filter((asset) => {
        const roomParts = [
            asset.room?.location,
            asset.room?.building,
            asset.room?.level,
            asset.room?.name,
            asset.room?.code,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const categoryText = [asset.category, asset.subCategory]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const noteText = asset.note ? asset.note.toLowerCase() : '';

        return (
            roomParts.includes(term) ||
            categoryText.includes(term) ||
            noteText.includes(term)
        );
    });
});
</script>

<template>
    <Head title="Assets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Assets
                </h2>
                <Link
                    v-if="can('asset-create')"
                    :href="route('assets.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Asset
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Search by room, location, category, or note..."
                    />
                </div>

                <div
                    v-if="filteredAssets.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No assets found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Location</th>
                                <th class="px-4 py-3 font-medium">Category</th>
                                <th class="px-4 py-3 font-medium">Subcategory</th>
                                <th class="px-4 py-3 font-medium">Count</th>
                                <th class="px-4 py-3 font-medium">Note</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="asset in filteredAssets" :key="asset.id">
                                <td class="px-4 py-3 text-gray-800">
                                    <div class="font-medium">
                                        {{ asset.room?.name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{
                                            [
                                                asset.room?.location,
                                                asset.room?.building,
                                                asset.room?.level,
                                                asset.room?.code,
                                            ]
                                                .filter(Boolean)
                                                .join(' - ')
                                        }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ asset.category ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ asset.subCategory ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 font-semibold">
                                    {{ asset.count ?? 1 }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ asset.note ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        v-if="can('asset-list')"
                                        :href="route('assets.show', asset.id)"
                                        class="text-indigo-600 hover:text-indigo-700"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
