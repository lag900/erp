<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

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
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Create News Article</h2>
                <Link :href="route('media.news.index')" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors">Back to Newsroom</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2rem] border border-slate-100">
                    <form @submit.prevent="submit" class="p-10 space-y-8">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Article Title</label>
                            <input v-model="form.title" type="text" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-lg" placeholder="Enter a compelling headline..." required />
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Category</label>
                                <select v-model="form.category" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold">
                                    <option>Academic</option>
                                    <option>Student Life</option>
                                    <option>Partnerships</option>
                                    <option>Events</option>
                                    <option>Official</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Publish Date</label>
                                <input v-model="form.publish_date" type="date" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Short Summary (Featured Text)</label>
                            <textarea v-model="form.description" rows="3" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-600 leading-relaxed" placeholder="A brief hook for the article card..." required></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Extended Content</label>
                            <textarea v-model="form.content" rows="8" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-600 leading-relaxed" placeholder="Write the full story here..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Featured Image</label>
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-100 border-dashed rounded-2xl hover:border-indigo-300 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-300" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-slate-500">
                                        <label for="image_file" class="relative cursor-pointer bg-white rounded-md font-bold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="image_file" type="file" @input="form.image_file = $event.target.files[0]" class="sr-only" />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-slate-400">WebP, PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                            <div v-if="form.image_file" class="mt-2 text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ form.image_file.name }} selected
                            </div>
                            <div v-if="form.errors.image_file" class="text-red-500 text-xs mt-1">{{ form.errors.image_file }}</div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center gap-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="w-full md:w-1/3">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Publishing Status</label>
                                <select v-model="form.status" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold">
                                    <option value="draft">Draft (Offline)</option>
                                    <option value="published">Published (Live)</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            
                            <div v-if="form.status === 'published'" class="flex items-center gap-3 md:mt-6 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                                <GlobalCheckbox v-model:checked="form.publish_to_facebook" id="fb_check" />
                                <label for="fb_check" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Post to Facebook page
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <Link :href="route('media.news.index')" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Discard</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-10 py-3 bg-indigo-600 text-white font-black rounded-xl hover:bg-slate-900 transition-all shadow-xl shadow-indigo-600/20 active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Publish Article</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
