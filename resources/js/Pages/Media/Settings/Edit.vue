<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    fb_page_url: props.settings.fb_page_url || '',
    fb_page_id: props.settings.fb_page_id || '',
    fb_access_token: props.settings.fb_access_token || '',
    fb_enabled: props.settings.fb_enabled,
    fb_auto_publish: props.settings.fb_auto_publish,
});

const submit = () => {
    form.put(route('media.settings.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Settings updated successfully!'),
    });
};
</script>

<template>
    <Head title="Media Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Media & Social Integration Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-8 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800">
                            <h3 class="font-bold text-sm">Security Warning</h3>
                            <p class="text-xs">Only Super Admins should access this page. These credentials allow the system to post on the university's official social media pages.</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Facebook Integration Section -->
                            <div class="border-b pb-8">
                                <h3 class="text-lg font-bold text-blue-800 mb-6 flex items-center gap-2">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook Page Integration
                                </h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <div class="flex items-center gap-4 bg-blue-50 p-4 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <input v-model="form.fb_enabled" type="checkbox" id="fb_enabled" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                            <label for="fb_enabled" class="text-sm font-bold text-slate-700">Enable Facebook Integration</label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input v-model="form.fb_auto_publish" type="checkbox" id="fb_auto" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                            <label for="fb_auto" class="text-sm font-bold text-slate-700">Auto-Publish to Facebook</label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Facebook Page URL</label>
                                        <input v-model="form.fb_page_url" type="url" placeholder="https://facebook.com/university_page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Facebook Page ID</label>
                                            <input v-model="form.fb_page_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Page Access Token</label>
                                        <textarea v-model="form.fb_access_token" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs"></textarea>
                                        <p class="text-[10px] text-gray-500 mt-1">Generate this from Facebook Business Suite / Meta for Developers.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all shadow-md active:scale-95 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : 'Update Settings' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
