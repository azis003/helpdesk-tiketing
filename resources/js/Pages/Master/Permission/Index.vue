<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

// Construct initial form state
const initialState = {};
props.roles.forEach(role => {
    initialState[role.id] = role.permissions.map(p => p.id);
});

const form = useForm({
    permissions: initialState
});

const submit = () => {
    form.post(route('master.permissions.update'));
};
</script>

<template>
    <Head title="Hak Akses & Peran" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Hak Akses & Peran</h2>
                <p class="text-slate-500 mt-1">Konfigurasi matriks perizinan untuk setiap peran pengguna.</p>
            </div>
            
            <button 
                type="button"
                @click="submit"
                :disabled="form.processing"
                class="inline-flex items-center justify-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-8 py-6 font-bold text-slate-800 text-sm uppercase tracking-wider sticky left-0 bg-slate-50 z-10 w-80">
                                Fitur & Izin
                            </th>
                            <th v-for="role in roles" :key="role.id" class="px-6 py-6 text-center font-extrabold text-slate-700 text-xs uppercase tracking-widest whitespace-nowrap">
                                {{ role.name.replace('_', ' ') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="permission in permissions" :key="permission.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-50">
                                <div class="font-bold text-slate-700 text-sm">{{ permission.name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ permission.guard_name }}</div>
                            </td>
                            <td v-for="role in roles" :key="role.id" class="px-6 py-5 text-center">
                                <label class="relative inline-flex items-center justify-center cursor-pointer group/check">
                                    <input 
                                        type="checkbox" 
                                        :value="permission.id" 
                                        v-model="form.permissions[role.id]" 
                                        class="peer sr-only"
                                    >
                                    <div class="w-6 h-6 bg-slate-100 border-2 border-slate-200 rounded-lg peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all flex items-center justify-center group-hover/check:border-indigo-300">
                                        <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                <button 
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50"
                >
                    {{ form.processing ? 'Memproses...' : 'Terapkan Perubahan Hak Akses' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
