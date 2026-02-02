<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    rooms: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const filteredRooms = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.rooms;
    }

    return props.rooms.filter((room) => {
        return (
            room.name.toLowerCase().includes(term) ||
            (room.code || '').toLowerCase().includes(term) ||
            (room.level || '').toLowerCase().includes(term) ||
            (room.building || '').toLowerCase().includes(term) ||
            (room.location || '').toLowerCase().includes(term)
        );
    });
});

const deleteForm = useForm({});

const deleteRoom = (roomId) => {
    deleteForm.delete(route('rooms.destroy', roomId));
};
</script>

<template>
    <Head title="Rooms" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Rooms
                </h2>
                <Link
                    v-if="can('room-create')"
                    :href="route('rooms.create')"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Add Room
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
                        placeholder="Search rooms..."
                    />
                </div>

                <div
                    v-if="filteredRooms.length === 0"
                    class="rounded bg-white p-6 shadow"
                >
                    <p class="text-gray-600">No rooms found.</p>
                </div>

                <div v-else class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Code</th>
                                <th class="px-4 py-3 font-medium">Level</th>
                                <th class="px-4 py-3 font-medium">Building</th>
                                <th class="px-4 py-3 font-medium">Location</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="room in filteredRooms" :key="room.id">
                                <td class="px-4 py-3 text-gray-800">
                                    {{ room.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ room.code || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ room.level || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ room.building || '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ room.location || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            v-if="can('room-edit')"
                                            :href="route('rooms.edit', room.id)"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </Link>
                                        <DangerButton
                                            v-if="can('room-delete')"
                                            type="button"
                                            class="text-xs"
                                            :disabled="deleteForm.processing"
                                            @click="deleteRoom(room.id)"
                                        >
                                            Delete
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
