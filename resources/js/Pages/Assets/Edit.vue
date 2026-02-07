<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

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
    departments: {
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
    serial_number: props.asset.serial_number ?? '',
    condition: props.asset.condition ?? 'active',
    is_shared: !!props.asset.is_shared,
    shared_department_ids: props.asset.shared_departments_ids ?? [],
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
        form.infos[0] = { key: '', value: '', image: null, image_url: null };
        return;
    }
    form.infos.splice(index, 1);
};
</script>

<template>
    <Head title="Edit Asset" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('assets.show', asset.id)"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:bg-gray-50 hover:text-primary active:scale-95 shadow-soft"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold leading-tight text-gray-800">
                            Modify Asset Record
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">Updating metadata for <span class="font-bold text-gray-700">#{{ asset.serial_number || asset.id }}</span></p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <form
                    @submit.prevent="
                        form.post(route('assets.update', asset.id), {
                            _method: 'put',
                            forceFormData: true,
                        })
                    "
                    class="space-y-8"
                >
                    <!-- Card: Core Logistics -->
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-premium">
                        <div class="border-b border-gray-50 bg-gray-50/50 px-6 py-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-900">Deployment & Identity</h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="room_id" value="Assigned Room" />
                                    <select
                                        id="room_id"
                                        v-model="form.room_id"
                                        class="mt-1 block w-full rounded-xl border-gray-200 bg-white py-3 pl-4 pr-10 text-sm shadow-soft focus:border-primary focus:ring-primary transition-all"
                                    >
                                        <option value="" disabled>Select Room</option>
                                        <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.label }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.room_id" />
                                </div>

                                <div>
                                    <InputLabel for="sub_category_id" value="Asset Type" />
                                    <select
                                        id="sub_category_id"
                                        v-model="form.sub_category_id"
                                        class="mt-1 block w-full rounded-xl border-gray-200 bg-white py-3 pl-4 pr-10 text-sm shadow-soft focus:border-primary focus:ring-primary transition-all"
                                    >
                                        <option value="" disabled>Select Type</option>
                                        <option v-for="cat in subCategories" :key="cat.id" :value="cat.id">{{ cat.label }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.sub_category_id" />
                                </div>

                                <div>
                                    <InputLabel for="serial_number" value="Serial Number" />
                                    <TextInput
                                        id="serial_number"
                                        v-model="form.serial_number"
                                        type="text"
                                        class="mt-1 block w-full border-gray-200 h-11"
                                        placeholder="e.g. SN-8829-X"
                                    />
                                    <InputError class="mt-2" :message="form.errors.serial_number" />
                                </div>

                                <div>
                                    <InputLabel for="condition" value="Current Condition" />
                                    <select
                                        id="condition"
                                        v-model="form.condition"
                                        class="mt-1 block w-full rounded-xl border-gray-200 bg-white py-3 pl-4 pr-10 text-sm shadow-soft focus:border-primary focus:ring-primary transition-all"
                                    >
                                        <option value="active">Active / Optimal</option>
                                        <option value="maintenance">Maintenance Required</option>
                                        <option value="damaged">Damaged / Non-functional</option>
                                        <option value="disposed">Disposed / Retired</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.condition" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Ownership & Sharing -->
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-premium">
                        <div class="border-b border-gray-50 bg-gray-50/50 px-6 py-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-900">Governance & Sharing</h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" v-model="form.is_shared" class="peer sr-only" />
                                    <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary peer-checked:after:translate-x-full peer-checked:after:border-white shadow-inner"></div>
                                    <span class="ml-4 text-sm font-bold text-gray-900 tracking-tight">Enable Multi-Department Visibility</span>
                                </label>
                            </div>

                            <div v-if="form.is_shared" class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4">
                                <template v-for="dept in departments" :key="dept.id">
                                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/30 transition-all hover:bg-white hover:shadow-soft">
                                        <input
                                            :id="`dept-edit-${dept.id}`"
                                            type="checkbox"
                                            v-model="form.shared_department_ids"
                                            :value="dept.id"
                                            class="h-4 w-4 rounded border-gray-200 text-primary focus:ring-primary"
                                        />
                                        <label :for="`dept-edit-${dept.id}`" class="text-xs font-bold text-gray-600 cursor-pointer uppercase tracking-tighter">{{ dept.name }}</label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Technical Specifications -->
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-premium">
                        <div class="flex items-center justify-between border-b border-gray-50 bg-gray-50/50 px-6 py-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-900">Technical Specifications</h3>
                            <button type="button" @click="addInfoRow" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-hover transition-all">Append Specification</button>
                        </div>
                        <div class="p-8 space-y-6">
                            <div v-for="(info, index) in form.infos" :key="index" class="relative rounded-2xl border border-gray-100 p-6 group transition-all hover:bg-gray-50/30">
                                <button type="button" @click="removeInfoRow(index)" class="absolute top-4 right-4 h-8 w-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 hover:text-white">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <InputLabel value="Specification Key" class="text-[10px] uppercase font-black tracking-widest text-gray-400 mb-2" />
                                        <TextInput v-model="info.key" class="w-full border-gray-200 h-10 text-sm" placeholder="e.g. Model, CPU..." />
                                    </div>
                                    <div>
                                        <InputLabel value="Specification Value" class="text-[10px] uppercase font-black tracking-widest text-gray-400 mb-2" />
                                        <TextInput v-model="info.value" class="w-full border-gray-200 h-10 text-sm" placeholder="e.g. Core i9, Dell XPS..." />
                                    </div>
                                    <div>
                                        <InputLabel value="Reference Image" class="text-[10px] uppercase font-black tracking-widest text-gray-400 mb-2" />
                                        <div class="flex items-center gap-4">
                                            <div v-if="getImagePreview(info.image, info.image_url)" class="h-10 w-10 flex-shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-white">
                                                <img :src="getImagePreview(info.image, info.image_url)" class="h-full w-full object-cover" />
                                            </div>
                                            <input type="file" @input="info.image = $event.target.files[0]" class="text-[10px] text-gray-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gray-100 file:text-gray-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pb-10">
                        <p class="text-xs text-gray-400 italic font-medium tracking-tight">System will log this update under your administrator ID for the audit trail.</p>
                        <div class="flex gap-4">
                            <Link :href="route('assets.show', asset.id)" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 transition-all uppercase tracking-widest">Cancel</Link>
                            <PrimaryButton :disabled="form.processing" class="shadow-soft px-8 py-3 rounded-xl transition-all active:scale-95">Commit All Changes</PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
