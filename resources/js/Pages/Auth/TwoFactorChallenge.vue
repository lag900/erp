<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('two-factor.challenge'), {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Two-factor Confirmation" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">
                Two-Factor Verification
            </h2>
            <p class="mt-2 text-sm text-gray-500 text-balance px-4">
                Please confirm access to your account by entering the authentication code sent to your email.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="code" value="Authentication Code" class="text-sm font-semibold text-gray-800" />

                <div class="mt-1.5">
                    <TextInput
                        id="code"
                        type="text"
                        inputmode="numeric"
                        class="block w-full px-4 py-3.5 text-center text-2xl font-bold tracking-[0.5em] border-gray-300 focus:border-primary focus:ring-primary shadow-sm rounded-lg"
                        v-model="form.code"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="••••••"
                        maxlength="6"
                    />
                </div>

                <InputError class="mt-2 text-center" :message="form.errors.code" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-4 text-base font-bold shadow-sm ring-1 ring-primary transition-all active:scale-[0.98]"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Verify Code</span>
                    <span v-else class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Verifying...</span>
                    </span>
                </PrimaryButton>
            </div>

            <div class="text-center mt-6">
                <button 
                    type="button"
                    class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors"
                >
                    Resend code
                </button>
            </div>
            
            <p class="text-center text-[11px] text-gray-400 mt-6 px-4 uppercase tracking-widest font-bold">
                Secure Enterprise ERP Authentication
            </p>
        </form>
    </GuestLayout>
</template>
