<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    user: Object
});

const page = usePage();

// Profile Form
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email || '',
    avatar: null,
    _method: 'PUT',
});

const avatarPreview = ref(props.user.avatar);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        profileForm.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const submitProfile = () => {
    profileForm.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <Head title="Profil Saya" />

    <AppLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Pengaturan Profil</h1>
            <p class="text-slate-500 mt-1">Kelola informasi akun dan keamanan Anda.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Edit Profile Card -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-900">Informasi Profil</h2>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitProfile">
                        <div class="mb-6 flex flex-col items-center">
                            <div class="relative group">
                                <div class="w-32 h-32 rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-md">
                                    <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <label for="avatar" class="absolute bottom-1 right-1 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-blue-500 transition-colors shadow-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <input id="avatar" type="file" class="hidden" @change="handleAvatarChange" accept="image/*" />
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Maks. 2MB (JPG, PNG)</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                                <input type="text" v-model="profileForm.name" class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required />
                                <div v-if="profileForm.errors.name" class="mt-1 text-sm text-red-500">{{ profileForm.errors.name }}</div>
                            </div>
                            
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Email</label>
                                <input type="email" v-model="profileForm.email" class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" />
                                <div v-if="profileForm.errors.email" class="mt-1 text-sm text-red-500">{{ profileForm.errors.email }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-400">Username</label>
                                <input type="text" :value="user.username" class="mt-1 block w-full bg-slate-100 border-slate-200 rounded-xl text-slate-500 cursor-not-allowed" disabled />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-400">Role</label>
                                <input type="text" :value="user.role" class="mt-1 block w-full bg-slate-100 border-slate-200 rounded-xl text-slate-500 cursor-not-allowed uppercase" disabled />
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-400">Unit Kerja</label>
                                <input type="text" :value="user.work_unit || '-'" class="mt-1 block w-full bg-slate-100 border-slate-200 rounded-xl text-slate-500 cursor-not-allowed" disabled />
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-500 transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50"
                                :disabled="profileForm.processing"
                            >
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-900">Ubah Password</h2>
                </div>
                <div class="p-6 flex-grow">
                    <form @submit.prevent="submitPassword" class="h-full flex flex-col">
                        <div class="space-y-4 flex-grow">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Password Lama</label>
                                <input type="password" v-model="passwordForm.current_password" class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required />
                                <div v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.current_password }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Password Baru</label>
                                <input type="password" v-model="passwordForm.password" class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required />
                                <div v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.password }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                                <input type="password" v-model="passwordForm.password_confirmation" class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required />
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-slate-900 text-white font-semibold rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 disabled:opacity-50"
                                :disabled="passwordForm.processing"
                            >
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
