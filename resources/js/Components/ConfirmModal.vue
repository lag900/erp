<script setup>
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const isVisible = ref(false);
const options = ref({
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    onConfirm: () => {},
});

const showConfirm = (opts) => {
    options.value = { ...options.value, ...opts };
    isVisible.value = true;
};

const handleConfirm = () => {
    if (options.value.onConfirm) options.value.onConfirm();
    isVisible.value = false;
};

const handleCancel = () => {
    isVisible.value = false;
};

// Global Exposure
onMounted(() => {
    window.showConfirm = showConfirm;
});
</script>

<template>
    <Modal :show="isVisible" @close="handleCancel" maxWidth="md">
        <div class="p-8 text-center bg-white rounded-3xl">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 shadow-soft">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            
            <h3 class="text-xl font-black uppercase tracking-tight text-gray-900 mb-2">
                {{ options.title }}
            </h3>
            
            <p class="text-sm font-medium text-gray-500 leading-relaxed max-w-xs mx-auto mb-8">
                {{ options.message }}
            </p>

            <div class="flex justify-center gap-4">
                <SecondaryButton @click="handleCancel" class="w-full justify-center !rounded-xl !py-3 !text-xs !font-black !uppercase !tracking-widest">
                    {{ options.cancelText }}
                </SecondaryButton>
                
                <DangerButton @click="handleConfirm" class="w-full justify-center !rounded-xl !py-3 !text-xs !font-black !uppercase !tracking-widest shadow-lg shadow-red-100 hover:shadow-red-200">
                    {{ options.confirmText }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
