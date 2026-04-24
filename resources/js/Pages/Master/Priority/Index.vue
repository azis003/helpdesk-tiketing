<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Modal from '@/Components/UI/Modal.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    priorities: Object,
    filters: Object,
    next_level: Number,
});

// State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedPriority = ref(null);
const search = ref(props.filters.search || '');

// Forms
const createForm = useForm({
    name: '',
    level: 1,
    color: '#000000',
});

const editForm = useForm({
    name: '',
    level: 1,
    color: '#000000',
});

const toggleForm = useForm({});

// Actions
const openCreateModal = () => {
    createForm.reset();
    createForm.level = props.next_level; // Default to next available level
    createForm.clearErrors();
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.post(route('master.priorities.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (priority) => {
    selectedPriority.value = priority;
    editForm.name = priority.name;
    editForm.level = priority.level;
    editForm.color = priority.color;
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitUpdate = () => {
    editForm.put(route('master.priorities.update', selectedPriority.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const toggleActive = (priority) => {
    Swal.fire({
        title: 'Ubah Status?',
        text: `Apakah Anda yakin ingin ${priority.is_active ? 'menonaktifkan' : 'mengaktifkan'} prioritas ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            toggleForm.patch(route('master.priorities.toggle-active', priority.id), {
                preserveScroll: true,
            });
        }
    });
};

// Search with debounce
watch(search, debounce((value) => {
    router.get(route('master.priorities.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

</script>

<template>
    <Head title="Master Prioritas" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Level Prioritas</h2>
                <p class="text-slate-500 mt-1">Kelola tingkat urgensi tiket.</p>
            </div>
            
            <button 
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 group"
            >
                <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6" />
                </svg>
                Tambah Prioritas
            </button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Cari nama prioritas..." 
                        class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-8 py-5 font-semibold">Nama & Level</th>
                            <th class="px-8 py-5 font-semibold text-center">Warna</th>
                            <th class="px-8 py-5 font-semibold text-center">Status</th>
                            <th class="px-8 py-5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="priority in priorities.data" :key="priority.id" class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center">
                                    <div 
                                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white mr-4 shadow-sm"
                                        :style="{ backgroundColor: priority.color }"
                                    >
                                        {{ priority.level }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ priority.name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Level {{ priority.level }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex justify-center items-center">
                                    <div class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ priority.color }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-center">
                                    <button 
                                        type="button"
                                        @click="toggleActive(priority)"
                                        :disabled="toggleForm.processing"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                        :class="priority.is_active ? 'bg-indigo-600' : 'bg-slate-200'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                            :class="priority.is_active ? 'translate-x-6' : 'translate-x-1'"
                                        />
                                    </button>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-end items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="openEditModal(priority)"
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="priorities.data.length === 0">
                            <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                <Pagination :links="priorities.links" />
            </div>
        </div>

        <!-- Modal Tambah -->
        <Modal :show="showCreateModal" @close="!createForm.processing && (showCreateModal = false)" maxWidth="xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Tambah Prioritas</h3>
                    <button @click="showCreateModal = false" :disabled="createForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Prioritas <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="name" 
                            type="text" 
                            v-model="createForm.name"
                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-medium"
                            required
                        />
                        <div v-if="createForm.errors.name" class="text-sm text-red-500 mt-2 font-medium">{{ createForm.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="level" class="block text-sm font-bold text-slate-700 mb-2">
                                Level (Angka) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                id="level" 
                                type="number" 
                                v-model="createForm.level"
                                class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-medium"
                                required
                            />
                            <div v-if="createForm.errors.level" class="text-sm text-red-500 mt-2 font-medium">{{ createForm.errors.level }}</div>
                        </div>
                        <div>
                            <label for="color" class="block text-sm font-bold text-slate-700 mb-2">
                                Warna <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input 
                                    id="color" 
                                    type="color" 
                                    v-model="createForm.color"
                                    class="h-12 w-20 p-1 bg-white border border-slate-200 rounded-xl cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="createForm.color"
                                    class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-mono"
                                />
                            </div>
                            <div v-if="createForm.errors.color" class="text-sm text-red-500 mt-2 font-medium">{{ createForm.errors.color }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" :disabled="createForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="createForm.processing" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                            <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="createForm.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div v-if="createForm.processing" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-[100]">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-indigo-600 font-bold text-sm tracking-wide">Memproses Data...</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Edit -->
        <Modal :show="showEditModal" @close="!editForm.processing && (showEditModal = false)" maxWidth="xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Edit Prioritas</h3>
                    <button @click="showEditModal = false" :disabled="editForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpdate" class="space-y-6">
                    <div>
                        <label for="edit_name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Prioritas <span class="text-red-500">*</span>
                        </label>
                        <input id="edit_name" type="text" v-model="editForm.name" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-medium" required />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="edit_level" class="block text-sm font-bold text-slate-700 mb-2">
                                Level <span class="text-red-500">*</span>
                            </label>
                            <input id="edit_level" type="number" v-model="editForm.level" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-medium" required />
                            <div v-if="editForm.errors.level" class="text-sm text-red-500 mt-2 font-medium">{{ editForm.errors.level }}</div>
                        </div>
                        <div>
                            <label for="edit_color" class="block text-sm font-bold text-slate-700 mb-2">
                                Warna <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input id="edit_color" type="color" v-model="editForm.color" class="h-12 w-20 p-1 bg-white border border-slate-200 rounded-xl cursor-pointer" />
                                <input type="text" v-model="editForm.color" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-600 font-mono" />
                            </div>
                            <div v-if="editForm.errors.color" class="text-sm text-red-500 mt-2 font-medium">{{ editForm.errors.color }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" :disabled="editForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                            <svg v-if="editForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="editForm.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div v-if="editForm.processing" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-[100]">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-indigo-600 font-bold text-sm tracking-wide">Memperbarui Data...</p>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
