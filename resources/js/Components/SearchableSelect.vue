<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: null,
    },
    options: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: 'Select an option...',
    },
    label: {
        type: String,
        default: '',
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);

// Normalize options to ensure they have id and label
const normalizedOptions = computed(() => {
    if (!Array.isArray(props.options)) {
        return [];
    }
    return props.options.map(option => {
        if (!option) return null;
        return {
            id: option.id ?? option.value ?? null,
            label: String(option.label ?? option.name ?? option.text ?? ''),
        };
    }).filter(opt => opt !== null && opt.id !== null);
});

// Filter options based on search query
const filteredOptions = computed(() => {
    const options = normalizedOptions.value;
    
    if (options.length === 0) {
        return [];
    }
    
    if (!searchQuery.value || !searchQuery.value.trim()) {
        return options;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return options.filter((option) => {
        return option.label.toLowerCase().includes(query);
    });
});

// Find selected option
const selectedOption = computed(() => {
    const options = normalizedOptions.value;
    if (options.length === 0 || props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
        return null;
    }
    
    return options.find((opt) => {
        return String(opt.id) === String(props.modelValue);
    }) || null;
});

// Select an option
const selectOption = (option) => {
    if (!option || option.id === null || option.id === undefined) {
        return;
    }
    emit('update:modelValue', option.id);
    isOpen.value = false;
    searchQuery.value = '';
};

// Toggle dropdown
const toggleDropdown = () => {
    if (props.disabled) {
        return;
    }
    
    isOpen.value = !isOpen.value;
    
    if (isOpen.value) {
        searchQuery.value = '';
        // Focus search input after dropdown opens
        nextTick(() => {
            if (searchInputRef.value) {
                searchInputRef.value.focus();
            }
        });
    }
};

// Close dropdown when clicking outside
const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

// Handle keyboard navigation
const handleKeydown = (event) => {
    if (!isOpen.value) {
        return;
    }
    
    if (event.key === 'Escape') {
        isOpen.value = false;
        searchQuery.value = '';
        event.preventDefault();
    } else if (event.key === 'Enter' && filteredOptions.value.length === 1) {
        selectOption(filteredOptions.value[0]);
        event.preventDefault();
    } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        // Prevent default scrolling
        event.preventDefault();
    }
};

// Watch for modelValue changes
watch(() => props.modelValue, (newValue) => {
    if (!newValue) {
        searchQuery.value = '';
    }
});

// Watch for options changes
watch(() => props.options, (newOptions, oldOptions) => {
    // Debug: log when options change
    if (props.label === 'Subcategory') {
        console.log('Subcategory options changed:', {
            old: oldOptions?.length || 0,
            new: newOptions?.length || 0,
            normalized: normalizedOptions.value.length,
        });
    }
    
    // If options changed and we have a selected value, verify it still exists
    if (props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '') {
        const options = normalizedOptions.value;
        const exists = options.some(opt => String(opt.id) === String(props.modelValue));
        if (!exists && options.length > 0) {
            // Clear selection if it doesn't exist in new options
            emit('update:modelValue', null);
        }
    }
    
    // Reset search if no results but we have options
    if (searchQuery.value && filteredOptions.value.length === 0 && normalizedOptions.value.length > 0) {
        searchQuery.value = '';
    }
}, { deep: true, immediate: true });

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('keydown', handleKeydown);
});

defineExpose({
    focus: () => {
        if (dropdownRef.value?.querySelector('button')) {
            dropdownRef.value.querySelector('button').focus();
        }
    },
    open: () => {
        if (!props.disabled) {
            isOpen.value = true;
            nextTick(() => {
                if (searchInputRef.value) {
                    searchInputRef.value.focus();
                }
            });
        }
    }
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <label v-if="label" :for="`ss-${label}`" class="form-group-label">
            {{ label }}
        </label>
        
        <div class="relative">
            <button
                :id="`ss-${label}`"
                type="button"
                :disabled="disabled"
                @click="toggleDropdown"
                class="flex items-center justify-between w-full h-[44px] px-4 bg-[#F8FAFC] border border-[#E5E7EB] rounded-[10px] text-[15px] font-medium text-gray-700 transition-all hover:border-[#1FA6A0] focus:border-[#1FA6A0] focus:bg-white focus:shadow-[0_0_0_4px_rgba(31,166,160,0.1)] disabled:opacity-50 disabled:cursor-not-allowed text-left group"
                :class="{ 'border-[#1FA6A0] bg-white ring-4 ring-[#1FA6A0]/10': isOpen }"
            >
                <span class="truncate" :class="{ 'text-gray-400': !selectedOption }">
                    {{ selectedOption ? selectedOption.label : placeholder }}
                </span>
                <svg
                    class="h-5 w-5 text-gray-400 group-hover:text-[#1FA6A0] transition-transform flex-shrink-0 ml-2"
                    :class="{ 'rotate-180 text-[#1FA6A0]': isOpen }"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="transform opacity-0 -translate-y-2"
                enter-to-class="transform opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="transform opacity-100 translate-y-0"
                leave-to-class="transform opacity-0 -translate-y-2"
            >
                <div
                    v-if="isOpen"
                    class="absolute z-[150] mt-2 w-full bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] overflow-hidden"
                >
                    <!-- Search Bar -->
                    <div class="p-2 bg-gray-50/50 border-b border-gray-100">
                        <div class="relative">
                            <input
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text"
                                class="w-full h-[38px] !bg-white !rounded-[8px] !text-[13px] !pl-9 !border-gray-200"
                                :placeholder="searchPlaceholder"
                                @click.stop
                                @keydown.stop
                            />
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options List -->
                    <div class="max-h-[280px] overflow-y-auto custom-scrollbar">
                        <div
                            v-if="filteredOptions.length === 0"
                            class="px-5 py-8 text-center"
                        >
                            <svg class="mx-auto h-8 w-8 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-[13px] text-gray-400 font-medium">{{ searchQuery ? 'No matches found' : 'No options available' }}</p>
                        </div>
                        
                        <ul role="listbox" class="p-1">
                            <li
                                v-for="option in filteredOptions"
                                :key="`opt-${option.id}`"
                                @click="selectOption(option)"
                                role="option"
                                :aria-selected="String(option.id) === String(modelValue)"
                                class="flex items-center justify-between px-4 py-2.5 rounded-[8px] cursor-pointer transition-all hover:bg-[#F8FAFC]"
                                :class="{ 'bg-[#E6F4F3]/50 text-[#1FA6A0]': String(option.id) === String(modelValue) }"
                            >
                                <span class="text-[14px] font-semibold truncate leading-none">
                                    {{ option.label }}
                                </span>
                                <svg
                                    v-if="String(option.id) === String(modelValue)"
                                    class="h-4 w-4 text-[#1FA6A0]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </li>
                        </ul>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E5E7EB;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #D1D5DB;
}
</style>
