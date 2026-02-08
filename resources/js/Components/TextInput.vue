<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full px-4 py-3 bg-gray-50/50 border-gray-200 rounded-lg text-gray-900 text-sm transition-all duration-300
               placeholder:text-gray-400
               hover:bg-white hover:border-gray-300
               focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 focus:outline-none
               dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-800 dark:focus:bg-gray-800"
        v-model="model"
        ref="input"
    />
</template>
