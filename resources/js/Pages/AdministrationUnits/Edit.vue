<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
});

const imagePreview = ref(props.unit.image_url);

const form = useForm({
    _method: 'PUT',
    title: props.unit.title,
    description: props.unit.description || '',
    director_name: props.unit.director_name,
    director_title: props.unit.director_title,
    director_image: null,
    access_password: '',
    display_order: props.unit.display_order,
    status: props.unit.status,
});

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.director_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    // We use POST with _method: 'PUT' to handle multi-part/form-data (required for file uploads in Laravel with PUT/PATCH)
    form.post(route('administration.update', props.unit.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Administration Unit" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('administration.index')" class="group rounded-full bg-white p-2 border border-gray-100 shadow-sm hover:bg-gray-50 transition-all">
                    <svg class="h-6 w-6 text-gray-400 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                        Edit Profile: {{ unit.title }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the administration card details and director information.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white rounded-[2.5rem] shadow-2xl shadow-gray-200/50 p-10 border border-gray-100 overflow-hidden">
                    <div class="space-y-12">
                        <!-- Primary Info -->
                        <section>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-8 flex items-center gap-4">
                                <span>Unit Information</span>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </h3>
                            
                            <div class="grid gap-8 grid-cols-1">
                                <div class="space-y-2">
                                    <InputLabel for="title" value="Department / Faculty Title" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                    <TextInput id="title" v-model="form.title" class="w-full h-16 !text-lg !rounded-2xl !bg-gray-50/50 !border-gray-100 focus:!bg-white shadow-sm" placeholder="e.g. Faculty of Industry" required />
                                    <InputError :message="form.errors.title" />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel for="description" value="Brief Description" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                    <textarea 
                                        id="description" 
                                        v-model="form.description" 
                                        class="w-full rounded-2xl border-gray-100 bg-gray-50/50 text-base py-4 px-5 focus:bg-white transition-all focus:ring-primary focus:border-primary shadow-sm min-h-[120px]" 
                                        placeholder="Describe the unit..."
                                    ></textarea>
                                    <InputError :message="form.errors.description" />
                                </div>
                            </div>
                        </section>

                        <!-- Director Info -->
                        <section>
                             <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-8 flex items-center gap-4">
                                <span>Director Profile</span>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </h3>

                            <div class="flex flex-col md:flex-row gap-10 items-start">
                                <!-- Image Upload -->
                                <div class="flex-shrink-0">
                                    <div class="relative group">
                                        <div class="h-40 w-40 rounded-[2.5rem] bg-gray-100 border-4 border-white shadow-xl overflow-hidden relative">
                                            <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full flex items-center justify-center text-gray-300">
                                                <svg class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            </div>
                                            <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                                <span class="text-white text-xs font-bold uppercase tracking-wider">Change Photo</span>
                                                <input type="file" @change="handleImageChange" class="hidden" accept="image/*" />
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.director_image" class="mt-4" />
                                </div>

                                <!-- Director Fields -->
                                <div class="flex-1 w-full space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <InputLabel for="director_name" value="Full Name" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                            <TextInput id="director_name" v-model="form.director_name" class="w-full h-14 !bg-gray-50/50 !border-gray-100" required />
                                            <InputError :message="form.errors.director_name" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel for="director_title" value="Job Title / Rank" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                            <TextInput id="director_title" v-model="form.director_title" class="w-full h-14 !bg-gray-50/50 !border-gray-100" required />
                                            <InputError :message="form.errors.director_title" />
                                        </div>
                                    </div>

                                    <div class="p-6 rounded-3xl bg-primary/5 border border-primary/10">
                                         <InputLabel for="access_password" value="Reset Security Password (Optional)" class="!text-[11px] !font-black !uppercase !tracking-widest !text-primary/60" />
                                         <TextInput id="access_password" type="password" v-model="form.access_password" class="w-full h-14 !bg-white !border-primary/20 mt-2 focus:!ring-primary/20" placeholder="Leave blank to keep current" auto-complete="new-password" />
                                         <InputError :message="form.errors.access_password" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Settings -->
                        <section>
                             <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-8 flex items-center gap-4">
                                <span>Configuration</span>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <InputLabel for="display_order" value="Display Priority (Order)" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                    <TextInput id="display_order" type="number" v-model="form.display_order" class="w-full h-14 !bg-gray-50/50 !border-gray-100" />
                                    <InputError :message="form.errors.display_order" />
                                </div>
                                <div class="space-y-2">
                                     <InputLabel for="status" value="Operational Status" class="!text-[11px] !font-black !uppercase !tracking-widest !text-gray-400" />
                                     <select id="status" v-model="form.status" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-5 text-sm focus:bg-white focus:ring-primary focus:border-primary shadow-sm transition-all appearance-none cursor-pointer">
                                         <option value="active">Active (Visible)</option>
                                         <option value="inactive">Inactive (Hidden)</option>
                                     </select>
                                     <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="mt-16 pt-10 border-t border-gray-50 flex flex-col sm:flex-row gap-4">
                        <AppButton type="submit" variant="primary" size="lg" class="flex-1 !h-16 !text-lg !rounded-3xl shadow-xl shadow-primary/20" :processing="form.processing">
                            Save Changes
                        </AppButton>
                        <AppButton :href="route('administration.index')" variant="secondary" size="lg" class="sm:w-40 !h-16 !rounded-3xl">
                            Discard
                        </AppButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
