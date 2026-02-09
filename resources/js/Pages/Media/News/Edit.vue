<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    news: Object,
});

const form = useForm({
    title: props.news.title,
    description: props.news.description,
    content: props.news.content || '',
    image_file: null,
    category: props.news.category || 'Academic',
    publish_date: props.news.publish_date ? new Date(props.news.publish_date).toISOString().split('T')[0] : '',
    status: props.news.status,
    publish_to_facebook: props.news.publish_to_facebook,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('media.news.update', props.news.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit News" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex items-center justify-between">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Edit Article</h2>
                <Link :href="route('media.news.index')" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors">Back to Newsroom</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2rem] border border-slate-100">
                    <form @submit.prevent="submit" class="p-10 space-y-8">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Article Title</label>
                            <input v-model="form.title" type="text" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-lg" required />
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
                            <textarea v-model="form.description" rows="3" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-600 leading-relaxed" required></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Full Article Body</label>
                            <textarea v-model="form.content" rows="8" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-600 leading-relaxed"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Article Cover Image</label>
                            <div class="mt-4 flex flex-col md:flex-row items-center gap-6 p-6 border-2 border-slate-100 border-dashed rounded-[1.5rem] hover:border-indigo-200 transition-all">
                                <div v-if="news.image" class="w-40 h-28 rounded-xl overflow-hidden shadow-lg shrink-0 border border-white ring-4 ring-white shadow-slate-200 flex-shrink-0">
                                    <img :src="news.image" alt="Current" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1 text-center md:text-left">
                                     <div class="flex text-sm text-slate-500 justify-center md:justify-start">
                                        <label for="image_file" class="relative cursor-pointer bg-white rounded-md font-bold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <span>Click to replace image</span>
                                            <input id="image_file" type="file" @input="form.image_file = $event.target.files[0]" class="sr-only" />
                                        </label>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">Leave empty to keep existing image. WebP, PNG, JPG up to 5MB.</p>
                                    <div v-if="form.errors.image_file" class="text-red-500 text-xs mt-1">{{ form.errors.image_file }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-6 p-8 bg-slate-50 rounded-[2rem] border border-slate-100 shadow-inner">
                            <div class="flex flex-col md:flex-row md:items-center gap-10">
                                <div class="w-full md:w-1/3">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Status</label>
                                    <select v-model="form.status" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold">
                                        <option value="draft">Draft (Offline)</option>
                                        <option value="published">Published (Live)</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center gap-3 md:mt-6 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                                    <GlobalCheckbox v-model:checked="form.publish_to_facebook" id="fb_check" />
                                    <label for="fb_check" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        Post to Facebook page
                                    </label>
                                </div>
                            </div>
                            
                            <div v-if="news.facebook_publish_status !== 'none'" class="text-xs p-4 rounded-2xl bg-white border border-slate-100 flex items-center gap-4">
                                <div class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shrink-0">
                                    Facebook Integration
                                </div>
                                <div class="flex-1 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400">Current Status:</span>
                                        <span class="font-black text-slate-900 uppercase tracking-tight">{{ news.facebook_publish_status }}</span>
                                    </div>
                                    <div v-if="news.facebook_post_id" class="font-mono text-[10px] text-slate-400">ID: {{ news.facebook_post_id }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                             <Link :href="route('media.news.index')" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Discard</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-10 py-3 bg-indigo-600 text-white font-black rounded-xl hover:bg-slate-900 transition-all shadow-xl shadow-indigo-600/20 active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Article</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
