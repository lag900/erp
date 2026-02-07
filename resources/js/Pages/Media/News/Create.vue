<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    description: '',
    content: '',
    image_file: null,
    category: 'Academic',
    publish_date: new Date().toISOString().split('T')[0],
    status: 'draft',
    publish_to_facebook: false,
});

const submit = () => {
    form.post(route('media.news.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create News" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create News Article</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <select v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>Academic</option>
                                    <option>Student Life</option>
                                    <option>Partnerships</option>
                                    <option>Events</option>
                                    <option>Official</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Publish Date</label>
                                <input v-model="form.publish_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Short Description (Summary)</label>
                            <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Content</label>
                            <textarea v-model="form.content" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Featured Image</label>
                            <input type="file" @input="form.image_file = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <div v-if="form.errors.image_file" class="text-red-500 text-xs mt-1">{{ form.errors.image_file }}</div>
                        </div>

                        <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            
                            <div v-if="form.status === 'published'" class="flex items-center gap-2 mt-6">
                                <input v-model="form.publish_to_facebook" type="checkbox" id="fb_check" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <label for="fb_check" class="text-sm font-bold text-blue-800">Publish to University Facebook Page</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('media.news.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-[#064e3b] text-white font-bold rounded-lg hover:bg-[#065f46] transition-all disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Save News Article' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
