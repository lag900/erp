<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import axios from 'axios';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    subCategories: {
        type: Array,
        required: true,
    },
});

const componentForm = useForm({
    sub_category_id: '',
    status: 'active',
    asset_code: '',
    note: '',
});

const bundleComponents = ref(props.asset.children || []);
const pendingComponents = ref([]);
const componentSelectRef = ref(null);
const successMessage = ref(null);
const isSaving = ref(false);

const addComponentToPending = () => {
    if (!componentForm.sub_category_id) return;

    const selectedSubCategory = props.subCategories.find(s => s.id === componentForm.sub_category_id);
    
    pendingComponents.value.push({
        ...componentForm.data(),
        temp_id: Date.now(),
        sub_category_name: selectedSubCategory?.name || 'Unknown'
    });
    
    // Smart Reset for Speed
    const savedStatus = componentForm.status;
    componentForm.reset();
    componentForm.status = savedStatus; // Keep status
    
    // Auto-open next for speed
    setTimeout(() => {
            componentSelectRef.value?.open();
    }, 100);
};

const removePending = (index) => {
    pendingComponents.value.splice(index, 1);
};

const saveAllComponents = () => {
    if (pendingComponents.value.length === 0) return;
    
    isSaving.value = true;
    
    axios.post(route('assets.store-components', props.asset.id), {
        components: pendingComponents.value
    })
    .then(response => {
        // Success
        bundleComponents.value = [...response.data.components, ...bundleComponents.value];
        pendingComponents.value = [];
        successMessage.value = 'All components saved successfully!';
        setTimeout(() => { successMessage.value = null; }, 3000);
    })
    .catch(error => {
        alert('Failed to save components. Please check for errors.');
        console.error(error);
    })
    .finally(() => {
        isSaving.value = false;
    });
};

const prefillComponent = (searchText) => {
    const match = props.subCategories.find(s => s.name.toLowerCase().includes(searchText.toLowerCase()));
    if (match) {
        componentForm.sub_category_id = match.id;
        // Optionally auto-add if we want extreme speed, but for now just select
    }
};

const finish = () => {
    router.visit(route('assets.show', props.asset.id));
};
</script>

<template>
    <Head title="Add Components to System" />

    <!-- Full Screen Workflow Layout -->
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0 shadow-sm z-10">
            <div>
                <div class="flex items-center gap-2">
                     <span class="bg-[#1FA6A0] text-white text-[10px] font-black uppercase px-2 py-0.5 rounded">Composite System</span>
                     <h2 class="text-lg font-bold text-gray-900">{{ props.asset.sub_category?.name || 'Asset Bundle' }}</h2>
                </div>
                <p class="text-xs text-gray-500 mt-1 font-mono">{{ props.asset.asset_code }}</p>
            </div>
            <button @click="finish" class="text-xs font-bold text-gray-500 hover:text-gray-900 uppercase tracking-widest border border-gray-300 px-6 py-2.5 rounded hover:bg-gray-50 bg-white transition-colors shadow-sm">
                Finish & Close
            </button>
        </div>

        <!-- Success Toast -->
        <transition name="fade">
            <div v-if="successMessage" class="absolute top-20 left-1/2 transform -translate-x-1/2 z-50 bg-[#1FA6A0] text-white py-2 px-6 rounded-full text-sm font-bold shadow-lg flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                {{ successMessage }}
            </div>
        </transition>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
            <!-- Left: Fast Entry Form -->
            <div class="flex-1 p-6 md:p-10 overflow-y-auto flex flex-col">
                <div class="max-w-xl mx-auto w-full space-y-8 flex-1">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                             <svg class="w-5 h-5 text-[#1FA6A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                             Build System Inventory
                        </h3>
                        
                        <!-- Entry Form -->
                        <form @submit.prevent="addComponentToPending" class="space-y-4 bg-white p-6 rounded-xl shadow-sm border border-gray-200 relative">
                             <!-- Focus Ring Effect -->
                             <div class="absolute -left-1 top-6 bottom-6 w-1 bg-[#1FA6A0] rounded-r"></div>

                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-12 md:col-span-5">
                                    <SearchableSelect
                                        ref="componentSelectRef"
                                        v-model="componentForm.sub_category_id"
                                        :options="subCategories"
                                        label="Component"
                                        placeholder="Select OR Type..."
                                        class="w-full"
                                        autofocus
                                    />
                                </div>
                                <div class="col-span-6 md:col-span-3">
                                     <InputLabel value="Status" class="text-[10px] uppercase font-bold text-gray-400 mb-1" />
                                     <select v-model="componentForm.status" class="w-full text-sm py-2 rounded border-gray-200 bg-gray-50 font-bold focus:ring-[#1FA6A0] focus:border-[#1FA6A0]">
                                         <option value="active">Active</option>
                                         <option value="maintenance">Maintenance</option>
                                         <option value="damaged">Damaged</option>
                                     </select>
                                </div>
                                <div class="col-span-6 md:col-span-4">
                                    <button 
                                        type="submit" 
                                        class="w-full h-[38px] bg-gray-800 text-white text-[10px] font-black uppercase tracking-wider rounded hover:bg-black transition-colors flex items-center justify-center gap-1"
                                    >
                                        Add Row
                                        <kbd class="hidden md:inline font-mono px-1 py-0.5 bg-gray-700 rounded text-[9px] text-gray-300">↵</kbd>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Suggestions -->
                            <div class="flex flex-wrap gap-2 pt-2">
                                <button type="button" @click="prefillComponent('Monitor')" class="px-2 py-1 rounded border border-gray-100 bg-gray-50 text-[10px] font-medium text-gray-500 hover:border-[#1FA6A0] hover:text-[#1FA6A0] transition-colors">+ Monitor</button>
                                <button type="button" @click="prefillComponent('Keyboard')" class="px-2 py-1 rounded border border-gray-100 bg-gray-50 text-[10px] font-medium text-gray-500 hover:border-[#1FA6A0] hover:text-[#1FA6A0] transition-colors">+ Keyboard</button>
                                <button type="button" @click="prefillComponent('Mouse')" class="px-2 py-1 rounded border border-gray-100 bg-gray-50 text-[10px] font-medium text-gray-500 hover:border-[#1FA6A0] hover:text-[#1FA6A0] transition-colors">+ Mouse</button>
                                <button type="button" @click="prefillComponent('CPU')" class="px-2 py-1 rounded border border-gray-100 bg-gray-50 text-[10px] font-medium text-gray-500 hover:border-[#1FA6A0] hover:text-[#1FA6A0] transition-colors">+ CPU</button>
                            </div>
                        </form>
                    </div>

                    <!-- Pending List -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-3">
                             <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Pending Components ({{ pendingComponents.length }})</h4>
                             <span v-if="pendingComponents.length > 0" class="text-[10px] text-orange-500 font-bold animate-pulse">Not Saved Yet</span>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase">Component</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase">Status</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="(item, idx) in pendingComponents" :key="item.temp_id" class="group hover:bg-orange-50/30 transition-colors">
                                        <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ item.sub_category_name }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500 uppercase font-medium">{{ item.status }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button @click="removePending(idx)" class="text-red-400 hover:text-red-600 p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="pendingComponents.length === 0">
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400 text-xs italic">
                                            Add components above to build your list.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Save Action -->
                    <div class="pt-4 sticky bottom-0 bg-gray-100 pb-2">
                        <button 
                            @click="saveAllComponents"
                            :disabled="pendingComponents.length === 0 || isSaving"
                            class="w-full h-14 bg-[#1FA6A0] text-white text-xs font-black uppercase tracking-[0.2em] rounded-lg shadow-lg shadow-[#1FA6A0]/20 hover:bg-[#178a85] active:scale-95 transition-all disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-3"
                        >
                            <svg v-if="isSaving" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span v-else>Save All Components ({{ pendingComponents.length }})</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Saved Hierarchy -->
            <div class="w-full md:w-80 bg-white border-l border-gray-200 overflow-y-auto p-6 hidden md:block">
                 <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">System Snapshot</h3>
                 
                 <div class="relative pl-4 space-y-6">
                     <!-- Timeline Line -->
                     <div class="absolute left-0 top-2 bottom-0 w-0.5 bg-gray-100"></div>

                     <!-- Parent -->
                     <div class="relative">
                         <div class="absolute -left-[21px] top-0 w-3 h-3 rounded-full bg-blue-500 ring-4 ring-white"></div>
                         <p class="text-sm font-bold text-gray-900 leading-none">System Root</p>
                         <p class="text-xs text-gray-400 mt-1">{{ props.asset.sub_category?.name }}</p>
                     </div>

                     <!-- Children -->
                     <div v-for="comp in bundleComponents" :key="comp.id" class="relative">
                         <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-green-400 ring-4 ring-white"></div>
                         <p class="text-xs font-bold text-gray-700 leading-none">{{ comp.name }}</p>
                         <p class="text-[10px] text-gray-400 mt-0.5 font-mono">{{ comp.asset_code }}</p>
                     </div>
                     
                     <div v-if="bundleComponents.length === 0" class="pl-2 pt-4">
                        <p class="text-[10px] text-gray-300 uppercase tracking-wider">Empty System</p>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translate(-50%, -10px); }
</style>
