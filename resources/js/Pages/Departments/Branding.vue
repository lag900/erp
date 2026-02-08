<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    department: Object,
});

const form = useForm({
    arabic_name: props.department.arabic_name ?? '',
    university_logo: null,
    department_logo: null,
});

const universityLogoPreview = ref(props.department.university_logo_url);
const departmentLogoPreview = ref(props.department.department_logo_url);

const handleUniversityLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.university_logo = file;
        universityLogoPreview.value = URL.createObjectURL(file);
    }
};

const handleDepartmentLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.department_logo = file;
        departmentLogoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('departments.branding.update', props.department.id), {
        onSuccess: () => {
            // Success notification or logic
        },
    });
};
</script>

<template>
    <Head title="Department Branding" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Branding & Identity: {{ department.name }}
                </h2>
                <span class="text-sm text-gray-400 font-medium">Arabic: {{ department.arabic_name || 'Not Set' }}</span>
            </div>
        </template>

        <div class="py-12 bg-gray-50/50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-soft rounded-2xl border border-gray-100">
                    <div class="p-8">
                        <div class="mb-8 border-b border-gray-50 pb-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Institutional Branding</h3>
                            <p class="text-sm text-gray-500">Define how this department appears in official reports and Arabic interfaces.</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Arabic Name Section -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                                <div class="md:col-span-1">
                                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Arabic Identity</h4>
                                    <p class="text-xs text-gray-500">Official department name in Arabic script.</p>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="max-w-md" dir="rtl">
                                        <InputLabel for="arabic_name" value="اسم القسم باللغة العربية" class="text-right" />
                                        <TextInput
                                            id="arabic_name"
                                            type="text"
                                            class="mt-1 block w-full text-right font-arabic"
                                            v-model="form.arabic_name"
                                            placeholder="أدخل اسم القسم باللغة العربية..."
                                        />
                                        <InputError class="mt-2" :message="form.errors.arabic_name" />
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-50" />

                            <!-- University Logo Section -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                                <div class="md:col-span-1">
                                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">University Logo</h4>
                                    <p class="text-xs text-gray-500">The primary logo of the institution.</p>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="flex items-center gap-6">
                                        <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 shadow-inner">
                                            <img v-if="universityLogoPreview" :src="universityLogoPreview" class="max-h-full max-w-full object-contain p-2" />
                                            <svg v-else class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <input type="file" @change="handleUniversityLogoChange" class="hidden" id="university_logo_input" accept="image/*" />
                                            <label for="university_logo_input" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                                Update Logo
                                            </label>
                                            <p class="mt-2 text-[10px] text-gray-400 uppercase tracking-wider font-bold">Recommended: PNG with transparency, 512px+</p>
                                            <InputError class="mt-2" :message="form.errors.university_logo" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-50" />

                            <!-- Department Logo Section -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                                <div class="md:col-span-1">
                                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Department Logo</h4>
                                    <p class="text-xs text-gray-500">Optional logo specific to this department.</p>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="flex items-center gap-6">
                                        <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 shadow-inner">
                                            <img v-if="departmentLogoPreview" :src="departmentLogoPreview" class="max-h-full max-w-full object-contain p-2" />
                                            <svg v-else class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <input type="file" @change="handleDepartmentLogoChange" class="hidden" id="department_logo_input" accept="image/*" />
                                            <label for="department_logo_input" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                                Update Logo
                                            </label>
                                            <p class="mt-2 text-[10px] text-gray-400 uppercase tracking-wider font-bold">Optional: Left-side logo for reports</p>
                                            <InputError class="mt-2" :message="form.errors.department_logo" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Save Branding Profile
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="mt-8 overflow-hidden bg-white shadow-soft rounded-2xl border border-gray-100">
                    <div class="p-6 bg-gray-50 border-b border-gray-100">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Report Header Preview</h4>
                    </div>
                    <div class="p-8 bg-white overflow-x-auto">
                        <div class="min-w-[700px] border border-gray-200 rounded-lg p-10 bg-white shadow-sm" dir="rtl">
                            <div class="flex items-center justify-between border-b-2 border-black pb-6 mb-6">
                                <div class="h-20 w-20 flex-shrink-0">
                                    <img v-if="universityLogoPreview" :src="universityLogoPreview" class="h-full w-full object-contain" />
                                    <div v-else class="h-full w-full bg-gray-100 rounded flex items-center justify-center text-[10px] text-gray-300">University</div>
                                </div>
                                <div class="text-center flex-1">
                                    <p class="text-lg font-bold leading-tight">جامعة باتو</p>
                                    <p class="text-xl font-black mt-1 leading-tight">{{ form.arabic_name || props.department.name }}</p>
                                    <p class="text-sm mt-1 text-gray-600">تقرير جرد الأصول الرسمي</p>
                                </div>
                                <div class="h-20 w-20 flex-shrink-0">
                                    <img v-if="departmentLogoPreview" :src="departmentLogoPreview" class="h-full w-full object-contain" />
                                    <div v-else class="h-full w-full bg-gray-100 rounded flex items-center justify-center text-[10px] text-gray-300">Department</div>
                                </div>
                            </div>
                            <div class="h-40 bg-gray-50/50 rounded flex items-center justify-center text-gray-300 italic text-sm">
                                [ محتوى التقرير سيظهر هنا ]
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-arabic {
    font-family: 'Amiri', 'Traditional Arabic', serif;
}
.shadow-soft {
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
}
</style>
