<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    rooms: {
        type: Array,
        required: true,
    },
    subCategories: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    room_id: props.asset.room_id,
    sub_category_id: props.asset.sub_category_id,
    note: props.asset.note ?? '',
    count: props.asset.count ?? 1,
    infos: props.asset.infos?.length
        ? props.asset.infos.map((info) => ({
              id: info.id,
              key: info.key,
              value: info.value ?? '',
              image: null,
              image_url: info.image_url ?? null,
          }))
        : [{ key: '', value: '', image: null, image_url: null }],
});

const canAddMoreInfo = computed(() =>
    form.infos.some((info) => info.key || info.value || info.image || info.image_url)
);

const addInfoRow = () => {
    form.infos.push({ key: '', value: '', image: null, image_url: null });
};

const getImagePreview = (image, imageUrl) => {
    if (image instanceof File) {
        return URL.createObjectURL(image);
    }
    if (imageUrl) {
        return imageUrl;
    }
    return null;
};

const removeInfoRow = (index) => {
    if (form.infos.length === 1) {
        form.infos[0] = { key: '', value: '', image: '' };
        return;
    }
    form.infos.splice(index, 1);
};
</script>

<template>
    <Head title="Edit Asset" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Asset
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('assets.show', asset.id)"
                        class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Back to Details
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
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form
                    class="rounded bg-white p-6 shadow"
                    @submit.prevent="
                        form.post(route('assets.update', asset.id), {
                            method: 'put',
                            forceFormData: true,
                        })
                    "
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="room_id" value="Room" />
                            <select
                                id="room_id"
                                v-model="form.room_id"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select room</option>
                                <option
                                    v-for="room in rooms"
                                    :key="room.id"
                                    :value="room.id"
                                >
                                    {{ room.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.room_id" />
                        </div>

                        <div>
                            <InputLabel for="sub_category_id" value="Subcategory" />
                            <select
                                id="sub_category_id"
                                v-model="form.sub_category_id"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select subcategory</option>
                                <option
                                    v-for="subCategory in subCategories"
                                    :key="subCategory.id"
                                    :value="subCategory.id"
                                >
                                    {{ subCategory.label }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.sub_category_id"
                            />
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="count" value="Count" />
                            <TextInput
                                id="count"
                                v-model.number="form.count"
                                type="number"
                                min="1"
                                class="mt-1 block w-full"
                                placeholder="Number of items"
                            />
                            <InputError class="mt-2" :message="form.errors.count" />
                        </div>

                        <div>
                            <InputLabel for="note" value="Note" />
                            <textarea
                                id="note"
                                v-model="form.note"
                                rows="3"
                                class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Additional notes"
                            />
                            <InputError class="mt-2" :message="form.errors.note" />
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Asset Information
                            </h3>
                            <button
                                type="button"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                :disabled="!canAddMoreInfo"
                                @click="addInfoRow"
                            >
                                Add Row
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div
                                v-for="(info, index) in form.infos"
                                :key="info.id ?? index"
                                class="rounded border border-gray-200 p-4"
                            >
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <InputLabel
                                            :for="`info-key-${index}`"
                                            value="Key"
                                        />
                                        <TextInput
                                            :id="`info-key-${index}`"
                                            v-model="info.key"
                                            class="mt-1 block w-full"
                                            placeholder="e.g. ip"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            :for="`info-value-${index}`"
                                            value="Value"
                                        />
                                        <TextInput
                                            :id="`info-value-${index}`"
                                            v-model="info.value"
                                            class="mt-1 block w-full"
                                            placeholder="e.g. 192.168.1.10"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            :for="`info-image-${index}`"
                                            value="Image (optional)"
                                        />
                                        <div v-if="getImagePreview(info.image, info.image_url)" class="mb-2">
                                            <img
                                                :src="getImagePreview(info.image, info.image_url)"
                                                alt="Preview"
                                                class="h-24 w-24 rounded border border-gray-300 object-cover"
                                            />
                                        </div>
                                        <input
                                            :id="`info-image-${index}`"
                                            type="file"
                                            accept="image/*"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                            @input="info.image = $event.target.files[0]"
                                        />
                                        <InputError class="mt-2" :message="form.errors[`infos.${index}.image`]" />
                                        <p class="mt-1 text-xs text-gray-500">
                                            Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button
                                        type="button"
                                        class="text-xs text-red-600 hover:text-red-700"
                                        @click="removeInfoRow(index)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <InputError class="mt-2" :message="form.errors['infos.*.key']" />
                    </div>

                    <div class="mt-8 flex items-center justify-between gap-3">
                        <DangerButton
                            v-if="can('asset-delete')"
                            type="button"
                            @click="$inertia.delete(route('assets.destroy', asset.id))"
                        >
                            Delete Asset
                        </DangerButton>
                        <PrimaryButton
                            v-if="can('asset-edit')"
                            :disabled="form.processing"
                        >
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
