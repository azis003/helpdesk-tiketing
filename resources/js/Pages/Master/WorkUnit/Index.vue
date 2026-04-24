<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Modal from '@/Components/UI/Modal.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    workUnits: Object,
    filters: Object,
});

// State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedUnit = ref(null);
const search = ref(props.filters.search || '');

// Forms
const createForm = useForm({
    code: '',
    name: '',
});

const editForm = useForm({
    code: '',
    name: '',
});

const toggleForm = useForm({});

// Actions
const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.post(route('master.work-units.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (unit) => {
    selectedUnit.value = unit;
    editForm.code = unit.code;
    editForm.name = unit.name;
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitUpdate = () => {
    editForm.put(route('master.work-units.update', selectedUnit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const toggleActive = (unit) => {
    Swal.fire({
        title: 'Ubah Status?',
        text: `Apakah Anda yakin ingin ${unit.is_active ? 'menonaktifkan' : 'mengaktifkan'} unit kerja ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            toggleForm.patch(route('master.work-units.toggle-active', unit.id), {
                preserveScroll: true,
            });
        }
    });
};

// Search with debounce
watch(search, debounce((value) => {
    router.get(route('master.work-units.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

</script>

<template>
    <Head title="Master Unit Kerja" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Unit Kerja</h2>
                <p class="text-slate-500 mt-1">Kelola daftar unit kerja / departemen.</p>
            </div>
            
            <button 
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 group"
            >
                <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6" />
                </svg>
                Tambah Unit Kerja
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
                        placeholder="Cari kode atau nama unit..." 
                        class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-8 py-5 font-semibold">Kode</th>
                            <th class="px-8 py-5 font-semibold">Nama Unit</th>
                            <th class="px-8 py-5 font-semibold text-center">Anggota</th>
                            <th class="px-8 py-5 font-semibold text-center">Status</th>
                            <th class="px-8 py-5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="unit in workUnits.data" :key="unit.id" class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold border border-slate-200">
                                    {{ unit.code }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-800">{{ unit.name }}</div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="text-slate-700 font-bold">{{ unit.members_count }}</span>
                                    <span class="text-slate-400 text-xs uppercase font-semibold tracking-tighter">Org</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-center">
                                    <button 
                                         type="button"
                                         @click="toggleActive(unit)"
                                         :disabled="toggleForm.processing"
                                         class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                         :class="unit.is_active ? 'bg-blue-600' : 'bg-slate-200'"
                                     >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                            :class="unit.is_active ? 'translate-x-6' : 'translate-x-1'"
                                        />
                                    </button>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-end items-center gap-2">
                                    <Link 
                                        :href="route('master.work-units.members', unit.id)"
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                        title="Anggota Tim"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </Link>
                                    <button 
                                        type="button"
                                        @click="openEditModal(unit)"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="workUnits.data.length === 0">
                            <td colspan="5" class="px-8 py-12 text-center text-slate-400">
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                <Pagination :links="workUnits.links" />
            </div>
        </div>

        <!-- Modal Tambah -->
        <Modal :show="showCreateModal" @close="!createForm.processing && (showCreateModal = false)" maxWidth="xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Tambah Unit Kerja</h3>
                    <button @click="showCreateModal = false" :disabled="createForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label for="code" class="block text-sm font-bold text-slate-700 mb-2">
                            Kode Unit <span class="text-red-500">*</span>
                        </label>
                        <input id="code" type="text" v-model="createForm.code" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium" placeholder="Contoh: IT, HR, dll." required />
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <input id="name" type="text" v-model="createForm.name" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium" required />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="showCreateModal = false" :disabled="createForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="createForm.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                            <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="createForm.processing">Menyimpan...</span>
                            <span v-else>Simpan Unit</span>
                        </button>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div v-if="createForm.processing" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-[100]">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-blue-600 font-bold text-sm tracking-wide">Memproses Data...</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Edit -->
        <Modal :show="showEditModal" @close="!editForm.processing && (showEditModal = false)" maxWidth="xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Edit Unit Kerja</h3>
                    <button @click="showEditModal = false" :disabled="editForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpdate" class="space-y-6">
                    <div>
                        <label for="edit_code" class="block text-sm font-bold text-slate-700 mb-2">
                            Kode Unit <span class="text-red-500">*</span>
                        </label>
                        <input id="edit_code" type="text" v-model="editForm.code" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium" required />
                    </div>

                    <div>
                        <label for="edit_name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <input id="edit_name" type="text" v-model="editForm.name" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium" required />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" :disabled="editForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
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
                        <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-blue-600 font-bold text-sm tracking-wide">Memperbarui Data...</p>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
