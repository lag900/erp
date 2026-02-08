<script setup>
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-700 shadow-sm ring-1 ring-inset ring-green-600/20">
            {{ status }}
        </div>

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">
                Welcome Back
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Sign in to access your administrative dashboard
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email Address" class="text-sm font-semibold text-gray-800" />

                <div class="mt-1.5">
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3.5 text-base border-gray-300 focus:border-primary focus:ring-primary shadow-sm rounded-lg"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="e.g. admin@batu.edu.eg"
                    />
                </div>

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" class="text-sm font-semibold text-gray-800" />
                </div>

                <div class="mt-1.5 relative group">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full px-4 py-3.5 pr-12 text-base border-gray-300 focus:border-primary focus:ring-primary shadow-sm rounded-lg"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                    >
                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.483 8.653 7.073 5.375 12 5.375c4.927 0 8.517 3.278 9.964 6.303.116.241.116.533 0 .774-1.447 3.025-5.037 6.303-9.964 6.303-4.927 0-8.517-3.278-9.964-6.303Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A11.003 11.003 0 0 0 3.25 12c.422 1.664 1.445 3.191 2.506 4.41C7.307 18.23 9.49 19.625 12 19.625c2.51 0 4.693-1.395 6.244-3.015a10.99 10.99 0 0 0 2.506-4.41c-.422-1.664-1.445-3.191-2.506-4.41A10.99 10.99 0 0 0 12 4.375c-2.454 0-4.637 1.395-6.188 3.015a10.963 10.963 0 0 0-1.832 2.508ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75l16.5 16.5" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center group cursor-pointer">
                    <GlobalCheckbox id="remember" name="remember" v-model:checked="form.remember" />
                    <span class="ms-3 text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm font-semibold text-primary hover:text-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                >
                    Forgot password?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-4 text-base font-bold shadow-sm ring-1 ring-primary transition-all active:scale-[0.98]"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Sign in</span>
                    <span v-else class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Signing in...</span>
                    </span>
                </PrimaryButton>
            </div>
            
            <p class="text-center text-xs text-gray-400 mt-4 px-4 line-clamp-2 md:line-clamp-none">
                Access to this system is restricted to authorized personnel only. 
                All login attempts are logged and monitored.
            </p>
        </form>
    </GuestLayout>
</template>
