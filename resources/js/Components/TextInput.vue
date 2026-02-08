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
        class="w-full px-4 py-2.5 bg-[#fcfdfe] border border-slate-200 rounded-xl text-slate-700 text-[14.5px] transition-all duration-200 
               placeholder:text-slate-400 placeholder:font-normal
               hover:border-slate-300
               focus:bg-white focus:border-[#3d4adb] focus:ring-4 focus:ring-[#3d4adb]/5 focus:outline-none font-medium"
        v-model="model"
        ref="input"
    />
</template>

