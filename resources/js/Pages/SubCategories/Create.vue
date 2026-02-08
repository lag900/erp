<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const form = useForm({
    category_id: '',
    name: '',
    name_ar: '',
    code: '',
    image: null,
});

// Auto-generate code based on name
watch(() => form.name, (newName) => {
    if (newName && newName.length >= 3) {
        const base = newName
            .replace(/[^A-Za-z]/g, '')
            .substring(0, 3)
            .toUpperCase();
        
        if (base.length === 3) {
             form.code = base;
        }
    }
});
</script>

<template>
    <Head title="Add Sub-category" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                     <nav class="flex mb-2" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                            <li><Link :href="route('subcategories.index')" class="hover:text-primary transition-colors">Sub-categories</Link></li>
                            <li><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5"/></svg></li>
                            <li class="text-gray-900">Create New</li>
                        </ol>
                    </nav>
                    <h2 class="text-3xl font-black leading-tight text-gray-900 uppercase tracking-tight">
                        Create Sub-category
                    </h2>
                </div>
                <Link
                    :href="route('subcategories.index')"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900"
                >
                    <svg class="mr-2 -ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-premium"
                    @submit.prevent="form.post(route('subcategories.store'), {
                        forceFormData: true,
                    })"
                >
                    <div class="border-b border-gray-50 bg-gray-50/50 px-8 py-6">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Sub-category Blueprint</h3>
                        <p class="mt-1 text-xs font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Hierarchy & Asset Serialization Data</p>
                    </div>

                    <div class="p-8 space-y-8">
                        <div>
                            <InputLabel for="category_id" value="Parent Category" class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <div class="relative">
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="block w-full rounded-xl border-gray-200 bg-white py-3 pl-4 pr-10 text-sm font-bold shadow-sm transition-all focus:border-primary focus:ring-primary focus:ring-offset-0"
                                >
                                    <option value="" disabled>Select a parent category...</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }} {{ category.name_ar ? `(${category.name_ar})` : '' }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Sub-category Name (English)" class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="block w-full rounded-xl border-gray-200 py-3 font-bold shadow-sm transition-all focus:border-primary focus:ring-primary h-12"
                                placeholder="e.g. Scientific Equipment"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="name_ar" value="Sub-category Name (Arabic)" class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <TextInput
                                id="name_ar"
                                v-model="form.name_ar"
                                dir="rtl"
                                class="block w-full rounded-xl border-gray-200 py-3 font-bold shadow-sm transition-all focus:border-primary focus:ring-primary h-12 text-right"
                                placeholder="مثال: أثاث مدرسي"
                            />
                            <InputError class="mt-2" :message="form.errors.name_ar" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Auto-Generated Code" class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <div class="relative">
                                <TextInput
                                    id="code"
                                    v-model="form.code"
                                    class="block w-full rounded-xl border-gray-100 bg-gray-50/50 py-3 font-mono font-black text-primary shadow-inner h-12"
                                    placeholder="SCI"
                                    readonly
                                />
                                <div class="absolute inset-y-0 right-4 flex items-center">
                                    <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">System Lock</span>
                                </div>
                            </div>
                            <p class="mt-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">This code is used for enterprise-wide asset serialization.</p>
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>

                        <div>
                            <InputLabel for="image" value="Visual Identifier" class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2" />
                            <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-gray-100 bg-gray-50/50 px-6 py-10 transition-all hover:bg-gray-50 hover:border-primary/20">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-200" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="mt-4 flex text-xs font-bold uppercase tracking-widest text-gray-500 justify-center">
                                        <label for="image" class="relative cursor-pointer rounded-md font-black text-primary hover:text-primary/80 transition-colors">
                                            <span>Upload Asset Icon</span>
                                            <input
                                                id="image"
                                                type="file"
                                                accept="image/*"
                                                class="sr-only"
                                                @input="form.image = $event.target.files[0]"
                                            />
                                        </label>
                                    </div>
                                    <p class="mt-1 text-[10px] font-bold text-gray-300 uppercase tracking-widest">Institutional standard: PNG/JPG/SVG < 2MB</p>
                                    <div v-if="form.image" class="mt-4 inline-flex items-center rounded-lg bg-green-50 px-3 py-1 ring-1 ring-inset ring-green-600/20">
                                         <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">Ready: {{ form.image.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.image" />
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-8 py-6 flex items-center justify-end gap-4 border-t border-gray-100">
                        <Link
                            :href="route('subcategories.index')"
                            class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors"
                        >
                            Abort
                        </Link>
                        <PrimaryButton 
                            v-if="can('sub_category-create')" 
                            :disabled="form.processing"
                            class="!rounded-xl shadow-premium px-8 h-12 !text-[11px] font-black uppercase tracking-[0.1em]"
                        >
                            Verify & Save Sub-category
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
