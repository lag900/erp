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
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <InputLabel v-if="label" :for="`searchable-select-${label}`" :value="label" />
        <div class="relative mt-1">
            <button
                :id="`searchable-select-${label}`"
                type="button"
                :disabled="disabled"
                class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500"
                @click="toggleDropdown"
            >
                <span class="block truncate">
                    {{ selectedOption ? selectedOption.label : placeholder }}
                </span>
                <span
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                >
                    <svg
                        class="h-5 w-5 text-gray-400 transition-transform"
                        :class="{ 'rotate-180': isOpen }"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </span>
            </button>

            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    class="absolute z-10 mt-1 w-full overflow-hidden rounded-md bg-white text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                >
                    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white px-3 py-2">
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :placeholder="searchPlaceholder"
                            @click.stop
                            @keydown.stop
                        />
                    </div>
                    
                    <div class="max-h-60 overflow-auto">
                        <div
                            v-if="filteredOptions.length === 0"
                            class="px-3 py-2 text-sm text-gray-500"
                        >
                            {{ searchQuery ? 'No options found' : 'No options available' }}
                        </div>
                        <ul
                            v-else
                            class="py-1"
                            role="listbox"
                        >
                            <li
                                v-for="option in filteredOptions"
                                :key="`option-${option.id}`"
                                class="relative cursor-pointer select-none py-2 pl-3 pr-9 transition-colors hover:bg-indigo-50"
                                :class="{
                                    'bg-indigo-50': String(option.id) === String(modelValue),
                                }"
                                role="option"
                                :aria-selected="String(option.id) === String(modelValue)"
                                @click="selectOption(option)"
                            >
                                <span
                                    class="block truncate"
                                    :class="{
                                        'font-medium text-indigo-600': String(option.id) === String(modelValue),
                                        'font-normal text-gray-900': String(option.id) !== String(modelValue),
                                    }"
                                >
                                    {{ option.label }}
                                </span>
                                <span
                                    v-if="String(option.id) === String(modelValue)"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>
