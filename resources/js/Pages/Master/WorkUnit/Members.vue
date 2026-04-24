<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    workUnit: Object,
    users: Array,
});

const form = useForm({
    user_id: '',
});

const addMember = () => {
    form.post(route('master.work-units.members.store', props.workUnit.id), {
        onSuccess: () => form.reset('user_id'),
    });
};

const removeMember = (member) => {
    Swal.fire({
        title: 'Hapus Anggota?',
        text: `Apakah Anda yakin ingin menghapus ${member.name} dari unit kerja ini?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route('master.work-units.members.destroy', [props.workUnit.id, member.id]), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Anggota telah berhasil dihapus.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Anggota Unit Kerja" />

    <AppLayout>
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('master.work-units.index')" 
                    class="p-3 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-blue-600 hover:border-blue-100 hover:bg-blue-50/50 transition-all group"
                >
                    <svg class="w-6 h-6 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Anggota Tim</h2>
                    <p class="text-slate-500 mt-1">Mengelola personel untuk <span class="text-blue-600 font-bold">{{ workUnit.name }}</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Add Member Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden sticky top-8">
                    <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="text-xl font-bold text-slate-800">Tambah Anggota Baru</h3>
                        <p class="text-sm text-slate-500 mt-1">Pilih pengguna untuk ditugaskan ke unit ini.</p>
                    </div>
                    <div class="p-8 relative">
                        <form @submit.prevent="addMember" class="space-y-6">
                            <div>
                                <label for="user" class="block text-sm font-bold text-slate-700 mb-2">Pilih Pengguna</label>
                                <div class="relative">
                                    <select 
                                        id="user" 
                                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium appearance-none" 
                                        v-model="form.user_id" 
                                        required
                                    >
                                        <option value="" disabled>-- Pilih Pengguna --</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.user_id" class="text-sm text-red-500 mt-2 font-medium">{{ form.errors.user_id }}</div>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="form.processing || !form.user_id"
                                class="w-full inline-flex items-center justify-center px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-100 transition-all active:scale-95 disabled:opacity-50 disabled:grayscale"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-if="form.processing">Menambahkan...</span>
                                <span v-else>Tambah ke Tim</span>
                            </button>
                        </form>

                        <!-- Empty State Info -->
                        <div v-if="users.length === 0" class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-amber-700 leading-relaxed font-medium">
                                    Semua pengguna aktif sudah terdaftar di unit kerja. Pastikan pengguna lain sudah diaktifkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Member List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Daftar Anggota Saat Ini</h3>
                            <p class="text-sm text-slate-500 mt-1">Total <span class="text-blue-600 font-bold">{{ workUnit.members?.length || 0 }}</span> anggota dalam tim ini.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                                    <th class="px-8 py-5 font-semibold">Pengguna</th>
                                    <th class="px-8 py-5 font-semibold">Username</th>
                                    <th class="px-8 py-5 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="member in workUnit.members" :key="member.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-xs uppercase">
                                                {{ member.name.substring(0, 2) }}
                                            </div>
                                            <div class="font-bold text-slate-800">{{ member.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-xs font-mono">
                                            @{{ member.username }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex justify-end">
                                            <button 
                                                @click="removeMember(member)" 
                                                class="p-3 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"
                                                title="Hapus dari Tim"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!workUnit.members || workUnit.members.length === 0">
                                    <td colspan="3" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-400 font-medium">Belum ada anggota yang terdaftar di tim ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
