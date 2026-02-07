<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    news: Object,
});

const form = useForm({});

const deleteNews = (id) => {
    if (confirm('Are you sure you want to delete this news?')) {
        form.delete(route('media.news.destroy', id));
    }
};
</script>

<template>
    <Head title="News Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">News & Events Management</h2>
                <Link
                    :href="route('media.news.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                >
                    Create News
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facebook</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publish Date</th>
                                    <th class="px-6 py-3 bg-gray-50"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in news.data" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span :class="{
                                            'px-2 py-1 rounded-full text-xs font-bold': true,
                                            'bg-green-100 text-green-800': item.status === 'published',
                                            'bg-yellow-100 text-yellow-800': item.status === 'draft',
                                            'bg-red-100 text-red-800': item.status === 'archived'
                                        }">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col gap-1">
                                            <span v-if="item.publish_to_facebook" class="text-blue-600 font-bold text-xs uppercase">Enabled</span>
                                            <span v-else class="text-gray-400 text-xs uppercase">Disabled</span>
                                            <span :class="{
                                                'text-[10px] font-medium px-1.5 py-0.5 rounded border inline-block w-fit': true,
                                                'border-green-200 bg-green-50 text-green-600': item.facebook_publish_status === 'published',
                                                'border-yellow-200 bg-yellow-50 text-yellow-600': item.facebook_publish_status === 'pending',
                                                'border-red-200 bg-red-50 text-red-600': item.facebook_publish_status === 'failed',
                                                'border-gray-200 bg-gray-50 text-gray-400': item.facebook_publish_status === 'none'
                                            }">
                                                {{ item.facebook_publish_status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.publish_date ? new Date(item.publish_date).toLocaleDateString() : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('media.news.edit', item.id)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                        <button @click="deleteNews(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Simple Pagination Link (Optional if data is large) -->
                        <div class="mt-6" v-if="news.links && news.links.length > 3">
                            <!-- Pagination logic here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
