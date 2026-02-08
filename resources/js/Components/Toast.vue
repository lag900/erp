<script setup>
import { ref, onMounted } from 'vue';

const toasts = ref([]);

const addToast = (type, message) => {
    const id = Date.now();
    toasts.value.push({ id, type, message });
    setTimeout(() => {
        removeToast(id);
    }, 4000);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

// Global Exposure
onMounted(() => {
    window.showToast = addToast;
});
</script>

<template>
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none">
        <TransitionGroup name="toast">
            <div 
                v-for="toast in toasts" 
                :key="toast.id" 
                class="pointer-events-auto flex items-start gap-3 rounded-xl border p-4 shadow-xl min-w-[320px] max-w-sm bg-white"
                :class="{
                    'border-green-200 bg-green-50': toast.type === 'success',
                    'border-red-200 bg-red-50': toast.type === 'error',
                    'border-blue-200 bg-blue-50': toast.type === 'info',
                }"
            >
                <!-- Icons -->
                <div class="flex-shrink-0 mt-0.5">
                    <svg v-if="toast.type === 'success'" class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="toast.type === 'error'" class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="toast.type === 'info'" class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                
                <div class="flex-1">
                    <p 
                        class="text-sm font-bold uppercase tracking-wide"
                        :class="{
                            'text-green-800': toast.type === 'success',
                            'text-red-800': toast.type === 'error',
                            'text-blue-800': toast.type === 'info',
                        }"
                    >
                        {{ toast.type === 'success' ? 'Operation Successful' : (toast.type === 'error' ? 'Active Error' : 'System Notice') }}
                    </p>
                    <p 
                        class="mt-1 text-sm font-medium leading-relaxed"
                        :class="{
                            'text-green-700': toast.type === 'success',
                            'text-red-700': toast.type === 'error',
                            'text-blue-700': toast.type === 'info',
                        }"
                    >
                        {{ toast.message }}
                    </p>
                </div>

                <button @click="removeToast(toast.id)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px) scale(0.9);
}

.toast-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(0.9);
}
</style>
