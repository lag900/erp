<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    id: {
        type: String,
        default: null,
    },
    indeterminate: {
        type: Boolean,
        default: false,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});

const isChecked = computed(() => {
    if (Array.isArray(proxyChecked.value)) {
        return proxyChecked.value.includes(props.value);
    }
    return proxyChecked.value;
});
</script>

<template>
    <div class="inline-flex items-center group cursor-pointer relative">
        <input
            type="checkbox"
            :id="id"
            :value="value"
            v-model="proxyChecked"
            :indeterminate="indeterminate"
            class="peer absolute opacity-0 w-6 h-6 cursor-pointer -m-1 z-10"
        />
        
        <div 
            class="relative flex items-center justify-center w-6 h-6 rounded border-2 transition-all duration-200 ease-in-out
                   bg-white border-gray-300 shadow-sm
                   group-hover:border-primary/50 group-hover:bg-gray-50
                   peer-focus-visible:ring-2 peer-focus-visible:ring-primary/20 peer-focus-visible:ring-offset-1
                   peer-checked:bg-primary peer-checked:border-primary
                   peer-indeterminate:bg-primary peer-indeterminate:border-primary"
            :class="{
                '!bg-primary !border-primary': isChecked || indeterminate
            }"
        >
            <!-- Checkmark Icon -->
            <svg 
                v-if="isChecked && !indeterminate"
                class="w-4 h-4 text-white transform transition-transform duration-200 scale-100"
                viewBox="0 0 16 16" 
                fill="none" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <path 
                    d="M13.3334 4L6.00008 11.3333L2.66675 8" 
                    stroke="currentColor" 
                    stroke-width="2.5" 
                    stroke-linecap="round" 
                    stroke-linejoin="round"
                />
            </svg>

            <!-- Indeterminate Dash -->
            <div 
                v-if="indeterminate"
                class="w-3 h-0.5 bg-white rounded-full"
            ></div>
        </div>
        
        <!-- MUI-style Hover Ripple Effect -->
        <div class="absolute inset-0 rounded-full w-10 h-10 -left-2 -top-2 opacity-0 group-hover:opacity-100 group-hover:bg-primary/10 transition-all duration-200 pointer-events-none scale-75 group-active:scale-100"></div>
    </div>
</template>

<style scoped>
/* MUI-like focus and active states */
input:focus-visible + div {
    outline: 2px solid theme('colors.primary.DEFAULT' / 20%);
    outline-offset: 2px;
}

/* Smooth transition for checked state */
.peer-checked + div svg, .peer-indeterminate + div div {
    animation: mui-ripple 0.2s ease-out;
}

@keyframes mui-ripple {
    from { transform: scale(0.5); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
