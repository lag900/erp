<template>
    <div class="relative w-full" ref="containerRef">
        <div class="relative">
            <input
                type="text"
                :value="modelValue"
                @input="handleInput"
                @focus="isOpen = true"
                class="block w-full rounded border-gray-300 bg-white px-3 py-2 text-sm transition-all focus:border-[#1FA6A0] focus:ring-0 focus:bg-white"
                :placeholder="placeholder"
            />
            <div class="absolute right-3 top-2.5">
                <svg v-if="isLoading" class="animate-spin h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <ul
                v-if="isOpen && filteredSuggestions.length > 0"
                class="absolute z-[100] mt-1 max-h-48 w-full overflow-auto rounded border border-gray-200 bg-white py-0 text-sm shadow-lg focus:outline-none"
            >
                <li
                    v-for="s in filteredSuggestions"
                    :key="s"
                    class="group relative flex items-center justify-between cursor-pointer select-none px-4 py-2.5 hover:bg-gray-50 hover:text-[#1FA6A0] font-bold transition-colors border-b border-gray-50 last:border-none"
                    @click="selectSuggestion(s)"
                >
                    <span class="text-xs uppercase tracking-wider">{{ s }}</span>
                    <button 
                        @click.stop="startRename(s)"
                        class="hidden group-hover:flex items-center justify-center p-1 rounded border border-gray-200 text-gray-400 hover:bg-white hover:text-[#1FA6A0] transition-all"
                        title="Rename Global Template"
                    >
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                </li>
            </ul>
        </transition>

        <!-- Rename Modal/Overlay (Formal) -->
        <div v-if="renamingKey" class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-900/40 backdrop-blur-[2px] p-4 text-left">
            <div class="w-full max-w-sm rounded bg-white p-8 shadow-2xl border border-gray-200" @click.stop>
                <h4 class="text-xs font-black uppercase tracking-[0.2em] text-gray-900 mb-2">Global Template Refactor</h4>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6 leading-relaxed">Renaming "{{ renamingKey }}" will affect the master registry for this category.</p>
                
                <div class="space-y-1 mb-8">
                    <InputLabel value="New Template Label" class="text-[10px] font-black uppercase text-gray-500" />
                    <input 
                        v-model="newKeyName" 
                        type="text" 
                        class="block w-full rounded border-gray-300 px-3 py-2 text-sm font-bold shadow-none focus:border-[#1FA6A0] focus:ring-0"
                        placeholder="Enter new label name..."
                        @keyup.enter="confirmRename"
                        ref="renameInput"
                    />
                </div>

                <div class="flex gap-4">
                    <button 
                        @click="renamingKey = null" 
                        class="flex-1 rounded border border-gray-200 bg-white py-3 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-colors"
                    >
                        Abort
                    </button>
                    <button 
                        @click="confirmRename" 
                        :disabled="!newKeyName || newKeyName === renamingKey"
                        class="flex-1 rounded bg-[#1FA6A0] py-3 text-[10px] font-black uppercase tracking-widest text-white hover:bg-[#168C87] disabled:opacity-50 transition-colors shadow-lg shadow-[#1FA6A0]/20"
                    >
                        Confirm Change
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: String,
    categoryId: [Number, String],
    placeholder: {
        type: String,
        default: 'Select or type specification...'
    }
});

const emit = defineEmits(['update:modelValue']);

const suggestions = ref([]);
const isOpen = ref(false);
const isLoading = ref(false);
const containerRef = ref(null);
const renameInput = ref(null);

const renamingKey = ref(null);
const newKeyName = ref('');

const fetchTemplates = async () => {
    if (!props.categoryId) {
        suggestions.value = [];
        return;
    }
    isLoading.value = true;
    try {
        const response = await axios.get(route('api.categories.spec-templates', props.categoryId));
        suggestions.value = response.data;
    } catch (e) {
        console.error('Failed to fetch spec templates');
    } finally {
        isLoading.value = false;
    }
};

const startRename = (key) => {
    renamingKey.value = key;
    newKeyName.value = key;
    isOpen.value = false;
    nextTick(() => {
        renameInput.value?.focus();
    });
};

const confirmRename = async () => {
    if (!newKeyName.value || newKeyName.value === renamingKey.value) return;
    
    try {
        await axios.post(route('api.categories.rename-spec-template', props.categoryId), {
            old_key: renamingKey.value,
            new_key: newKeyName.value
        });
        
        // Update local list
        const index = suggestions.value.indexOf(renamingKey.value);
        if (index !== -1) {
            suggestions.value[index] = newKeyName.value;
        }
        
        // If current value was the one being renamed, update parent
        if (props.modelValue === renamingKey.value) {
            emit('update:modelValue', newKeyName.value);
        }
        
        renamingKey.value = null;
    } catch (e) {
        alert('Failed to rename template: ' + (e.response?.data?.message || e.message));
    }
};

const filteredSuggestions = computed(() => {
    if (!props.modelValue) return suggestions.value;
    const query = props.modelValue.toLowerCase();
    return suggestions.value.filter(s => s.toLowerCase().includes(query));
});

watch(() => props.categoryId, () => {
    fetchTemplates();
}, { immediate: true });

const selectSuggestion = (s) => {
    emit('update:modelValue', s);
    isOpen.value = false;
};

const handleInput = (e) => {
    emit('update:modelValue', e.target.value);
    isOpen.value = true;
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
