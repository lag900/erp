<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    fb_page_url: props.settings.fb_page_url || '',
    fb_page_id: props.settings.fb_page_id || '',
    fb_access_token: props.settings.fb_access_token || '',
    fb_enabled: props.settings.fb_enabled,
    fb_auto_publish: props.settings.fb_auto_publish,
    ig_page_url: props.settings.ig_page_url || '',
    ig_enabled: props.settings.ig_enabled,
    ig_embed_token: props.settings.ig_embed_token || '',
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
                                            <GlobalCheckbox v-model:checked="form.fb_enabled" id="fb_enabled" />
                                            <label for="fb_enabled" class="text-sm font-bold text-slate-700">Enable Facebook Integration</label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <GlobalCheckbox v-model:checked="form.fb_auto_publish" id="fb_auto" />
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

                            <!-- Instagram Integration Section -->
                            <div class="border-b pb-8">
                                <h3 class="text-lg font-bold text-pink-700 mb-6 flex items-center gap-2">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.848 0-3.204.012-3.584.07-4.849.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    Instagram Page Integration
                                </h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <div class="flex items-center gap-4 bg-pink-50 p-4 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <GlobalCheckbox v-model:checked="form.ig_enabled" id="ig_enabled" />
                                            <label for="ig_enabled" class="text-sm font-bold text-slate-700">Enable Instagram Integration</label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Official Instagram URL</label>
                                        <input v-model="form.ig_page_url" type="url" placeholder="https://instagram.com/university_profile" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Instagram Embed Token / Script</label>
                                        <textarea v-model="form.ig_embed_token" rows="3" placeholder="Enter embed code or access token for feed display" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 font-mono text-xs"></textarea>
                                        <p class="text-[10px] text-gray-500 mt-1">Paste the embed code from a third-party feed service or your Facebook/Instagram Developer token.</p>
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
