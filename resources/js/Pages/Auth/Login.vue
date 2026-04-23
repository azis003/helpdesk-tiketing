<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    username: '',
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-[#f0f2f5] p-4 font-sans">
        <div class="w-full max-w-[420px] bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 sm:p-10 border border-slate-100">
            <!-- Icon / Logo Area -->
            <div class="flex flex-col items-center mb-8">
                <div class="relative mb-6">
                    <div class="w-[72px] h-[72px] bg-[#eef3fb] rounded-[20px] flex items-center justify-center">
                        <!-- Bank Icon -->
                        <svg class="w-10 h-10 text-[#004dd0]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L1 8v2h22V8L12 2zm-7 10v7h2v-7H5zm6 0v7h2v-7h-2zm6 0v7h2v-7h-2zM2 20v2h20v-2H2z" />
                        </svg>
                    </div>
                    <!-- Shield Check Icon -->
                    <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm">
                         <svg class="w-6 h-6 text-[#004dd0]" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
                <p class="text-sm text-slate-500 mt-2">Gunakan username dan password</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">Username / NIP</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            id="username"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-[13px] placeholder-slate-400 focus:outline-none focus:border-[#004dd0] focus:ring-1 focus:ring-[#004dd0] transition-colors bg-white"
                            placeholder="Masukkan username Anda"
                            v-model="form.username"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <div v-if="form.errors.username" class="mt-1.5 text-xs text-red-500 font-medium">
                        {{ form.errors.username }}
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-lg text-[13px] placeholder-slate-400 tracking-widest focus:outline-none focus:border-[#004dd0] focus:ring-1 focus:ring-[#004dd0] transition-colors bg-white"
                            placeholder="••••••••"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                            tabindex="-1"
                        >
                            <!-- Eye Icon -->
                            <svg v-if="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash Icon -->
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 font-medium">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="pt-3">
                    <button
                        type="submit"
                        class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-[13px] font-bold text-white bg-[#004dd0] hover:bg-[#003db3] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#004dd0] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="mr-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        Masuk
                        <svg v-if="!form.processing" class="ml-2 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" />
                        </svg>
                    </button>
                </div>
            </form>

            <div class="mt-10 text-center">
                <p class="text-[11px] font-medium text-slate-400">@BP2KOMDIGI</p>
            </div>
        </div>
    </div>
</template>
